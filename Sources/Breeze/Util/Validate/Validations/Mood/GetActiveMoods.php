<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;

class GetActiveMoods extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [];

	protected const SUCCESS_KEY = 'moodCreated';

	public function __construct(
		protected Allow $validateAllow,
		protected BaseRepositoryInterface $repository
	) {
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function isValid(): void
	{
	}
}
