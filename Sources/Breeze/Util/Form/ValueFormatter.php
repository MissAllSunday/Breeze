<?php

declare(strict_types=1);


namespace Breeze\Util\Form;

use Breeze\Traits\TextTrait;

abstract class ValueFormatter implements ValueFormatterInterface
{
	use TextTrait;

	private const FORMATTER_SUFFIX = 'Formatter';

	public static function getFormatters(): array
	{
		$formatters = [];

		foreach(get_declared_classes() as $loadedClass) {
			if ( is_subclass_of($loadedClass, self::class) ){
				$formatters[
					strtolower(
						str_replace(self::FORMATTER_SUFFIX, '', $loadedClass)
					)
				] = new $loadedClass();
			}
		}

		return $formatters;
	}
}
