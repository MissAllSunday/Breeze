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

	public function testGetByIdsReturnsMultipleEntities(): void
	{
		$mockResult = new \stdClass();

		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($mockResult);

		$this->dbClient->expects($this->exactly(4))
			->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				[
					'id_member' => 1,
					'member_name' => 'User1',
					'real_name' => 'User One',
					'pm_ignore_list' => '',
					'buddy_list' => '',
					'variable' => 'generalWall',
					'value' => '1',
				],
				[
					'id_member' => 2,
					'member_name' => 'User2',
					'real_name' => 'User Two',
					'pm_ignore_list' => '1,5',
					'buddy_list' => '3,4',
					'variable' => 'wall',
					'value' => '1',
				],
				[
					'id_member' => 2,
					'member_name' => 'User2',
					'real_name' => 'User Two',
					'pm_ignore_list' => '1,5',
					'buddy_list' => '3,4',
					'variable' => 'blockBuddyRequests',
					'value' => '1',
				],
				null,
			);

		$this->dbClient->expects($this->once())
			->method('freeResult')
			->with($mockResult);

		$result = $this->settingsRepository->getByIds([1, 2]);

		$this->assertCount(2, $result);
		$this->assertArrayHasKey(1, $result);
		$this->assertArrayHasKey(2, $result);

		$this->assertEquals(1, $result[1]->getGeneralWall());
		$this->assertEquals([], $result[1]->getBlockList());
		$this->assertEquals([], $result[1]->getBuddies());

		$this->assertEquals(1, $result[2]->getWall());
		$this->assertEquals(1, $result[2]->getBlockBuddyRequests());
		$this->assertEquals([1, 5], $result[2]->getBlockList());
		$this->assertEquals([3, 4], $result[2]->getBuddies());
	}

	public function testGetByIdsReturnsEmptyForUnknownUsers(): void
	{
		$mockResult = new \stdClass();

		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($mockResult);

		$this->dbClient->expects($this->once())
			->method('fetchAssoc')
			->willReturn(null);

		$this->dbClient->expects($this->once())
			->method('freeResult')
			->with($mockResult);

		$result = $this->settingsRepository->getByIds([99, 100]);

		$this->assertEquals([], $result);
	}
}
