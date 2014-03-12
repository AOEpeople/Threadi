<?php
/**
 * Thread Class based on the implementation http://blog.motane.lu/2009/01/02/multithreading-in-php/
 * from Tudor Barbu
 *
 * @author Daniel Pötzinger
 */
class Threadi_Thread_PHPThread extends Threadi_Thread_AbstractThread implements Threadi_Thread_ThreadInterface {

	/**
	 * @var integer
	 */
	protected $parentId;

	/**
	 * Constructor
	 *
	 * @param callback $callback
	 * @param bool $killSelfOnExit
	 * @throws Exception
	 */
	public function __construct($callback = NULL, $killSelfOnExit = FALSE) {
		if (! function_exists('pcntl_fork')) {
			throw new Exception('PCNTL functions not available on this PHP installation');
		}
		parent::__construct($callback, $killSelfOnExit);
	}

	/**
	 * Checks if the child thread is alive
	 *
	 * @return boolean
	 */
	public function isAlive() {
		$this->requireStart();
		$pid = pcntl_waitpid($this->threadId, $status, WNOHANG);
		return ($pid === 0);
	}

	/**
	 * Starts the thread, all the parameters are
	 * passed to the callback function
	 *
	 * @throws Exception
	 */
	public function start() {
		$id = pcntl_fork();
		if ($id == - 1) {
			throw new Exception('Forking was not possible');
		}
		if ($id) {
			// parent thread gets child id
			$this->threadId = $id;
			$this->started = TRUE;
			return $this->threadId;
		} else {
			// child process
			// 1 register callback for kill
			pcntl_signal(SIGTERM, array(
				$this, 'signalHandler'
			));
			$args = func_get_args();
			$this->executeCallback($this->callback, $args);
			if ($this->killSelfOnExit) {
				// avoid Exception: Thread was not started yet!
				posix_kill(getmypid(), SIGKILL);
			} else {
				exit(0);
			}
		}
	}

	/**
	 * Attempts to stop the thread
	 * returns true on success and false otherwise
	 *
	 * @param integer $signal - SIGKILL/SIGTERM
	 * @param boolean $wait
	 * @return void
	 */
	public function stop($signal = SIGKILL, $wait = false) {
		$this->requireStart();
		if ($this->isAlive()) {
			posix_kill($this->threadId, $signal);
			if ($wait) {
				$this->waitTillReady();
			}
		}
	}

	/**
	 * Wait until ready
	 *
	 * @return void
	 */
	public function waitTillReady() {
		$this->requireStart();
		$status = 0;
		pcntl_waitpid($this->threadId, $status);
	}

	/**
	 * Signal handler
	 *
	 * @param integer $signal
	 */
	protected function signalHandler($signal) {
		switch ($signal) {
			case SIGTERM:
				exit(0);
				break;
		}
	}
}
