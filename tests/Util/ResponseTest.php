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

	public function testConstants(): void
	{
		$this->assertEquals('content-type: application/json', Response::CONTENT_TYPE);
		$this->assertEquals('error', Response::ERROR_TYPE);
		$this->assertEquals('info', Response::INFO_TYPE);
		$this->assertEquals('success', Response::SUCCESS_TYPE);
		$this->assertEquals('error_server', Response::DEFAULT_ERROR_KEY);
		$this->assertEquals(200, Response::OK);
		$this->assertEquals(201, Response::CREATED);
		$this->assertEquals(202, Response::ACCEPTED);
		$this->assertEquals(204, Response::NO_CONTENT);
		$this->assertEquals(404, Response::NOT_FOUND);
		$this->assertEquals(400, Response::BAD_REQUEST);
		$this->assertEquals(401, Response::UNAUTHORIZED);
		$this->assertEquals(405, Response::METHOD_NOT_ALLOWED);
		$this->assertEquals(406, Response::NOT_ACCEPTABLE);
	}

	public function testMessageTypesConstant(): void
	{
		$this->assertIsArray(Response::MESSAGE_TYPES);
		$this->assertCount(3, Response::MESSAGE_TYPES);
		$this->assertContains('error', Response::MESSAGE_TYPES);
		$this->assertContains('info', Response::MESSAGE_TYPES);
		$this->assertContains('success', Response::MESSAGE_TYPES);
	}

	public function testResponsePropertyInitialization(): void
	{
		$reflection = new \ReflectionClass($this->response);
		$property = $reflection->getProperty('response');
		$property->setAccessible(true);
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
