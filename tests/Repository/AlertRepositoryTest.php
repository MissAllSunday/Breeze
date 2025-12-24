<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\AlertEntity;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AlertRepositoryTest extends TestCase
{
	private MockObject|ClientInterface $dbClient;

	private AlertRepository $alertRepository;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->dbClient = $this->createMock(ClientInterface::class);
		$this->alertRepository = new AlertRepository($this->dbClient);
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('user_alerts', $this->alertRepository->getTableName());
	}

	public function testInsert(): void
	{
		$alertEntity = AlertEntity::from([
			'id_member' => 1,
			'content_id' => 5,
			'content_type' => 'status',
			'content_action' => 'like',
			'is_read' => false,
		]);

		$this->dbClient->expects($this->once())
			->method('insert')
			->with('user_alerts', $this->anything());

		$this->dbClient->expects($this->once())
			->method('getInsertedId')
			->willReturn(10);

		$result = $this->alertRepository->insert($alertEntity);

		$this->assertEquals(10, $result);
	}

	#[DataProvider('getByIdProvider')]
	public function testGetById(int $alertId, array $dbResult, array $expected): void
	{
		$this->dbClient->expects($this->once())
			->method('query')
			->willReturn($dbResult);
		$this->dbClient->expects($this->exactly(1))
			->method('fetchAssoc')
			->willReturnOnConsecutiveCalls(
				reset($dbResult),
				null
			);

		$result = $this->alertRepository->getById($alertId);

		$this->assertInstanceOf(AlertEntity::class, $result);
		$this->assertEquals('br_sta', $result->getContentType());
	}

	public static function getByIdProvider(): array
	{
		return [
			'user with alerts' => [
				1,
				[['id_member' => 1,
					'id_member_started' => 2,
					'content_type' => 'br_sta',
					'content_Action' => 'like',
				]],
				[1],
			],
		];
	}
}
