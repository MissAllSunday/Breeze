<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\User;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\User\UserSettings;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserSettingsTest extends TestCase
{
	#[DataProvider('isValidProvider')]
	public function testIsValid(array $data, bool $isExpectedException): void
	{
		$validateData = $this->createMock(Data::class);
		$validateUser = $this->createMock(User::class);
		$validateAllow = $this->createMock(Allow::class);
		$repository = $this->createMock(BaseRepositoryInterface::class);

		$userSettings = new UserSettings(
			$validateData,
			$validateUser,
			$validateAllow,
			$repository
		);

		$userSettings->setData($data);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
		}

		$userSettings->isValid();

		$this->assertEquals($userSettings->data, $data);
	}

	public static function isValidProvider(): array
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
