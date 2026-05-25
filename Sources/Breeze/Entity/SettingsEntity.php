<?php

declare(strict_types=1);


namespace Breeze\Entity;

class SettingsEntity
{
	public const string MASTER = 'master';
	public const string MAX_FLOOD_NUM = 'maxFloodNum';
	public const string MAX_FLOOD_MINUTES = 'maxFloodMinutes';
	public const string TYPE_CHECK = 'check';
	public const string TYPE_INT = 'int';
	public const string TYPE_TEXT = 'text';
	public const string TYPE_TEXTAREA = 'textArea';
	public const string TYPE_SELECT = 'select';
	public const string PF_TEXT_KEY = 'custom_profile_placement_';

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class();
	}

	public static function getColumns(): array
	{
		return [
			self::MASTER => self::TYPE_CHECK,
			self::MAX_FLOOD_NUM => self::TYPE_INT,
			self::MAX_FLOOD_MINUTES => self::TYPE_INT,
		];
	}

	public static function defaultValues(): array
	{
		return [
			self::TYPE_CHECK => false,
			self::TYPE_INT => 0,
			self::TYPE_TEXT => '',
			self::TYPE_TEXTAREA => '',
			self::TYPE_SELECT => [],
		];
	}
}
