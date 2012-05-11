<?php namespace Illuminate\Events;

class Dispatcher {

	/**
	 * All of the registered events.
	 *
	 * @var array
	 */
	protected $events = array();

	/**
	 * Register an evenet listener.
	 *
	 * @param  string  $event
	 * @param  mixed   $callable
	 * @return void
	 */
	public function listen($event, $callable)
	{
		if ( ! is_callable($callable))
		{
			throw new \InvalidArgumentException("Event listener must be callable.");
		}

		$this->events[$event][] = $callable;
	}

	/**
	 * Fire all of the listeners for a given event.
	 *
	 * @param  string  $event
	 * @param  array   $payload
	 * @return array
	 */
	public function fire($event, array $payload = array())
	{
		$responses = array();

		foreach ($this->events[$event] as $callable)
		{
			$responses[] = call_user_func_array($callable, $payload);
		}

		return $responses;
	}

}