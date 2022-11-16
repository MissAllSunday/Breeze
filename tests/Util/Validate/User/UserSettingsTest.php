<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\User;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\User\UserSettings;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserSettingsTest extends TestCase
{
	use ProphecyTrait;

	/**
	 * @dataProvider isValidProvider
	 */
	public function testIsValid(array $data, bool $isExpectedException): void
	{
		$validateData = $this->prophesize(Data::class);
		$validateUser = $this->prophesize(User::class);
		$validateAllow = $this->prophesize(Allow::class);
		$repository = $this->prophesize(BaseRepositoryInterface::class);

		$userSettings = new UserSettings(
			$validateData->reveal(),
			$validateUser->reveal(),
			$validateAllow->reveal(),
			$repository->reveal()
		);

		$userSettings->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		}

		$userSettings->isValid();

		$this->assertEquals($userSettings->data, $data);
	}

	public function isValidProvider(): array
	{
		return [
			'default values' => [
				'data' => [
					'wall' => 0,
					'generalWall' => 0,
					'paginationNumber' => 5,
					'kickIgnored' => 0,
					'aboutMe' => '',
				],
				'isExpectedException' => false,
			],
		];
	}
}
