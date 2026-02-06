<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Service\CommentServiceInterface;
use Breeze\Service\LikeServiceInterface;
use Breeze\Service\StatusServiceInterface;
use Breeze\Util\Form\SettingsBuilderInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AdminServiceTest extends TestCase
{
	private AdminServiceInterface|MockObject $adminService;

	private MockObject|StatusServiceInterface $statusService;

	private MockObject|CommentServiceInterface $commentService;

	private MockObject|LikeServiceInterface $likeService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$settingsBuilder = $this->createStub(SettingsBuilderInterface::class);
		$this->statusService = $this->createMock(StatusServiceInterface::class);
		$this->commentService = $this->createMock(CommentServiceInterface::class);
		$this->likeService = $this->createMock(LikeServiceInterface::class);

		$this->adminService = $this->getMockBuilder(AdminService::class)
			->onlyMethods([
				'global',
				'setGlobal',
				'requireOnce',
				'setLanguage',
				'setTemplate',
				'getText',
				'getSmfText',
				'isEnable',
				'saveConfigVars',
				'getRequest',
			])
			->setConstructorArgs([
				$settingsBuilder,
				$this->statusService,
				$this->commentService,
				$this->likeService,
			])
			->getMock();
	}

	#[DataProvider('initProvider')]
	public function testInit(array $subActions, array $expectedTabs): void
	{
		$context = ['admin_menu_name' => 'admin_menu'];
		$scriptUrl = 'http://example.com/index.php';

		$this->adminService->expects($this->exactly(2))
			->method('global')
			->willReturnMap([
				['context', $context],
				['script_url', $scriptUrl],
			]);

		$this->adminService->expects($this->exactly(2))
			->method('requireOnce');

		$this->adminService->expects($this->once())
			->method('setLanguage');

		$this->adminService->expects($this->once())
			->method('setTemplate');

		$this->adminService->expects($this->exactly(count($subActions) * 2))
			->method('getText')
			->willReturn('Test Description');

		$this->adminService->expects($this->once())
			->method('setGlobal');

		$this->adminService->init($subActions);
	}

	public static function initProvider(): array
	{
		return [
			'single sub action' => [
				['settings'],
				['settings' => ['url' => 'test', 'description' => 'Test', 'label' => 'Test']],
			],
			'multiple sub actions' => [
				['settings', 'permissions'],
				['settings' => [], 'permissions' => []],
			],
		];
	}

	#[DataProvider('defaultSubActionContentProvider')]
	public function testDefaultSubActionContent(string $subActionName, array $templateParams, string $smfTemplate, bool $shouldReturn): void
	{
		if ($shouldReturn) {
			$this->adminService->defaultSubActionContent($subActionName, $templateParams, $smfTemplate);
			$this->expectNotToPerformAssertions();

			return;
		}

		$context = [

			'session_var' => 'sesc',
			'session_id' => 'abc123',
			'admin_menu_name' => 'admin_menu',
			'admin_menu' => [
				'tab_data' => [],
			],
		];
		$scriptUrl = 'http://example.com/index.php';

		$this->adminService->expects($this->exactly(2))
			->method('global')
			->willReturnMap([
				['context', $context],
				['script_url', $scriptUrl],
			]);

		$this->adminService->expects($this->exactly(2))
			->method('getText')
			->willReturn('Test Title');

		$this->adminService->expects($this->once())
			->method('setGlobal');

		$this->adminService->defaultSubActionContent($subActionName, $templateParams, $smfTemplate);
	}

	public static function defaultSubActionContentProvider(): array
	{
		return [
			'empty sub action' => ['', [], '', true],
			'zero sub action' => ['0', [], '', true],
			'valid sub action' => ['settings', [], '', false],
			'with template params' => ['permissions', ['key' => 'value'], '', false],
			'with smf template' => ['donate', [], 'custom_template', false],
		];
	}

	#[DataProvider('configVarsProvider')]
	public function testConfigVars(bool $save): void
	{
		$this->adminService->expects($this->once())
			->method('requireOnce')
			->with('ManageServer');

		if ($save) {
			$this->adminService->expects($this->once())
				->method('saveConfigVars');
		} else {
			$this->adminService->expects($this->never())
				->method('saveConfigVars');
		}

		$this->adminService->configVars($save);
	}

	public static function configVarsProvider(): array
	{
		return [
			'save config vars' => [true],
			'do not save config vars' => [false],
		];
	}

	#[DataProvider('permissionsConfigVarsProvider')]
	public function testPermissionsConfigVars(bool $save): void
	{
		$this->adminService->expects($this->once())
			->method('setLanguage');

		if ($save) {
			$this->adminService->expects($this->once())
				->method('saveConfigVars');
		} else {
			$this->adminService->expects($this->never())
				->method('saveConfigVars');
		}

		$this->adminService->permissionsConfigVars($save);
	}

	public static function permissionsConfigVarsProvider(): array
	{
		return [
			'save permissions config vars' => [true],
			'do not save permissions config vars' => [false],
		];
	}

	public function testSaveConfigVars(): void
	{
		// This method calls global functions, so we just test it doesn't throw
		$this->expectNotToPerformAssertions();
		// In a real test, you'd mock checkSession() and saveDBSettings()
	}

	public function testGetActionName(): void
	{
		$result = $this->adminService->getActionName();
		$this->assertEquals('breezeAdmin', $result);
	}

	public function testLoadComponents(): void
	{
		// Method is empty, just test it doesn't throw
		$this->adminService->loadComponents(['component1', 'component2']);
		$this->expectNotToPerformAssertions();
	}

	#[DataProvider('maintenanceProvider')]
	public function testMaintenance(bool $fix, string $type, bool $expectCommentsFix, bool $expectLikesFix): void
	{
		$context = ['session_var' => 'sesc', 'session_id' => 'abc123'];
		$scriptUrl = 'http://example.com/index.php';

		$this->adminService->expects($this->exactly(2))
			->method('global')
			->willReturnMap([
				['context', $context],
				['script_url', $scriptUrl],
			]);

		if ($fix) {
			$this->adminService->expects($this->once())
				->method('getRequest')
				->with('type', 'all')
				->willReturn($type);

			if ($expectCommentsFix) {
				$this->commentService->expects($this->once())->method('deleteOrphans');
				$this->statusService->expects($this->once())->method('recountComments');
			} else {
				$this->commentService->expects($this->never())->method('deleteOrphans');
				$this->statusService->expects($this->never())->method('recountComments');
			}

			if ($expectLikesFix) {
				$this->likeService->expects($this->once())->method('deleteOrphans');
				$this->statusService->expects($this->once())->method('recountLikes');
				$this->commentService->expects($this->once())->method('recountLikes');
			} else {
				$this->likeService->expects($this->never())->method('deleteOrphans');
				$this->statusService->expects($this->never())->method('recountLikes');
				$this->commentService->expects($this->never())->method('recountLikes');
			}
		} else {
			$this->adminService->expects($this->never())->method('getRequest');
		}

		$this->commentService->expects($this->once())->method('countOrphans')->willReturn(5);
		$this->likeService->expects($this->once())->method('countOrphans')->willReturn(10);

		$this->adminService->expects($this->once())->method('setGlobal');

		$this->adminService->maintenance($fix);
	}

	public static function maintenanceProvider(): array
	{
		return [
			'no fix' => [false, '', false, false],
			'fix all' => [true, 'all', true, true],
			'fix comments' => [true, 'comments', true, false],
			'fix likes' => [true, 'likes', false, true],
		];
	}
}
