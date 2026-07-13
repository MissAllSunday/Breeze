<?php

declare(strict_types=1);

namespace Breeze\Util;

use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
	private Response $response;

	protected function setUp(): void
	{
		$this->response = new Response();

		// Initialize globals
		$GLOBALS['context'] = [
			'session_var' => 'foo',
			'session_id' => 'baz',
		];
		$GLOBALS['txt'] = [
			'Breeze_success_test' => 'Test success message',
			'Breeze_error_test' => 'Test error message',
			'Breeze_error_server' => 'Server error: %s',
		];
	}

	protected function tearDown(): void
	{
		// Restore global state
		$GLOBALS['context'] = [
			'session_var' => 'foo',
			'session_id' => 'baz',
			'cust_profile_fields_placement' => [
				'standard',
				'icons',
				'above_signature',
				'below_signature',
				'below_avatar',
				'above_member',
				'bottom_poster',
				'before_member',
				'after_member',
			],
		];
	}

	public function testResponsePropertyInitialization(): void
	{
		$reflection = new \ReflectionClass($this->response);
		$property = $reflection->getProperty('response');
		$value = $property->getValue($this->response);

		$this->assertIsArray($value);
		$this->assertArrayHasKey('message', $value);
		$this->assertArrayHasKey('content', $value);
		$this->assertEquals('', $value['message']);
		$this->assertEquals([], $value['content']);
	}

	public function testSuccessMethodExists(): void
	{
		$this->assertTrue(method_exists($this->response, 'success'));
	}

	public function testErrorMethodExists(): void
	{
		$this->assertTrue(method_exists($this->response, 'error'));
	}

	public function testPrintMethodExists(): void
	{
		$this->assertTrue(method_exists($this->response, 'print'));
	}

	public function testRedirectMethodExists(): void
	{
		$this->assertTrue(method_exists($this->response, 'redirect'));
	}

	public function testResponseUsesRequestTrait(): void
	{
		$this->assertTrue(method_exists($this->response, 'getRequest'));
		$this->assertTrue(method_exists($this->response, 'isRequestSet'));
	}

	public function testResponseUsesTextTrait(): void
	{
		$this->assertTrue(method_exists($this->response, 'getText'));
		$this->assertTrue(method_exists($this->response, 'getSmfText'));
	}
}
