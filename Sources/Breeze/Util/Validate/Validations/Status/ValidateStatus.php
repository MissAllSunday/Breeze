<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateStatus extends ValidateData
{
	protected StatusServiceInterface $statusService;

	public function __construct(
		UserServiceInterface $userService,
		StatusServiceInterface $statusService
	) {
		$this->statusService = $statusService;

		parent::__construct($userService);
	}

	public function getParams(): array
	{
		return $this->getData();
	}
}
