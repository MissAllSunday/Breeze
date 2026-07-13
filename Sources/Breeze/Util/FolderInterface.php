<?php

declare(strict_types=1);

namespace Breeze\Util;

interface FolderInterface
{
	public static function getFilesInFolder(string $directoryPath): array;
}
