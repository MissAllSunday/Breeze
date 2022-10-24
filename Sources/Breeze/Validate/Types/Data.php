<?php

declare(strict_types=1);

namespace Breeze\Validate\Types;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Validate\DataNotFoundException;

class Data
{
	/**
	 * @throws InvalidDataException
	 */
	public function dataExists(int $id, BaseRepositoryInterface $repository): void
	{
		$repository->getById($id);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function compare(array $defaultParams, array $data): void
	{
		if (!empty(array_diff_key($defaultParams, $data))) {
			throw new DataNotFoundException('incomplete_data');
		}
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function isInt(array $shouldBeIntValues, array $data): void
	{
		foreach ($shouldBeIntValues as $integerValueName) {
			if (!is_int($data[$integerValueName])) {
				throw new DataNotFoundException('malformed_data');
			}
		}
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function isString(array $shouldBeString, array $data): void
	{
		foreach ($shouldBeString as $stringValueName) {
			if (!is_string($data[$stringValueName])) {
				throw new DataNotFoundException('malformed_data');
			}
		}
	}
}
