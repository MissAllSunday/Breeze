<?php

declare(strict_types=1);

namespace Breeze\Config;

class MapperAggregate
{
	const MAPPERS_FOLDER =  __DIR__ . '/Mappers';
	const MAPPER_KEY = 'Mapper';

	protected $mappers = [];

	public function getMappers(): array
	{
		$scannedMappers = $this->scanMappersFolder();

		foreach ($scannedMappers as $mapperFile)
		{
			$mapperFileInfo = pathinfo($mapperFile, PATHINFO_FILENAME);
			$this->mappers[str_replace(self::MAPPER_KEY, '', $mapperFileInfo)] = include(self::MAPPERS_FOLDER . '/' . $mapperFile);
		}

		return $this->mappers;
	}

	protected function scanMappersFolder(): array
	{
		return array_diff(scandir(self::MAPPERS_FOLDER), ['..', '.']);
	}
}
