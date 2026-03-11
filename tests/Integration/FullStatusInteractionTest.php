<?php

declare(strict_types=1);

namespace Breeze\Integration;

use Breeze\Database\DatabaseClient;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Event\Comment\CommentEventListener;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\HandlerServiceProvider;
use Breeze\Event\Like\LikeEventListener;
use Breeze\Event\Status\StatusEventListener;
use Breeze\Fixtures\CommentFixtures;
use Breeze\Fixtures\StatusFixtures;
use Breeze\LikesEnum;
use Breeze\Repository\AlertRepository;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\LikeRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Service\AlertService;
use Breeze\Service\LikeService;
use League\Event\EventDispatcher;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * Integration tests for a full user interaction flow.
 *
 * These tests require a configured test database.
 * Run tests/setup-test-database.php before running these tests.
 */
class FullStatusInteractionTest extends TestCase
{
	private static ?\PDO $pdo = null;

	private static ?DatabaseClient $dbClient = null;

	private ?StatusRepository $statusRepository = null;

	private ?CommentRepository $commentRepository = null;

	private ?LikeRepository $likeRepository = null;

	private ?LikeService $likeService = null;

	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../database-config.php';

		if (!isDatabaseAvailable()) {
			self::markTestSkipped('Database is not available. Run tests/setup-test-database.php first.');
		}

		self::$pdo = getTestDatabaseConnection();
		initializeSmfDatabaseFunctions();

		// Ensure native SMF tables used in tests exist in the test database
		self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS `user_likes` (
                `id_member` INT(10) UNSIGNED NOT NULL,
                `id_content` INT(10) UNSIGNED NOT NULL,
                `content_type` VARCHAR(20) NOT NULL,
                `like_time` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`id_member`, `id_content`, `content_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
		self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS `user_alerts` (
                `id_alert` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_member` INT(10) UNSIGNED NOT NULL,
                `alert_time` INT(10) UNSIGNED NOT NULL,
                `alert_type` VARCHAR(20) NOT NULL,
                `content_id` INT(10) UNSIGNED NOT NULL,
                `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id_alert`),
                KEY `id_member` (`id_member`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
		self::$pdo->exec("
            CREATE TABLE IF NOT EXISTS `members` (
                `id_member` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `member_name` VARCHAR(80) NOT NULL DEFAULT '',
                PRIMARY KEY (`id_member`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

		self::$dbClient = new DatabaseClient();
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
		self::$pdo->exec("TRUNCATE TABLE `user_likes`");
		self::$pdo->exec("TRUNCATE TABLE `user_alerts`");
		self::$pdo->exec("TRUNCATE TABLE `members`");
		self::$pdo->exec("TRUNCATE TABLE `{$prefix}breeze_options`");

		$this->likeRepository = new LikeRepository(self::$dbClient);
		$this->commentRepository = new CommentRepository(self::$dbClient, $this->likeRepository);
		$this->statusRepository = new StatusRepository(self::$dbClient, $this->commentRepository, $this->likeRepository);

		$alertRepository = new AlertRepository(self::$dbClient);
		$handlerServiceProvider = new HandlerServiceProvider();
		$alertService = new AlertService($alertRepository, $handlerServiceProvider);

		$container = $this->createMock(ContainerInterface::class);
		$container->method('get')->willReturnMap([
			[StatusRepository::class, $this->statusRepository],
			[CommentRepository::class, $this->commentRepository],
		]);

		$statusEventListener = new StatusEventListener($alertService);
		$commentEventListener = new CommentEventListener($alertService);
		$likeEventListener = new LikeEventListener($alertService, $container);
		$eventDispatcher = new EventDispatcher();
		$eventServiceProvider = new EventServiceProvider($eventDispatcher, $statusEventListener, $commentEventListener, $likeEventListener);

		$this->likeService = new LikeService($this->likeRepository, $eventServiceProvider);
	}

	#[AllowMockObjectsWithoutExpectations]
	public function testFullStatusInteractionFlow(): void
	{
		// 1. User A (ID 100) posts a new status.
		$statusData = StatusFixtures::forInsertion();
		$statusData[StatusEntity::USER_ID] = 100;
		$statusData[StatusEntity::WALL_ID] = 100;
		$statusData[StatusEntity::BODY] = 'User A status';
		$statusEntity = StatusEntity::from($statusData);
		$insertedStatus = $this->statusRepository->insert($statusEntity)[0];
		$statusId = $insertedStatus->getId();

		$this->assertNotNull($statusId);
		$this->assertEquals('User A status', $insertedStatus->getBody());

		// 2. User B (ID 101) comments on the status.
		$commentData = CommentFixtures::forInsertion();
		$commentData[CommentEntity::STATUS_ID] = $statusId;
		$commentData[CommentEntity::USER_ID] = 101;
		$commentData[CommentEntity::BODY] = 'User B comment';
		$commentEntity = CommentEntity::from($commentData);
		$insertedComment = $this->commentRepository->insert($commentEntity)[0];
		$commentId = $insertedComment->getId();

		$this->assertNotNull($commentId);
		$this->assertEquals('User B comment', $insertedComment->getBody());

		// 3. User A (ID 100) likes User B's comment.
		$this->likeService->likeContent(LikesEnum::Comments, $commentId, 100);

		// 4. Verify that the status, comment, and like are all correctly stored.
		$status = $this->statusRepository->getById($statusId);
		$comments = $this->commentRepository->getByStatus([$statusId]);
		$this->assertCount(1, $comments);

		$comment = $this->commentRepository->getById($commentId);

		$likeInfoEntities = $this->likeRepository->getByContent(LikesEnum::Comments, [$commentId]);
		$this->assertArrayHasKey($commentId, $likeInfoEntities);
		$likeInfo = $likeInfoEntities[$commentId];
		$this->assertInstanceOf(LikeInfoEntity::class, $likeInfo);

		$likeEntities = $likeInfo->getLikes();
		$this->assertCount(1, $likeEntities);

		$likeEntity = array_shift($likeEntities);
		$this->assertInstanceOf(LikeEntity::class, $likeEntity);
		$this->assertEquals(100, $likeEntity->getIdMember());
	}
}
