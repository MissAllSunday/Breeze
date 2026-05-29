<?php

declare(strict_types=1);

namespace Breeze\Validate\Types;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\InvalidDataException;

class Data
{
	/**
	 * @throws DataNotFoundException
	 */
	public function dataExists(int $id, BaseRepositoryInterface $repository): void
	{
		if (!$repository->doesContentExists($id)) {
			throw new DataNotFoundException('error_no_data');
		}
	}

	/**
	 * @throws InvalidDataException
	 */
	public function compare(array $defaultParams, array $data): void
	{
		if (array_diff_key($defaultParams, $data) !== []) {
			throw new InvalidDataException('incomplete_data');
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
	public function isString(array $shouldBeString, array &$data): void
	{
		foreach ($shouldBeString as $stringName) {
			// Does the string has only numbers?
			if (is_numeric($data[$stringName])) {
				$data[$stringName] = (string) $data[$stringName];
			}

			if (!is_string($data[$stringName])) {
				throw new DataNotFoundException('malformed_data');
			}
		}
	}
}
