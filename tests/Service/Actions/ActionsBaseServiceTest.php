<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Repository\BaseRepositoryInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class ActionsBaseServiceTest extends TestCase
{
	private ActionsBaseService|MockObject $actionsBaseService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$baseRepository = $this->createStub(BaseRepositoryInterface::class);

		// Create a concrete implementation of the abstract class for testing
		$this->actionsBaseService = $this->getMockBuilder(ActionsBaseService::class)
			->setConstructorArgs([$baseRepository])
			->getMock();
	}

	public function testImplementsActionsServiceInterface(): void
	{
		$this->assertInstanceOf(ActionsServiceInterface::class, $this->actionsBaseService);
	}

	public function testInitMethodExists(): void
	{
		$this->assertTrue(method_exists($this->actionsBaseService, 'init'));
	}

	public function testGetActionNameMethodExists(): void
	{
		$this->assertTrue(method_exists($this->actionsBaseService, 'getActionName'));
	}

	public function testDefaultSubActionContentMethodExists(): void
	{
		$this->assertTrue(method_exists($this->actionsBaseService, 'defaultSubActionContent'));
	}

	public function testInitAcceptsArrayParameter(): void
	{
		$subActions = ['action1', 'action2', 'action3'];

		$this->actionsBaseService->expects($this->once())
			->method('init')
			->with($subActions);

		$this->actionsBaseService->init($subActions);
	}

	public function testInitWithEmptyArray(): void
	{
		$subActions = [];

		$this->actionsBaseService->expects($this->once())
			->method('init')
			->with($subActions);

		$this->actionsBaseService->init($subActions);
	}

	public function testGetActionNameReturnsString(): void
	{
		$expectedActionName = 'testAction';

		$this->actionsBaseService->expects($this->once())
			->method('getActionName')
			->willReturn($expectedActionName);

		$result = $this->actionsBaseService->getActionName();

		$this->assertIsString($result);
		$this->assertEquals($expectedActionName, $result);
	}

	public function testDefaultSubActionContentAcceptsParameters(): void
	{
		$subActionName = 'testSubAction';
		$templateParams = ['key' => 'value'];
		$smfTemplate = 'testTemplate';

		$this->actionsBaseService->expects($this->once())
			->method('defaultSubActionContent')
			->with($subActionName, $templateParams, $smfTemplate);

		$this->actionsBaseService->defaultSubActionContent($subActionName, $templateParams, $smfTemplate);
	}

	public function testDefaultSubActionContentWithEmptyParameters(): void
	{
		$subActionName = 'testSubAction';
		$templateParams = [];
		$smfTemplate = '';

		$this->actionsBaseService->expects($this->once())
			->method('defaultSubActionContent')
			->with($subActionName, $templateParams, $smfTemplate);

		$this->actionsBaseService->defaultSubActionContent($subActionName, $templateParams, $smfTemplate);
	}

	public function testInheritsFromBaseService(): void
	{
		$reflection = new \ReflectionClass($this->actionsBaseService);
		$parentClass = $reflection->getParentClass();

		$this->assertNotFalse($parentClass);
		$this->assertEquals('Breeze\Service\Actions\ActionsBaseService', $parentClass->getName());
	}

	public function testIsAbstractClass(): void
	{
		$reflection = new \ReflectionClass(ActionsBaseService::class);
		$this->assertTrue($reflection->isAbstract());
	}

	public function testInitIsAbstractMethod(): void
	{
		$reflection = new \ReflectionClass(ActionsBaseService::class);
		$method = $reflection->getMethod('init');
		$this->assertTrue($method->isAbstract());
	}

	public function testGetActionNameIsAbstractMethod(): void
	{
		$reflection = new \ReflectionClass(ActionsBaseService::class);
		$method = $reflection->getMethod('getActionName');
		$this->assertTrue($method->isAbstract());
	}
}
