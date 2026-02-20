<?php

declare(strict_types=1);

namespace Breeze\Integration;

use Breeze\Database\DatabaseClient;
use Breeze\Entity\StatusEntity;
use Breeze\Fixtures\StatusFixtures;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\LikeRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Status operations using real database
 *
 * These tests require a configured test database.
 * Run tests/setup-test-database.php before running these tests.
 */
class StatusIntegrationTest extends TestCase
{
	private static ?\PDO $pdo = null;

	private static ?DatabaseClient $dbClient = null;

	private static ?StatusRepository $statusRepository = null;

	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../database-config.php';

		if (!isDatabaseAvailable()) {
			self::markTestSkipped('Database is not available. Run tests/setup-test-database.php first.');
		}

		self::$pdo = getTestDatabaseConnection();
		initializeSmfDatabaseFunctions();

		// Initialize database client
		self::$dbClient = new DatabaseClient();

		// Initialize repositories
		$likeRepository = new LikeRepository(self::$dbClient);
		$commentRepository = new CommentRepository(self::$dbClient, $likeRepository);
		self::$statusRepository = new StatusRepository(self::$dbClient, $commentRepository, $likeRepository);
	}

	protected function setUp(): void
	{
		if (self::$pdo === null) {
			$this->markTestSkipped('Database connection not available');
		}

		// Clean up test data before each test
		$this->cleanupTestData();
	}

	protected function tearDown(): void
	{
		// Clean up test data after each test
		$this->cleanupTestData();
	}

	private function cleanupTestData(): void
	{
		global $testDbConfig;
		$prefix = $testDbConfig['prefix'];

		// Delete test statuses (this will cascade to comments via repository)
		self::$pdo->exec("DELETE FROM `{$prefix}breeze_status` WHERE user_id IN (100, 101, 102)");
		self::$pdo->exec("DELETE FROM `{$prefix}breeze_comments` WHERE user_id IN (100, 101, 102)");
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function testInsertStatus(): void
	{
		$statusData = StatusFixtures::forInsertion();
		$statusData[StatusEntity::USER_ID] = 100;
		$statusData[StatusEntity::WALL_ID] = 100;
		$statusData[StatusEntity::BODY] = 'Integration test status';

		$statusEntity = StatusEntity::from($statusData);
		$result = self::$statusRepository->insert($statusEntity);

		$this->assertIsArray($result);
		$this->assertCount(1, $result);
		$this->assertInstanceOf(StatusEntity::class, $result[0]);
		$this->assertGreaterThan(0, $result[0]->getId());
		$this->assertEquals('Integration test status', $result[0]->getBody());
		$this->assertEquals(100, $result[0]->getUserId());
		$this->assertEquals(100, $result[0]->getWallId());
	}

	/**
	 * @throws InvalidStatusException
	 * @throws DataNotFoundException
	 */
	public function testGetStatusById(): void
	{
		// Insert a status first
		$statusData = StatusFixtures::forInsertion();
		$statusData[StatusEntity::USER_ID] = 100;
		$statusData[StatusEntity::WALL_ID] = 100;
		$statusData[StatusEntity::BODY] = 'Test status for retrieval';

		$statusEntity = StatusEntity::from($statusData);
		$insertedStatuses = self::$statusRepository->insert($statusEntity);
		$insertedId = $insertedStatuses[0]->getId();

		// Retrieve the status
		$retrievedStatus = self::$statusRepository->getById($insertedId);

		$this->assertInstanceOf(StatusEntity::class, $retrievedStatus);
		$this->assertEquals($insertedId, $retrievedStatus->getId());
		$this->assertEquals('Test status for retrieval', $retrievedStatus->getBody());
		$this->assertEquals(100, $retrievedStatus->getUserId());
		$this->assertEquals(100, $retrievedStatus->getWallId());
	}

	/**
	 * @throws InvalidStatusException
	 * @throws DataNotFoundException
	 */
	public function testDeleteStatusById(): void
	{
		// Insert a status first
		$statusData = StatusFixtures::forInsertion();
		$statusData[StatusEntity::USER_ID] = 100;
		$statusData[StatusEntity::WALL_ID] = 100;
		$statusData[StatusEntity::BODY] = 'Status to be deleted';

		$statusEntity = StatusEntity::from($statusData);
		$insertedStatuses = self::$statusRepository->insert($statusEntity);
		$insertedId = $insertedStatuses[0]->getId();

		// Verify it exists
		$this->assertInstanceOf(StatusEntity::class, self::$statusRepository->getById($insertedId));

		// Delete the status
		$result = self::$statusRepository->deleteById($insertedId);
		$this->assertTrue($result);

		// Verify it's deleted
		$this->expectException(DataNotFoundException::class);
		self::$statusRepository->getById($insertedId);
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function testGetStatusesByProfile(): void
	{
		// Insert multiple statuses for the same wall
		$wallId = 100;
		$statusBodies = ['First status', 'Second status', 'Third status'];

		foreach ($statusBodies as $body) {
			$statusData = StatusFixtures::forInsertion();
			$statusData[StatusEntity::USER_ID] = 100;
			$statusData[StatusEntity::WALL_ID] = $wallId;
			$statusData[StatusEntity::BODY] = $body;

			$statusEntity = StatusEntity::from($statusData);
			self::$statusRepository->insert($statusEntity);
		}

		// Retrieve statuses by profile
		$statuses = self::$statusRepository->getByProfile([$wallId], 10);

		$this->assertIsArray($statuses);
		$this->assertCount(3, $statuses);

		foreach ($statuses as $status) {
			$this->assertInstanceOf(StatusEntity::class, $status);
			$this->assertEquals($wallId, $status->getWallId());
		}
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function testGetStatusCount(): void
	{
		// Insert multiple statuses for different walls
		$wallId1 = 100;
		$wallId2 = 101;

		for ($i = 0; $i < 3; $i++) {
			$statusData = StatusFixtures::forInsertion();
			$statusData[StatusEntity::USER_ID] = 100;
			$statusData[StatusEntity::WALL_ID] = $wallId1;
			$statusData[StatusEntity::BODY] = "Status $i for wall 100";

			$statusEntity = StatusEntity::from($statusData);
			self::$statusRepository->insert($statusEntity);
		}

		for ($i = 0; $i < 2; $i++) {
			$statusData = StatusFixtures::forInsertion();
			$statusData[StatusEntity::USER_ID] = 101;
			$statusData[StatusEntity::WALL_ID] = $wallId2;
			$statusData[StatusEntity::BODY] = "Status $i for wall 101";

			$statusEntity = StatusEntity::from($statusData);
			self::$statusRepository->insert($statusEntity);
		}

		// Get count for wall 100
		$count1 = self::$statusRepository->getCount([
			'columnName' => StatusEntity::WALL_ID,
			'ids' => [$wallId1],
		]);
		$this->assertEquals(3, $count1);

		// Get count for wall 101
		$count2 = self::$statusRepository->getCount([
			'columnName' => StatusEntity::WALL_ID,
			'ids' => [$wallId2],
		]);
		$this->assertEquals(2, $count2);
	}

	public function testGetNonExistentStatus(): void
	{
		$this->expectException(DataNotFoundException::class);
		self::$statusRepository->getById(999999);
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function testInsertStatusWithEmptyBody(): void
	{
		$statusData = StatusFixtures::forInsertion();
		$statusData[StatusEntity::USER_ID] = 100;
		$statusData[StatusEntity::WALL_ID] = 100;
		$statusData[StatusEntity::BODY] = '';

		$statusEntity = StatusEntity::from($statusData);
		$result = self::$statusRepository->insert($statusEntity);

		// Empty body should still be allowed
		$this->assertIsArray($result);
		$this->assertCount(1, $result);
		$this->assertEquals('', $result[0]->getBody());
	}
}
