<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

class ValidateComment extends ValidateData implements ValidateDataInterface
{
	protected const PARAMS = [
		'posterId' => 0,
		'statusOwnerId' => 0,
		'profileOwnerId' => 0,
		'statusId' => 0,
		'body' => '',
	];

	protected const STEPS = [
		'compare',
		'clean',
	];

	public function getSteps(): array
	{
		return self::STEPS;
	}

	public function getParams(): array
	{
		return self::PARAMS;
	}
}
