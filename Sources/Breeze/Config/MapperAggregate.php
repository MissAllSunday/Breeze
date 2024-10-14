<?php

declare(strict_types=1);

namespace Breeze\Config;

use Breeze\Util\Folder;

class MapperAggregate
{
	public const MAPPERS_FOLDER = __DIR__ . '/Mappers';
	public const MAPPER_KEY = 'Mapper';

	protected static array $mappers = [];

	public function buildMappers(): void
	{
		if (!empty(self::$mappers)) {
			return;
		}

		$scannedMappers = Folder::getFilesInFolder(self::MAPPERS_FOLDER);

		foreach ($scannedMappers as $mapperFile) {
			$mapperFileInfo = pathinfo($mapperFile, \PATHINFO_FILENAME);
			$mapperKey = str_replace(self::MAPPER_KEY, '', $mapperFileInfo);

			self::$mappers[$mapperKey] = $mapperFile;
		}
	}

	public function getMappers(): array
	{
		$this->buildMappers();

		return self::$mappers;
	}
}
