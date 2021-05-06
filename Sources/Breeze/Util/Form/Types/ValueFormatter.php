<?php

declare(strict_types=1);


namespace Breeze\Util\Form\Types;

use Breeze\Traits\TextTrait;

abstract class ValueFormatter
{
	use TextTrait;

	public const FORMATTER_DIR = 'Types';

	public const FORMATTER_TYPE = 'Type';

	public static function getNameSpace(): string
	{
		return __NAMESPACE__ . '\\';
	}
}
