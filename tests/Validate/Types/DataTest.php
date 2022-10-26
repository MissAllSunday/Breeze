<?php

namespace Breeze\Validate\Types;

use Breeze\Repository\BaseRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DataTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider compareProvider
	 */
	public function testCompare(array $defaultParams, array $data): void
	{

	}

	public function compareProvider(): array
	{
		return [

		];
	}
}