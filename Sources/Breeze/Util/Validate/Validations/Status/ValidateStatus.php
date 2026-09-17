<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Util\Validate\Validations\ValidateActions;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class ValidateStatus extends ValidateActions implements ValidateActionsInterface
{
	public function __construct(
		protected DeleteStatus $deleteStatus,
		protected PostStatus $postStatus,
		protected StatusByProfile $statusByProfile
	) {
	}

	/**
	 * Sub-action names mirror StatusController::SUB_ACTIONS. They are kept as
	 * literals on purpose: referencing the controller constant here would invert
	 * the dependency, since controllers already depend on this namespace.
	 *
	 * `wall` and `single` are explicit no-ops - both read their input straight
	 * from the request (`cursor` / `id`) and never touch `$this->data`.
	 * `total` shares `profile`'s contract: it reads `$this->data['wall_id']` and
	 * must be validated against a real member.
	 *
	 * @return array<string, ValidateDataInterface | null>
	 */
	public function validators(): array
	{
		return [
			'profile' => $this->statusByProfile,
			'total' => $this->statusByProfile,
			'postStatus' => $this->postStatus,
			'deleteStatus' => $this->deleteStatus,
			'wall' => null,
			'single' => null,
		];
	}
}
