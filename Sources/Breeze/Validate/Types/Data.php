<?php

declare(strict_types=1);

namespace Breeze\Validate\Types;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Validate\DataNotFoundException;

class Data
{
	/**
	 * @throws DataNotFoundException
	 */
	public function dataExists(int $id, BaseRepositoryInterface $repository): void
	{
		$repository->getById($id);
	}

	/**
	 * @throws InvalidDataException
	 */
	public function compare(array $defaultParams, array $data): void
	{
		if (!empty(array_diff_key($defaultParams, $data))) {
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
	public function isString(array $shouldBeString, array $data): void
	{
		foreach ($shouldBeString as $stringName) {
			$stringValue = $data[$stringName];

			// Does the string has only numbers?
			if (ctype_digit((string) $stringValue)) {
				$stringValue = (string) $stringValue;
			}

			if (!is_string($stringValue)) {
				throw new DataNotFoundException('malformed_data');
			}
		}
	}
}
