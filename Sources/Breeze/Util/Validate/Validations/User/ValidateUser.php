<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\User;

use Breeze\Util\Validate\Validations\ValidateActions;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class ValidateUser extends ValidateActions implements ValidateActionsInterface
{
	public function __construct(
		protected UserSettings $userSettings
	) {
	}

	/**
	 * Sub-action names mirror UserSettingsController::SUB_ACTIONS. `main` only
	 * renders the settings form, so it is an explicit no-op; `save` is the single
	 * write path and must go through the UserSettings validator.
	 *
	 * @return array<string, ValidateDataInterface | null>
	 */
	public function validators(): array
	{
		return [
			'save' => $this->userSettings,
			'main' => null,
		];
	}
}
