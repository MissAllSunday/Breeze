<?php

declare(strict_types=1);


namespace Breeze\Util;

use Breeze\Breeze;
use Breeze\Traits\SettingsTrait;


class Editor
{
	use SettingsTrait;

	public const BREEZE_EDITOR_ID = Breeze::PATTERN . 'editor';

	public function createEditor(array $editorOptions = []): void
	{
		$editorOptions = array_merge([
			'id' => self::BREEZE_EDITOR_ID,
			'value' => '',
			'height' => '170px',
			'width' => '90%',
		], $editorOptions);

		$this->requireOnce('Subs-Editor');

		create_control_richedit($editorOptions);
	}
}
