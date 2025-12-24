<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

use Breeze\Database\ClientInterface;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class SettingsRepositoryTest extends TestCase
{
	private MockObject|ClientInterface $dbClient;

	private SettingsRepository $settingsRepository;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->dbClient = $this->createMock(ClientInterface::class);
		$this->settingsRepository = new SettingsRepository($this->dbClient);
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('members', $this->settingsRepository->getTableName());
	}

	/**
	 * @throws DataNotFoundException
	 */
	#[DataProvider('getByIdProvider')]
	public function testGetById(int $userId, array|bool $dbResult, bool $shouldReturnEntity): void
	{
		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($dbResult);

		if (!$shouldReturnEntity) {
			$this->expectException(DataNotFoundException::class);
		} else {
			$this->dbClient->expects($this->exactly(2))
				->method('fetchAssoc')
				->willReturnOnConsecutiveCalls(
					reset($dbResult),
					null
				);
		}

		$result = $this->settingsRepository->getById($userId);

		if ($shouldReturnEntity) {
			$this->assertInstanceOf(UserSettingsEntity::class, $result);
			$this->assertEquals(1, $result->getGeneralWall());
		}
	}

	public static function getByIdProvider(): array
	{
		return [
			'user settings found' => [
				1,
				[
					['variable' => 'generalWall', 'value' => '1'],
				],
				true,
			],
			'user settings not found' => [
				999,
				false,
				false,
			],
		];
	}
}
