<?php

declare(strict_types=1);

namespace Breeze\Util;

class Folder
{
	public static function getFilesInFolder(string $directoryPath): array
	{
		return !is_dir($directoryPath) ? [] : array_diff(scandir($directoryPath), ['..', '.']);
	}
}
