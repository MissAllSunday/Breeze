<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Service\SecurityServiceInterface;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
	protected function setUp(): void
	{
		$GLOBALS['context'] = [
			'session_var' => 'foo',
			'session_id' => 'baz',
		];
		$GLOBALS['txt'] = [
			'Breeze_success_test' => 'Test success message',
			'Breeze_error_test' => 'Test error message',
			'Breeze_error_server' => 'Server error: %s',
			'Breeze_error_default' => 'Default error message',
		];
	}

	public function testSuccessMethod(): void
	{
		$securityStub = $this->createStub(SecurityServiceInterface::class);
		$securityStub->method('createToken')
			->willReturn([
				'breeze_token_var' => 'test_var',
				'breeze_token' => 'test_value',
			]);

		$emitterMock = $this->createMock(ResponseEmitterInterface::class);
		$emitterMock->expects($this->once())
			->method('emit')
			->with(
				[
					'message' => 'Test success message',
					'content' => [],
					'token' => [
						'var' => 'test_var',
						'value' => 'test_value',
					],
				],
				ResponseInterface::OK,
				ResponseInterface::CONTENT_TYPE
			);

		$response = new Response($securityStub, $emitterMock);
		$response->success('test');
	}

	public function testSuccessMethodWithContent(): void
	{
		$securityStub = $this->createStub(SecurityServiceInterface::class);
		$securityStub->method('createToken')
			->willReturn([
				'breeze_token_var' => 'test_var',
				'breeze_token' => 'test_value',
			]);

		$emitterMock = $this->createMock(ResponseEmitterInterface::class);
		$emitterMock->expects($this->once())
			->method('emit')
			->with(
				[
					'message' => 'Test success message',
					'content' => ['foo' => 'bar'],
					'token' => [
						'var' => 'test_var',
						'value' => 'test_value',
					],
				],
				ResponseInterface::OK,
				ResponseInterface::CONTENT_TYPE
			);

		$response = new Response($securityStub, $emitterMock);
		$response->success('test', ['foo' => 'bar']);
	}

	public function testErrorMethod(): void
	{
		$securityStub = $this->createStub(SecurityServiceInterface::class);
		$securityStub->method('createToken')
			->willReturn([
				'breeze_token_var' => 'err_var',
				'breeze_token' => 'err_value',
			]);

		$emitterMock = $this->createMock(ResponseEmitterInterface::class);
		$emitterMock->expects($this->once())
			->method('emit')
			->with(
				[
					'message' => sprintf($GLOBALS['txt']['Breeze_error_server'], 'Test error message'),
					'content' => [],
					'token' => [
						'var' => 'err_var',
						'value' => 'err_value',
					],
				],
				ResponseInterface::NOT_FOUND,
				ResponseInterface::CONTENT_TYPE
			);

		$response = new Response($securityStub, $emitterMock);
		$response->error('test');
	}

	public function testErrorMethodWithEmptyMessage(): void
	{
		$securityStub = $this->createStub(SecurityServiceInterface::class);
		$securityStub->method('createToken')
			->willReturn([
				'breeze_token_var' => 'err_var',
				'breeze_token' => 'err_value',
			]);

		$emitterMock = $this->createMock(ResponseEmitterInterface::class);
		$emitterMock->expects($this->once())
			->method('emit')
			->with(
				[
					'message' => '',
					'content' => [],
					'token' => [
						'var' => 'err_var',
						'value' => 'err_value',
					],
				],
				ResponseInterface::NOT_FOUND,
				ResponseInterface::CONTENT_TYPE
			);

		$response = new Response($securityStub, $emitterMock);
		$response->error('');
	}

	public function testAppendToken(): void
	{
		$securityMock = $this->createMock(SecurityServiceInterface::class);
		$securityMock->expects($this->once())
			->method('createToken')
			->with(ResponseInterface::CSRF_TOKEN_ACTION, 'get')
			->willReturn([
				'breeze_token_var' => 'token_key',
				'breeze_token' => 'token_secret',
			]);

		$emitterStub = $this->createStub(ResponseEmitterInterface::class);

		$response = new Response($securityMock, $emitterStub);
		$result = $response->appendToken(['data' => 'sample']);

		$this->assertArrayHasKey('token', $result);
		$this->assertEquals('token_key', $result['token']['var']);
		$this->assertEquals('token_secret', $result['token']['value']);
		$this->assertEquals('sample', $result['data']);
	}

	public function testRedirectMethod(): void
	{
		$securityStub = $this->createStub(SecurityServiceInterface::class);

		$emitterMock = $this->createMock(ResponseEmitterInterface::class);
		$emitterMock->expects($this->once())
			->method('redirectExit')
			->with('index.php?action=admin');

		$response = new Response($securityStub, $emitterMock);
		$response->redirect('index.php?action=admin');
	}
}
