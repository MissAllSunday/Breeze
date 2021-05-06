<?php

declare(strict_types=1);

namespace Breeze\Config;

use Breeze\Util\Folder;

class MapperAggregate
{
	public const MAPPERS_FOLDER =  __DIR__ . '/Mappers';

	public const MAPPER_KEY = 'Mapper';

	protected array $mappers = [];

	public function getMappers(): array
	{
		$scannedMappers = Folder::getFilesInFolder(self::MAPPERS_FOLDER);

		foreach ($scannedMappers as $mapperFile) {
			$mapperFileInfo = pathinfo($mapperFile, \PATHINFO_FILENAME);
			$mapperKey = str_replace(self::MAPPER_KEY, '', $mapperFileInfo);

			$this->mappers[$mapperKey] = include(self::MAPPERS_FOLDER . '/' . $mapperFile);
		}

		return $this->mappers;
	}
}
