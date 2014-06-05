<?php
/**
 * Oggetto Web Messenger extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Oggetto Messenger module to newer versions in the future.
 * If you wish to customize the Oggetto Messenger module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @copyright  Copyright (C) 2012 Oggetto Web (http://oggettoweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/../abstract.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

/**
 * Messenger worker description
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Shell_Messenger_Worker
{
    /**
     * Worker ID
     *
     * @var integer
     */
    private $_id;

    /**
     * Queue
     *
     * @var Oggetto_Shell_Messenger_Queue
     */
    private $_queue;

    /**
     * Init the workers
     *
     * @param Oggetto_Shell_Messenger_Queue $queue Queue
     * @param integer                       $id    ID
     */
    public function __construct(Oggetto_Shell_Messenger_Queue $queue, $id)
    {
        $this->_queue = $queue;
        $this->_id = $id;
    }

    /**
     * Get worker ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get worker queue
     *
     * @return \Oggetto_Shell_Messenger_Queue
     */
    public function getQueue()
    {
        return $this->_queue;
    }
}

/**
 * Messenger queue description
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Shell_Messenger_Queue
{
    /**
     * Distributed type: each message will be processed in
     * separate process
     */
    const TYPE_DISTRIBUTED = 'd';

    /**
     * Selfish type: each message will be processed in
     * listener process
     */
    const TYPE_SELFISH = 's';

    /**
     * Queue name
     *
     * @var string
     */
    private $_name;

    /**
     * Number of workers in the queue
     *
     * @var string
     */
    private $_workersCount;

    /**
     * Message type
     *
     * @var string
     */
    private $_type;

    /**
     * Init queue
     *
     * @param string  $name         Queue name
     * @param integer $workersCount Number of workers for the queue
     * @param string  $type         Queue type
     *
     * @return \Oggetto_Shell_Messenger_Queue
     */
    public function __construct($name, $workersCount, $type)
    {
        $this->_name = $name;
        $this->_workersCount = $workersCount;
        $this->_type = $type;
    }

    /**
     * Get queue name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get workers
     *
     * @return Oggetto_Shell_Messenger_Worker[]
     */
    public function getWorkers()
    {
        $result = [];
        for ($i = 0; $i < $this->_workersCount; $i++) {
            $id = $this->_name . ':' . $i;
            $result[] = new Oggetto_Shell_Messenger_Worker($this, $id);
        }

        return $result;
    }

    /**
     * Get queue listener type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get worker memory limit
     *
     * @return string
     */
    public function getMemoryLimit()
    {
        return 110 * 1024 * 1024;
    }
}

/**
 * Messenger updates receiver shell script
 *
 * @category   Oggetto
 * @package    Oggetto_Messenger
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_Shell_Messenger_Receiver extends Mage_Shell_Abstract
{
    /**
     * Queues to start
     *
     * @var Oggetto_Shell_Messenger_Queue[]
     */
    protected $_queues = array();

    /**
     * Running worker PIDs
     *
     * @var array
     */
    protected $_workerPids = array();

    /**
     * Run script
     *
     * @return void
     */
    public function run()
    {
        if ($this->getArg('start')) {
            $this->_queues = $this->_parseQueues();
            return $this->_runDaemon();
        }

        if ($this->getArg('stop')) {
            return $this->_stopDaemon();
        }

        if ($this->getArg('reload')) {
            return $this->_reloadDaemonWorkers();
        }

        if ($this->getArg('status')) {
            return $this->_printStatus();
        }

        die($this->usageHelp());
    }

    /**
     * Parse requested queues
     *
     * @return array
     */
    private function _parseQueues()
    {
        if (!$this->getArg('queues') && !$this->getArg('config')) {
            die("Queues description not specified, use -h option to get help\n");
        }

        if ($this->getArg('queues')) {
            return $this->_parseManualQueues($this->getArg('queues'));
        } else {
            return $this->_parseQueuesConfig($this->getArg('config'));
        }
    }

    /**
     * Parse queues config from file
     *
     * @param string $file Path to file
     * @return Oggetto_Shell_Messenger_Queue[]
     */
    private function _parseQueuesConfig($file)
    {
        if (!file_exists($file)) {
            die('Config file not found');
        }

        $config = (new Yaml())->parse($file);
        $result = [];
        foreach ($config['queues'] as $_config) {
            $result[] = new Oggetto_Shell_Messenger_Queue(
                $_config['name'],
                $_config['workers'],
                $_config['type']
            );
        }
        return $result;
    }

    /**
     * Parse queues config from CLI
     *
     * @param array $config Queues config
     * @return Oggetto_Shell_Messenger_Queue[]
     */
    private function _parseManualQueues($config)
    {
        $result = [];
        $queues = explode(',', $config);
        foreach ($queues as $_queue) {
            list ($name, $workers, $type) = explode(':', $_queue);
            $result[] = new Oggetto_Shell_Messenger_Queue($name, $workers, $type);
        }
        return $result;
    }

    /**
     * Get daemon pid
     *
     * @return integer
     */
    protected function _getDaemonPid()
    {
        if (!file_exists($this->_getPidFile())) {
            return null;
        }

        return file_get_contents($this->_getPidFile());
    }

    /**
     * Save daemon pid
     *
     * @param string $pid PID
     * @return void
     */
    protected function _saveDaemonPid($pid)
    {
        file_put_contents($this->_getPidFile(), $pid);
    }

    /**
     * Prepare daemon PID file
     *
     * @return void
     */
    protected function _prepareDaemonPidFile()
    {
        $dir = dirname($this->_getPidFile());
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!is_writeable($dir)) {
            exit("Cannot run receiver: {$dir} is not writeable!\n");
        }
    }

    /**
     * Get path to daemon pid file
     *
     * @return string
     */
    protected function _getPidFile()
    {
        return __DIR__ . '/../../var/messenger/receiver.pid';
    }

    /**
     * Stop daemon
     *
     * @return void
     */
    protected function _stopDaemon()
    {
        if ($pid = $this->_getDaemonPid()) {
            echo "Stopping updates receiver\n";
            posix_kill($pid, SIGTERM);
        } else {
            echo "Receiver is not running\n";
        }
    }

    /**
     * Send signal to daemon to reload workers
     *
     * @return void
     */
    protected function _reloadDaemonWorkers()
    {
        if ($pid = $this->_getDaemonPid()) {
            echo "Reloading updates receiver\n";
            posix_kill($pid, SIGUSR1);
        } else {
            echo "Receiver is not running\n";
        }
    }

    /**
     * Run receiver daemon
     *
     * @return void
     */
    protected function _runDaemon()
    {
        if ($this->_getDaemonPid()) {
            exit("Receiver is already running\n");
        }
        $this->_prepareDaemonPidFile();
        echo "Starting updates receiver\n";

        $this->_workerPids = array();
        $pid = pcntl_fork();
        if ($pid) {
            // Write main daemon pid to file
            $this->_saveDaemonPid($pid);

            // Exit root process
            exit();
        }

        // Master daemon execution:
        declare(ticks = 1);
        pcntl_signal(SIGTERM, array($this, 'handleSignal'));
        pcntl_signal(SIGHUP, array($this, 'handleSignal'));
        pcntl_signal(SIGINT, array($this, 'handleSignal'));
        pcntl_signal(SIGUSR1, array($this, 'handleSignal'));
        $this->_spawnWorkers();
    }

    /**
     * Spawn update workers
     *
     * @return void
     */
    protected function _spawnWorkers()
    {
        while (true) {
            if (count($this->_workerPids) < count($this->_getWorkersToRun())) {

                $worker = $this->_getNextWorker();
                $pid = pcntl_fork();
                if (!$pid) {
                    // Worker process
                    $workerScript = __DIR__ . '/bin/queue_listener';
                    pcntl_exec($workerScript, array(
                        '--queue', $worker->getQueue()->getName(),
                        '--type', $worker->getQueue()->getType(),
                        '--memory_limit', $worker->getQueue()->getMemoryLimit(),
                        '--log-format', $this->getArg('log-format'),
                    ));
                    exit();
                } else {
                    // Parent process
                    $this->_workerPids[$worker->getId()] = $pid;
                }
            }

            $dead = pcntl_waitpid(-1, $status, WNOHANG);
            while ($dead > 0) {
                // Remove the gone pid from the array
                unset($this->_workerPids[array_search($dead, $this->_workerPids)]);

                // Look for another one
                $dead = pcntl_waitpid(-1, $status, WNOHANG);
            }
            sleep(1);
        }
    }

    /**
     * Get next worker
     *
     * @return Oggetto_Shell_Messenger_Worker
     */
    private function _getNextWorker()
    {
        $workers = $this->_getWorkersToRun();
        $remainingWorkers = array_diff(array_keys($workers), array_keys($this->_workerPids));
        $nextId = array_shift($remainingWorkers);
        return $workers[$nextId];
    }

    /**
     * Get workers to run
     *
     * @return Oggetto_Shell_Messenger_Worker[]
     */
    private function _getWorkersToRun()
    {
        $result = [];
        foreach ($this->_queues as $_queue) {
            foreach ($_queue->getWorkers() as $_worker) {
                $result[$_worker->getId()] = $_worker;
            }
        }

        return $result;
    }

    /**
     * Handle incoming signal
     *
     * @param integer $signal Signal
     * @return void
     */
    public function handleSignal($signal)
    {
        if (in_array($signal, array(SIGTERM, SIGHUP, SIGINT))) {
            $this->_sendSignalToWorkers($signal);
            unlink($this->_getPidFile());
            exit();
        } elseif ($signal == SIGUSR1) {
            // Respawn workers
            $this->_sendSignalToWorkers(SIGTERM);
            $this->_workerPids = array();
        }
    }

    /**
     * Send signal to workers
     *
     * @param integer $signal Signal
     * @return void
     */
    protected function _sendSignalToWorkers($signal)
    {
        foreach ($this->_workerPids as $pid) {
            posix_kill($pid, $signal);
        }
        foreach ($this->_workerPids as $pid) {
            pcntl_waitpid($pid, $status);
        }
    }

    /**
     * Print daemon status
     *
     * @return void
     */
    private function _printStatus()
    {
        $daemonPid = $this->_getDaemonPid();
        if (!$daemonPid) {
            echo "Pid file not found, daemon seems not to be running.\n";
            return;
        }

        echo "PID: {$daemonPid}.\n";
        if (file_exists("/proc/{$daemonPid}" )){
            echo "Process is running.\n";
        } else {
            echo "Process is not running.\n";
        }
    }

    /**
     * Retrieve usage help message
     *
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE
This program controls updates receiveng from Messenger.
Usage: php receiver.php <command> [options]

    Commands:
    start   Start the receiver daemon.
            Queues description should be passed as a second argument:

            $ php receiver.php start --queues foo:1:s,bar:2:d,baz:3:d

            In this example the following workers will be started:
             - one worker which listens `foo` queue. `s` means it will run messages in listener process
             - two workers which listen `bar` queue. `d` means that it will run each message in separate process
             - three workers which listen `baz` queue. `d` means that it will run each message in separate process

             More details about `start` options: https://github.com/OggettoWeb/messenger/wiki

    stop    Stop the receiver daemon
    reload  Re-spawn all receiver workers
    status  Show receiver status

    Options:
    --log-format    Log format (json or text). Text by default
USAGE;
    }
}

$shell = new Oggetto_Shell_Messenger_Receiver();
$shell->run();