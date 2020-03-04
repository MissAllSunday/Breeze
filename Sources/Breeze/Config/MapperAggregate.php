<?php

declare(strict_types=1);

namespace Breeze\Config;

class MapperAggregate
{
	const MAPPERS_FOLDER = 'Mappers';

	protected $mappers = [];

	public function getMappers(): void
	{
		$scannedMappers = $this->scanMappersFolder();

		foreach ($scannedMappers as $mapperFile)
			$this->mappers[] = include $mapperFile;

	}

	protected function scanMappersFolder(): array
	{
		 $mappersFolder = __DIR__ . '/' . self::MAPPERS_FOLDER;

		return array_diff(scandir($mappersFolder), ['..', '.']);
	}
}
