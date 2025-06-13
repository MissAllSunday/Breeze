<?php

declare(strict_types=1);

namespace Breeze\Validate\Types;

use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidDataException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;

class Data
{
	public function dataExists(int $id, StatusRepositoryInterface | CommentRepositoryInterface $repository): void
	{
		$repository->getById($id);
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
