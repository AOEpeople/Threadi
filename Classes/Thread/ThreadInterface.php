<?php
/**
 * Thread interface
 *
 * @author Daniel Pötzinger
 */
interface Threadi_Thread_ThreadInterface extends Threadi_ReadyAskableInterface {

	/**
	 * @param callback $callback
	 * @param bool $killSelfOnExit
	 */
	public function __construct($callback = NULL, $killSelfOnExit = FALSE);

	/**
	 * @return int
	 */
	public function getThreadId();

	/**
	 * checks if the child thread is alive
	 *
	 * @return boolean
	 */
	public function isAlive();

	/**
	 * starts the thread, all the parameters are
	 * passed to the callback function
	 *
	 * @return void
	 */
	public function start();

	/**
	 * attempts to stop the thread
	 * returns true on success and false otherwise
	 *
	 * @param integer $signal - SIGKILL/SIGTERM
	 * @param boolean $wait
	 */
	public function stop($signal = SIGKILL, $wait = false);
}
