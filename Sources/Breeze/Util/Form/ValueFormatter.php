<?php

declare(strict_types=1);


namespace Breeze\Util\Form;

use Breeze\Traits\TextTrait;
use Breeze\Util\Folder;

abstract class ValueFormatter implements ValueFormatterInterface
{
	use TextTrait;

	private const FORMATTER_SUFFIX = 'Formatter';
	private const FORMATTERS_DIR = __DIR__ . '/Formatters';
	private const FORMATTERS_NAMESPACE = 'Breeze\Util\Form\Formatters\\';

	public static function getFormatters(): array
	{
		$formatters = [];

		foreach (Folder::getFilesInFolder(self::FORMATTERS_DIR) as $formatterFile)
		{
			$formatterFileInfo = pathinfo($formatterFile, PATHINFO_FILENAME);

			$formatterKey = strtolower(
				str_replace(self::FORMATTER_SUFFIX, '', $formatterFileInfo)
			);

			$formatterClass = self::FORMATTERS_NAMESPACE . $formatterFileInfo;

			$formatters[$formatterKey] = new $formatterClass();
		}

		return $formatters;
	}
}
