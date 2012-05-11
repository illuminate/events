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
	 * Get the first event listener response.
	 *
	 * @param  string  $event
	 * @param  array   $payload
	 * @return mixed
	 */
	public function first($event, array $payload = array())
	{
		return $this->fire($event, $payload, true);
	}

	/**
	 * Fire all of the listeners for a given event.
	 *
	 * @param  string  $event
	 * @param  array   $payload
	 * @param  bool    $halt
	 * @return array
	 */
	public function fire($event, array $payload = array(), $halt = false)
	{
		$responses = array();

		foreach ($this->events[$event] as $callable)
		{
			$response = call_user_func_array($callable, $payload);

			// If the response is not null and halting is enabled, we will stop
			// firing the events and return the response. This allows us to
			// get just the first valid response from an event listener.
			if ( ! is_null($response) and $halt)
			{
				return $response;
			}

			$responses[] = $response;
		}

		return $responses;
	}

}