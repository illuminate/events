<?php namespace Illuminate\Events;

use Illuminate\Support\ServiceProvider;

class EventsServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @param  Illuminate\Foundation\Application  $app
	 * @return void
	 */
	public function register($app)
	{
		$app['events'] = $app->share(function($app)
		{
			return new Dispatcher;
		});
	}

}