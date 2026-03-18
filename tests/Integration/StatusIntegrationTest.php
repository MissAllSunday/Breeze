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
		require_once __DIR__ . '/../setup-test-database.php';

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

		global $testDbConfig;

		// Clean up test data before each test
		truncateTestTables(self::$pdo, $testDbConfig['prefix']);
	}

	protected function tearDown(): void
	{
		global $testDbConfig;

		// Clean up test data after each test
		truncateTestTables(self::$pdo, $testDbConfig['prefix']);
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

	/**
	 * Test cursor-based pagination with encodeCursor, decodeCursor, and getNextCursor
	 *
	 * @throws InvalidStatusException
	 */
	public function testCursorPagination(): void
	{
		$wallId = 100;

		// Insert 5 statuses with slight time delays to ensure different timestamps
		$insertedIds = [];
		for ($i = 0; $i < 5; $i++) {
			$statusData = StatusFixtures::forInsertion();
			$statusData[StatusEntity::USER_ID] = 100;
			$statusData[StatusEntity::WALL_ID] = $wallId;
			$statusData[StatusEntity::BODY] = "Status $i";
			$statusEntity = StatusEntity::from($statusData);
			$result = self::$statusRepository->insert($statusEntity);
			$insertedIds[] = $result[0]->getId();
			usleep(10000); // 10ms delay to ensure different timestamps
		}

		// Get first page (3 items)
		$firstPage = self::$statusRepository->getByProfile([$wallId], 3);
		$this->assertCount(3, $firstPage);

		// Get next cursor from first page
		$cursor = self::$statusRepository->getNextCursor($firstPage);
		$this->assertNotNull($cursor);
		$this->assertIsString($cursor);

		// Decode cursor to verify it contains valid data
		$decodedCursor = self::$statusRepository->decodeCursor($cursor);
		$this->assertIsArray($decodedCursor);
		$this->assertArrayHasKey('id', $decodedCursor);
		$this->assertArrayHasKey('created_at', $decodedCursor);

		// Get second page using cursor
		$secondPage = self::$statusRepository->getByProfile([$wallId], 3, $cursor);
		$this->assertCount(2, $secondPage); // Should have remaining 2 items

		// Verify no overlap between pages
		$firstPageIds = array_map(fn ($s) => $s->getId(), $firstPage);
		$secondPageIds = array_map(fn ($s) => $s->getId(), $secondPage);
		$this->assertEmpty(array_intersect($firstPageIds, $secondPageIds));

		// Test encoding a cursor manually
		$lastStatus = end($firstPage);
		$manualCursor = self::$statusRepository->encodeCursor(
			$lastStatus->getId(),
			$lastStatus->getCreatedAt()->getTimestamp()
		);
		$this->assertIsString($manualCursor);

		// Decode it back
		$decoded = self::$statusRepository->decodeCursor($manualCursor);
		$this->assertEquals($lastStatus->getId(), $decoded['id']);
	}

	/**
	 * Test getBasicInfoById() retrieves status without full details
	 * Note: This method only retrieves id, wall_id, and user_id (not body or other fields)
	 *
	 * @throws InvalidStatusException
	 * @throws DataNotFoundException
	 */
	public function testGetBasicInfoById(): void
	{
		// Insert a status
		$statusData = StatusFixtures::forInsertion();
		$statusData[StatusEntity::USER_ID] = 100;
		$statusData[StatusEntity::WALL_ID] = 100;
		$statusData[StatusEntity::BODY] = 'Basic info test';
		$statusEntity = StatusEntity::from($statusData);
		$result = self::$statusRepository->insert($statusEntity);
		$statusId = $result[0]->getId();

		// Get basic info (only id, wall_id, user_id)
		$basicInfo = self::$statusRepository->getBasicInfoById($statusId);

		$this->assertInstanceOf(StatusEntity::class, $basicInfo);
		$this->assertEquals($statusId, $basicInfo->getId());
		$this->assertEquals(100, $basicInfo->getUserId());
		$this->assertEquals(100, $basicInfo->getWallId());
		// Note: body is NOT retrieved by getBasicInfoById()
	}

	/**
	 * Test recountLikes() updates like counts correctly
	 *
	 * @throws InvalidStatusException
	 */
	public function testRecountLikes(): void
	{
		// Insert a status
		$statusData = StatusFixtures::forInsertion();
		$statusData[StatusEntity::USER_ID] = 100;
		$statusData[StatusEntity::WALL_ID] = 100;
		$statusData[StatusEntity::BODY] = 'Status for like counting';
		$statusEntity = StatusEntity::from($statusData);
		$result = self::$statusRepository->insert($statusEntity);
		$statusId = $result[0]->getId();

		// Manually set incorrect like count in database
		global $testDbConfig;
		$prefix = $testDbConfig['prefix'];
		self::$pdo->exec("UPDATE `{$prefix}breeze_status` SET likes = 999 WHERE id = {$statusId}");

		// Verify incorrect count
		$stmt = self::$pdo->prepare("SELECT likes FROM `{$prefix}breeze_status` WHERE id = ?");
		$stmt->execute([$statusId]);
		$likes = $stmt->fetchColumn();
		$this->assertEquals(999, $likes);

		// Recount likes
		self::$statusRepository->recountLikes();

		// Verify count is corrected to 0 (no actual likes in user_likes table)
		$stmt->execute([$statusId]);
		$likes = $stmt->fetchColumn();
		$this->assertEquals(0, $likes);
	}
}
