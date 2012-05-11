<?php

use Illuminate\Events\Dispatcher;

class DispatcherTest extends PHPUnit_Framework_TestCase {

	public function testBasicEventFiring()
	{
		$e = new Dispatcher;
		$e->listen('foo', function()
		{
			return 'bar';
		});
		$e->listen('foo', function()
		{
			return 'baz';
		});
		$responses = $e->fire('foo');

		$this->assertEquals(2, count($responses));
		$this->assertEquals('bar', $responses[0]);
		$this->assertEquals('baz', $responses[1]);
	}


	public function testFirstMethod()
	{
		$GLOBALS['__event.test.foo'] = false;
		$e = new Dispatcher;
		$e->listen('foo', function()
		{
			return 'baz';
		});
		$e->listen('foo', function()
		{
			$GLOBALS['__event.test.foo'] = true;
			return 'bar';
		});
		$response = $e->first('foo');

		$this->assertEquals('baz', $response);
		$this->assertFalse($GLOBALS['__event.test.foo']);

		unset($GLOBALS['__event.test.foo']);
	}

}