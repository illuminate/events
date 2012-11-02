<?php namespace Illuminate\Events;

use Closure;
use Illuminate\Container;

class Dispatcher {

	/**
	 * All of the registered events.
	 *
	 * @var array
	 */
	protected $events = array();

	/**
	 * All of the queued event payloads.
	 *
	 * @var array
	 */
	protected $queued = array();

	/**
	 * The IoC container instance.
	 *
	 * @var Illuminate\Container
	 */
	protected $container;

	/**
	 * Register a global event listener.
	 *
	 * @param  mixed   $callable
	 * @return void
	 */
	public function all($callable)
	{
		return $this->listen('*', $callable);
	}

	/**
	 * Register an event listener.
	 *
	 * @param  string  $event
	 * @param  mixed   $callable
	 * @return void
	 */
	public function listen($event, $callable)
	{
		$this->events[$event][] = $this->buildEventListener($callable);
	}

	/**
	 * Override all other registered event listeners.
	 *
	 * @param  string  $event
	 * @param  mixed   $callable
	 * @return void
	 */
	public function override($event, $callable)
	{
		$this->events[$event] = array($this->buildEventListener($callable));
	}

	/**
	 * Add a payload to an event queue.
	 *
	 * @param  string  $queue
	 * @param  string  $key
	 * @param  array   $payload
	 * @return void
	 */
	public function queue($queue, $key, array $payload = array())
	{
		$this->queued[$queue][$key] = $payload;
	}

	/**
	 * Register an event "flusher" to handles the flushing of a queue.
	 *
	 * @param  string   $queue
	 * @param  Closure  $callable
	 * @return void
	 */
	public function flusher($queue, $callable)
	{
		$this->flushers[$queue][] = $this->buildEventListener($callable);
	}

	/**
	 * Build the event listener callback.
	 *
	 * @param  mixed    $callable
	 * @return Closure
	 */
	protected function buildEventListener($callable)
	{
		if (is_string($callable) and isset($this->container))
		{
			return $this->buildClassListenerCallback($callable);
		}

		if ( ! is_callable($callable))
		{
			throw new \InvalidArgumentException("Event listener must be callable.");
		}

		return $callable;
	}

	/**
	 * Build an event listener callback from a string.
	 *
	 * @param  string   $callback
	 * @return Closure
	 */
	protected function buildClassListenerCallback($callable)
	{
		$container = $this->container;

		// For even callbacks that are strings, we will resolve them from the container
		// and call the handle method on the instance, passing in the arguments that
		// are passed into our handler, which allows for testable, class handlers.
		return function() use ($callable, $container)
		{
			$callable = array($container->make($callable), 'handle');

			return call_user_func_array($callable, func_get_args());
		};
	}

	/**
	 * Run an event flusher for all of the payloads in a given queue.
	 *
	 * @param  string  $queue
	 * @return void
	 */
	public function flush($queue)
	{
		foreach ($this->flushers[$queue] as $flusher)
		{
			if ( ! isset($this->queued[$queue]))
			{
				return;
			}

			// We will simply spin through each payload registered for this event and
			// fire the flushers, passing each payloads as we go, which allows all
			// the events on the queue to be processed by these flushers easily.
			foreach ($this->queued[$queue] as $key => $payload)
			{
				array_unshift($payload, $key);

				call_user_func_array($flusher, $payload);
			}
		}
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

		// First we'll fire all of the global event listners passing the name of
		// the event followed by the array of parameters. These are good for
		// profiling, debugging and viewing tfhe application event calls.
		foreach ($this->getListeners('*') as $callable)
		{
			$response = call_user_func($callable, $event, $payload);
		}

		foreach ($this->getListeners($event) as $callable)
		{
			$response = call_user_func_array($callable, $payload);

			// If the response is not null and halting is enabled, we will stop firing
			// the events and return the response. This allows us to get the first
			// valid response from a listener and return it back to the caller.
			if ( ! is_null($response) and $halt) return $response;

			$responses[] = $response;
		}

		return $responses;
	}

	/**
	 * Get the listeners for a given event.
	 *
	 * @param  string  $event
	 * @return array
	 */
	protected function getListeners($event)
	{
		return isset($this->events[$event]) ? $this->events[$event] : array();
	}

	/**
	 * Set the IoC container instance.
	 *
	 * @param  Illuminate\Container  $container
	 * @return void
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

}