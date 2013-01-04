<?php namespace Illuminate\Events;

class Subscriber implements EventSubscriberInterface {

	/**
	 * Get the events listened to by the subscriber.
	 *
	 * @return array
	 */
	public static function subscribes()
	{
		return array();
	}

	/**
	 * Get the events listened to by the subscriber.
	 *
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return static::subscribes();
	}

}