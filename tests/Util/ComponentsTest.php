<?php

declare(strict_types=1);

namespace Breeze\Util;

use PHPUnit\Framework\TestCase;

class ComponentsTest extends TestCase
{
	private Components $components;

	protected function setUp(): void
	{
		$this->components = new Components();

		// Initialize global context
		$GLOBALS['context'] = [
			'session_var' => 'foo',
			'session_id' => 'baz',
		];
		$GLOBALS['txt'] = [
			'Breeze_error_wrong_values' => 'Wrong values',
			'Breeze_error_empty' => 'Empty',
			'Breeze_page_no_status' => 'No status',
			'Breeze_error_generic' => 'Generic error',
			'Breeze_success_deleted_status' => 'Status deleted',
			'Breeze_success_deleted_comment' => 'Comment deleted',
			'Breeze_general_save' => 'Save',
			'Breeze_general_delete' => 'Delete',
			'Breeze_general_close' => 'Close',
			'Breeze_general_cancel' => 'Cancel',
			'Breeze_general_send' => 'Send',
			'Breeze_info_loading_end' => 'End',
			'Breeze_load_more' => 'Load more',
			'Breeze_info_empty_data' => 'Empty data',
			'Breeze_tabs_wall' => 'Wall',
			'Breeze_tabs_about' => 'About',
			'Breeze_tabs_activity' => 'Activity',
			'Breeze_tabs_buddies' => 'Buddies',
			'go_up' => 'Go up',
			'like' => 'Like',
			'unlike' => 'Unlike',
			'Breeze_action_like' => 'Like',
			'Breeze_action_comment' => 'Comment',
			'Breeze_action_delete' => 'Delete',
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

	public function testLoadUIVarsWithEmptyArray(): void
	{
		// This should not throw any errors
		$this->components->loadUIVars([]);
		$this->assertTrue(true);
	}

	public function testLoadUIVarsWithData(): void
	{
		$vars = [
			'testVar1' => 'value1',
			'testVar2' => ['key' => 'value'],
		];

		// This should not throw any errors
		$this->components->loadUIVars($vars);
		$this->assertTrue(true);
	}

	public function testLoadComponentsWithEmptyArray(): void
	{
		// This should not throw any errors
		$this->components->loadComponents([]);
		$this->assertTrue(true);
	}

	public function testLoadTxtVarsForWithGeneralComponent(): void
	{
		// This should not throw any errors
		$this->components->loadTxtVarsFor(['general']);
		$this->assertTrue(true);
	}

	public function testLoadTxtVarsForWithMultipleComponents(): void
	{
		// This should not throw any errors
		$this->components->loadTxtVarsFor(['general', 'tabs', 'like', 'actions']);
		$this->assertTrue(true);
	}

	public function testLoadTxtVarsForWithActionsComponent(): void
	{
		// Should load action bar labels without errors
		$this->components->loadTxtVarsFor(['actions']);
		$this->assertTrue(true);
	}

	public function testLoadTxtVarsForWithNonExistentComponent(): void
	{
		// Should skip non-existent components without error
		$this->components->loadTxtVarsFor(['nonexistent']);
		$this->assertTrue(true);
	}

	public function testCssFileConstant(): void
	{
		$this->assertEquals('breeze.css', Components::CSS_FILE);
	}

	public function testFolderConstant(): void
	{
		$this->assertEquals('breezeComponents/', Components::FOLDER);
	}

	public function testMainJsFileConstant(): void
	{
		$this->assertStringContainsString('breezeComponents/', Components::MAIN_JS_FILE);
		$this->assertStringEndsWith('.js', Components::MAIN_JS_FILE);
	}
}
