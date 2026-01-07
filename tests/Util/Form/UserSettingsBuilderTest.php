<?php

declare(strict_types=1);

namespace Breeze\Util\Form;

use PHPUnit\Framework\TestCase;

class UserSettingsBuilderTest extends TestCase
{
	private UserSettingsBuilder $builder;

	protected function setUp(): void
	{
		// Initialize globals
		$GLOBALS['context'] = [
			'session_var' => 'foo',
			'session_id' => 'baz',
		];
		$GLOBALS['txt'] = [
			'Breeze_user_settings_enable' => 'Enable',
			'Breeze_user_settings_enable_desc' => 'Enable description',
			'Breeze_user_settings_master' => 'Master',
			'Breeze_user_settings_master_desc' => 'Master description',
			'Breeze_user_settings_pagination' => 'Pagination',
			'Breeze_user_settings_pagination_desc' => 'Pagination description',
			'Breeze_user_settings_wall_posts_show' => 'Wall posts show',
			'Breeze_user_settings_wall_posts_show_desc' => 'Wall posts show description',
			'Breeze_user_settings_wall_comments_show' => 'Wall comments show',
			'Breeze_user_settings_wall_comments_show_desc' => 'Wall comments show description',
			'Breeze_user_settings_activity_log_show' => 'Activity log show',
			'Breeze_user_settings_activity_log_show_desc' => 'Activity log show description',
			'Breeze_user_settings_activity_log_comments_show' => 'Activity log comments show',
			'Breeze_user_settings_activity_log_comments_show_desc' => 'Activity log comments show description',
		];

		$this->builder = new UserSettingsBuilder();
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

	public function testImplementsUserSettingsBuilderInterface(): void
	{
		$this->assertInstanceOf(UserSettingsBuilderInterface::class, $this->builder);
	}

	public function testSetFormWithEmptyFormValues(): void
	{
		$formOptions = [
			'title' => 'Test Form',
			'elements' => [],
		];

		$this->builder->setForm($formOptions);

		// Should not throw any errors
		$this->assertTrue(true);
	}

	public function testSetFormWithFormValues(): void
	{
		$formOptions = [
			'title' => 'Test Form',
			'elements' => [],
		];

		$formValues = [
			'enable' => true,
			'master' => false,
		];

		$this->builder->setForm($formOptions, $formValues);

		// Should not throw any errors
		$this->assertTrue(true);
	}

	public function testDisplayReturnsString(): void
	{
		$formOptions = [
			'title' => 'Test Form',
			'elements' => [],
		];

		$this->builder->setForm($formOptions);
		$result = $this->builder->display();

		$this->assertIsString($result);
	}

	public function testBuilderUsesTextTrait(): void
	{
		$this->assertTrue(method_exists($this->builder, 'getText'));
		$this->assertTrue(method_exists($this->builder, 'getSmfText'));
	}
}
