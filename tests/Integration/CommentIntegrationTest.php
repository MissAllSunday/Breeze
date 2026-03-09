<?php

declare(strict_types=1);

namespace Breeze\Integration;

use Breeze\Database\DatabaseClient;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Fixtures\CommentFixtures;
use Breeze\Fixtures\StatusFixtures;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\InvalidCommentException;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\LikeRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Comment operations using real database
 *
 * These tests require a configured test database.
 * Run tests/setup-test-database.php before running these tests.
 */
class CommentIntegrationTest extends TestCase
{
	private static ?\PDO $pdo = null;

	private static ?DatabaseClient $dbClient = null;

	private static ?CommentRepository $commentRepository = null;

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
		self::$commentRepository = new CommentRepository(self::$dbClient, $likeRepository);
		self::$statusRepository = new StatusRepository(self::$dbClient, self::$commentRepository, $likeRepository);
	}

	protected function setUp(): void
	{
		if (self::$pdo === null) {
			$this->markTestSkipped('Database connection not available');
		}

		global $testDbConfig;
		$prefix = $testDbConfig['prefix'];

		// Ensure a clean state before every test
		self::$pdo->exec("TRUNCATE TABLE `{$prefix}breeze_status`");
		self::$pdo->exec("TRUNCATE TABLE `{$prefix}breeze_comments`");
		self::$pdo->exec("TRUNCATE TABLE `{$prefix}user_likes`");
		self::$pdo->exec("TRUNCATE TABLE `{$prefix}members`");
		self::$pdo->exec("TRUNCATE TABLE `{$prefix}breeze_options`");
	}

	/**
	 * Create a test status for comment tests
	 *
	 * @throws InvalidStatusException
	 */
	private function createTestStatus(): int
	{
		$statusData = StatusFixtures::forInsertion();
		$statusData[StatusEntity::USER_ID] = 100;
		$statusData[StatusEntity::WALL_ID] = 100;
		$statusData[StatusEntity::BODY] = 'Test status for comments';

		$statusEntity = StatusEntity::from($statusData);
		$result = self::$statusRepository->insert($statusEntity);

		return $result[0]->getId();
	}

	/**
	 * @throws InvalidCommentException
	 * @throws InvalidStatusException
	 */
	public function testInsertComment(): void
	{
		$statusId = $this->createTestStatus();

		$commentData = CommentFixtures::forInsertion();
		$commentData[CommentEntity::STATUS_ID] = $statusId;
		$commentData[CommentEntity::USER_ID] = 100;
		$commentData[CommentEntity::BODY] = 'Integration test comment';

		$commentEntity = CommentEntity::from($commentData);
		$result = self::$commentRepository->insert($commentEntity);

		$this->assertIsArray($result);
		$this->assertCount(1, $result);
		$this->assertInstanceOf(CommentEntity::class, $result[0]);
		$this->assertGreaterThan(0, $result[0]->getId());
		$this->assertEquals($statusId, $result[0]->getStatusId());
		$this->assertEquals(100, $result[0]->getUserId());
	}

	/**
	 * @throws InvalidCommentException
	 * @throws InvalidStatusException
	 * @throws DataNotFoundException
	 */
	public function testGetCommentById(): void
	{
		$statusId = $this->createTestStatus();

		// Insert a comment first
		$commentData = CommentFixtures::forInsertion();
		$commentData[CommentEntity::STATUS_ID] = $statusId;
		$commentData[CommentEntity::USER_ID] = 100;
		$commentData[CommentEntity::BODY] = 'Test comment for retrieval';

		$commentEntity = CommentEntity::from($commentData);
		$insertedComments = self::$commentRepository->insert($commentEntity);
		$insertedId = $insertedComments[0]->getId();

		// Retrieve the comment
		$retrievedComment = self::$commentRepository->getById($insertedId);

		$this->assertInstanceOf(CommentEntity::class, $retrievedComment);
		$this->assertEquals($insertedId, $retrievedComment->getId());
		$this->assertEquals($statusId, $retrievedComment->getStatusId());
		$this->assertEquals(100, $retrievedComment->getUserId());
	}

	/**
	 * @throws InvalidCommentException
	 * @throws InvalidStatusException
	 */
	public function testDeleteCommentById(): void
	{
		$statusId = $this->createTestStatus();

		// Insert a comment first
		$commentData = CommentFixtures::forInsertion();
		$commentData[CommentEntity::STATUS_ID] = $statusId;
		$commentData[CommentEntity::USER_ID] = 100;
		$commentData[CommentEntity::BODY] = 'Comment to be deleted';

		$commentEntity = CommentEntity::from($commentData);
		$insertedComments = self::$commentRepository->insert($commentEntity);
		$insertedId = $insertedComments[0]->getId();

		// Verify it exists
		$this->assertInstanceOf(CommentEntity::class, self::$commentRepository->getById($insertedId));

		// Delete the comment
		$result = self::$commentRepository->deleteById($insertedId);
		$this->assertTrue($result);

		// Verify it's deleted - should throw DataNotFoundException
		$this->expectException(DataNotFoundException::class);
		self::$commentRepository->getById($insertedId);
	}

	/**
	 * @throws InvalidCommentException
	 * @throws InvalidStatusException
	 */
	public function testGetCommentsByStatus(): void
	{
		$statusId = $this->createTestStatus();

		// Insert multiple comments for the same status
		$commentBodies = ['First comment', 'Second comment', 'Third comment'];

		foreach ($commentBodies as $body) {
			$commentData = CommentFixtures::forInsertion();
			$commentData[CommentEntity::STATUS_ID] = $statusId;
			$commentData[CommentEntity::USER_ID] = 100;
			$commentData[CommentEntity::BODY] = $body;

			$commentEntity = CommentEntity::from($commentData);
			self::$commentRepository->insert($commentEntity);
		}

		// Retrieve comments by status
		$comments = self::$commentRepository->getByStatus([$statusId]);

		$this->assertIsArray($comments);
		$this->assertCount(3, $comments);

		foreach ($comments as $comment) {
			$this->assertInstanceOf(CommentEntity::class, $comment);
			$this->assertEquals($statusId, $comment->getStatusId());
		}
	}

	/**
	 * @throws InvalidCommentException
	 * @throws InvalidStatusException
	 * @throws DataNotFoundException
	 */
	public function testDeleteCommentsByStatusId(): void
	{
		$statusId = $this->createTestStatus();

		// Insert multiple comments for the status
		$commentIds = [];
		for ($i = 0; $i < 3; $i++) {
			$commentData = CommentFixtures::forInsertion();
			$commentData[CommentEntity::STATUS_ID] = $statusId;
			$commentData[CommentEntity::USER_ID] = 100;
			$commentData[CommentEntity::BODY] = "Comment $i";

			$commentEntity = CommentEntity::from($commentData);
			$result = self::$commentRepository->insert($commentEntity);
			$commentIds[] = $result[0]->getId();
		}

		// Verify comments exist
		$this->assertCount(3, self::$commentRepository->getByStatus([$statusId]));

		// Delete all comments for the status
		$result = self::$commentRepository->deleteByStatusId($statusId);
		$this->assertTrue($result);

		// Verify all comments are deleted
		$this->assertEmpty(self::$commentRepository->getByStatus([$statusId]));
	}

	/**
	 * @throws InvalidCommentException
	 * @throws InvalidStatusException
	 * @throws DataNotFoundException
	 */
	public function testDeleteStatusCascadesToComments(): void
	{
		$statusId = $this->createTestStatus();

		// Insert comments for the status
		for ($i = 0; $i < 2; $i++) {
			$commentData = CommentFixtures::forInsertion();
			$commentData[CommentEntity::STATUS_ID] = $statusId;
			$commentData[CommentEntity::USER_ID] = 100;
			$commentData[CommentEntity::BODY] = "Comment $i";

			$commentEntity = CommentEntity::from($commentData);
			self::$commentRepository->insert($commentEntity);
		}

		// Verify comments exist
		$this->assertCount(2, self::$commentRepository->getByStatus([$statusId]));

		// Delete the status (should cascade to comments)
		self::$statusRepository->deleteById($statusId);

		// Verify comments are deleted
		$this->assertEmpty(self::$commentRepository->getByStatus([$statusId]));
	}

	/**
	 * Test that attempting to get a non-existent comment throws DataNotFoundException
	 */
	public function testGetNonExistentComment(): void
	{
		$this->expectException(DataNotFoundException::class);
		self::$commentRepository->getById(999999);
	}

	/**
	 * @throws InvalidCommentException
	 * @throws InvalidStatusException
	 */
	public function testInsertCommentWithEmptyBody(): void
	{
		$statusId = $this->createTestStatus();

		$commentData = CommentFixtures::forInsertion();
		$commentData[CommentEntity::STATUS_ID] = $statusId;
		$commentData[CommentEntity::USER_ID] = 100;
		$commentData[CommentEntity::BODY] = '';

		$commentEntity = CommentEntity::from($commentData);
		$result = self::$commentRepository->insert($commentEntity);

		// Empty body should still be allowed
		$this->assertIsArray($result);
		$this->assertCount(1, $result);
	}

	/**
	 * Test getByProfile() retrieves comments for a user's wall
	 *
	 * @throws InvalidCommentException
	 * @throws InvalidStatusException
	 */
	public function testGetCommentsByProfile(): void
	{
		// Create statuses on different walls
		$wallId1 = 100;
		$wallId2 = 101;

		// Create status on wall 100
		$statusData1 = StatusFixtures::forInsertion();
		$statusData1[StatusEntity::USER_ID] = 100;
		$statusData1[StatusEntity::WALL_ID] = $wallId1;
		$statusData1[StatusEntity::BODY] = 'Status on wall 100';
		$statusEntity1 = StatusEntity::from($statusData1);
		$status1 = self::$statusRepository->insert($statusEntity1);
		$statusId1 = $status1[0]->getId();

		// Create status on wall 101
		$statusData2 = StatusFixtures::forInsertion();
		$statusData2[StatusEntity::USER_ID] = 101;
		$statusData2[StatusEntity::WALL_ID] = $wallId2;
		$statusData2[StatusEntity::BODY] = 'Status on wall 101';
		$statusEntity2 = StatusEntity::from($statusData2);
		$status2 = self::$statusRepository->insert($statusEntity2);
		$statusId2 = $status2[0]->getId();

		// Add comments to both statuses
		for ($i = 0; $i < 2; $i++) {
			$commentData = CommentFixtures::forInsertion();
			$commentData[CommentEntity::STATUS_ID] = $statusId1;
			$commentData[CommentEntity::USER_ID] = 100;
			$commentData[CommentEntity::BODY] = "Comment $i on wall 100";
			$commentEntity = CommentEntity::from($commentData);
			self::$commentRepository->insert($commentEntity);
		}

		for ($i = 0; $i < 3; $i++) {
			$commentData = CommentFixtures::forInsertion();
			$commentData[CommentEntity::STATUS_ID] = $statusId2;
			$commentData[CommentEntity::USER_ID] = 101;
			$commentData[CommentEntity::BODY] = "Comment $i on wall 101";
			$commentEntity = CommentEntity::from($commentData);
			self::$commentRepository->insert($commentEntity);
		}

		// Get comments for wall 100
		$commentsWall1 = self::$commentRepository->getByProfile([$wallId1]);
		$this->assertIsArray($commentsWall1);
		$this->assertCount(2, $commentsWall1);

		// Get comments for wall 101
		$commentsWall2 = self::$commentRepository->getByProfile([$wallId2]);
		$this->assertIsArray($commentsWall2);
		$this->assertCount(3, $commentsWall2);
	}

	/**
	 * Test countOrphans() and deleteOrphans() for orphaned comments
	 *
	 * @throws InvalidCommentException
	 * @throws InvalidStatusException
	 */
	public function testOrphanedComments(): void
	{
		$statusId = $this->createTestStatus();

		// Insert comments
		for ($i = 0; $i < 3; $i++) {
			$commentData = CommentFixtures::forInsertion();
			$commentData[CommentEntity::STATUS_ID] = $statusId;
			$commentData[CommentEntity::USER_ID] = 100;
			$commentData[CommentEntity::BODY] = "Comment $i";
			$commentEntity = CommentEntity::from($commentData);
			self::$commentRepository->insert($commentEntity);
		}

		// Initially no orphans
		$orphanCount = self::$commentRepository->countOrphans();
		$this->assertEquals(0, $orphanCount);

		// Delete the status directly from database (bypassing repository to create orphans)
		global $testDbConfig;
		$prefix = $testDbConfig['prefix'];
		self::$pdo->exec("DELETE FROM `{$prefix}breeze_status` WHERE id = {$statusId}");

		// Now we should have orphaned comments
		$orphanCount = self::$commentRepository->countOrphans();
		$this->assertEquals(3, $orphanCount);

		// Delete orphans
		self::$commentRepository->deleteOrphans();

		// Verify orphans are deleted
		$orphanCount = self::$commentRepository->countOrphans();
		$this->assertEquals(0, $orphanCount);
	}

	/**
	 * Test recountLikes() updates like counts correctly
	 *
	 * @throws InvalidCommentException
	 * @throws InvalidStatusException
	 */
	public function testRecountLikes(): void
	{
		$statusId = $this->createTestStatus();

		// Insert a comment
		$commentData = CommentFixtures::forInsertion();
		$commentData[CommentEntity::STATUS_ID] = $statusId;
		$commentData[CommentEntity::USER_ID] = 100;
		$commentData[CommentEntity::BODY] = 'Comment for like testing';
		$commentEntity = CommentEntity::from($commentData);
		$result = self::$commentRepository->insert($commentEntity);
		$commentId = $result[0]->getId();

		// Manually set incorrect like count in database
		global $testDbConfig;
		$prefix = $testDbConfig['prefix'];
		self::$pdo->exec("UPDATE `{$prefix}breeze_comments` SET likes = 999 WHERE id = {$commentId}");

		// Verify incorrect count
		$stmt = self::$pdo->prepare("SELECT likes FROM `{$prefix}breeze_comments` WHERE id = ?");
		$stmt->execute([$commentId]);
		$likes = $stmt->fetchColumn();
		$this->assertEquals(999, $likes);

		// Recount likes
		self::$commentRepository->recountLikes();

		// Verify count is corrected to 0 (no actual likes in user_likes table)
		$stmt->execute([$commentId]);
		$likes = $stmt->fetchColumn();
		$this->assertEquals(0, $likes);
	}
}
