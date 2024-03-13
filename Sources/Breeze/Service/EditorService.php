<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Traits\SettingsTrait;
use Breeze\Traits\TextTrait;

class EditorService implements EditorServiceInterface
{
	use SettingsTrait;
	use TextTrait;

	public function setEditor(): void
	{
		$this->requireOnce('Subs-Editor');

		create_control_richedit([
			'id' => Breeze::NAME,
			'value' => '',
			'labels' => [
				'post_button' => $this->getText('general_send'),
			],
			'preview_type' => 2,
			'required' => true,
		]);
	}
}
