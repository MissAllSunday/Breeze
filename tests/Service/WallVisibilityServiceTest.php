<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\SettingsRepositoryInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Red tests for the not-yet-existing WallVisibilityService.
 *
 * Pins the five-gate contract from docs/WALL_VISIBILITY_RULES.md §5. The
 * platform gate is delegated to PermissionsServiceInterface via a new
 * `canViewActivity()` method that will be added in Phase 1.
 */
#[AllowMockObjectsWithoutExpectations]
class WallVisibilityServiceTest extends TestCase
{
	private SettingsRepositoryInterface|MockObject $userSettingsRepository;

	private PermissionsServiceInterface|MockObject $permissionsService;

	private WallVisibilityService $wallVisibilityService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->userSettingsRepository = $this->createMock(SettingsRepositoryInterface::class);
		$this->permissionsService = $this->createStub(PermissionsServiceInterface::class);
		$this->permissionsService->method('canViewActivity')->willReturn(true);
		$this->permissionsService->method('canViewProfileWall')->willReturn(true);

		$this->wallVisibilityService = new WallVisibilityService(
			$this->userSettingsRepository,
			$this->permissionsService,
		);
	}

	/**
	 * @param array<int, array<string, mixed>> $settingsById
	 */
	private function stubSettings(array $settingsById): void
	{
		$entitiesById = [];
		foreach ($settingsById as $id => $data) {
			$entitiesById[$id] = UserSettingsEntity::from($data);
		}

		$this->userSettingsRepository->method('getByIds')
			->willReturnCallback(
				static fn (array $ids) => array_intersect_key($entitiesById, array_flip($ids))
			);
		$this->userSettingsRepository->method('getById')
			->willReturnCallback(
				static fn (int $id) => $entitiesById[$id] ?? UserSettingsEntity::from([])
			);
	}

	#[DataProvider('isVisibleToViewerProvider')]
	public function testIsVisibleToViewer(
		array $settingsById,
		bool $canViewActivity,
		int $authorId,
		int $wallOwnerId,
		int $viewerId,
		bool $expected
	): void {
		$this->stubSettings($settingsById);
		$this->permissionsService = $this->createStub(PermissionsServiceInterface::class);
		$this->permissionsService->method('canViewActivity')->willReturn($canViewActivity);
		$this->wallVisibilityService = new WallVisibilityService(
			$this->userSettingsRepository,
			$this->permissionsService,
		);

		$this->assertSame(
			$expected,
			$this->wallVisibilityService->isVisibleToViewer($authorId, $wallOwnerId, $viewerId)
		);
	}

	public static function isVisibleToViewerProvider(): array
	{
		// Conventions: viewer=100, author=200, wallOwner=300 (or 200 when post is on the author's own wall).
		// Settings keys passed to UserSettingsEntity::from(): comma-strings for buddies/blockList; generalWall as 0|1.
		$open = ['generalWall' => 1];
		$viewerBuddyOfAuthor = ['buddies' => '200', 'generalWall' => 1];

		return [
			'all gates pass → visible' => [
				'settingsById' => [100 => $viewerBuddyOfAuthor, 200 => $open, 300 => $open],
				'canViewActivity' => true,
				'authorId' => 200, 'wallOwnerId' => 300, 'viewerId' => 100,
				'expected' => true,
			],
			'inclusion gate fails: neither author nor wall owner is a buddy' => [
				'settingsById' => [100 => $open, 200 => $open, 300 => $open],
				'canViewActivity' => true,
				'authorId' => 200, 'wallOwnerId' => 300, 'viewerId' => 100,
				'expected' => false,
			],
			'feature gate fails: author has generalWall = 0' => [
				'settingsById' => [
					100 => $viewerBuddyOfAuthor,
					200 => ['generalWall' => 0],
					300 => $open,
				],
				'canViewActivity' => true,
				'authorId' => 200, 'wallOwnerId' => 300, 'viewerId' => 100,
				'expected' => false,
			],
			'safety gate fails: author has viewer in block list' => [
				'settingsById' => [
					100 => $viewerBuddyOfAuthor,
					200 => ['blockList' => '100', 'generalWall' => 1],
					300 => $open,
				],
				'canViewActivity' => true,
				'authorId' => 200, 'wallOwnerId' => 300, 'viewerId' => 100,
				'expected' => false,
			],
			'safety gate fails (symmetric): viewer has author in block list' => [
				'settingsById' => [
					100 => ['buddies' => '200', 'blockList' => '200', 'generalWall' => 1],
					200 => $open, 300 => $open,
				],
				'canViewActivity' => true,
				'authorId' => 200, 'wallOwnerId' => 300, 'viewerId' => 100,
				'expected' => false,
			],
			'safety gate fails: wall owner has viewer in block list' => [
				'settingsById' => [
					100 => $viewerBuddyOfAuthor,
					200 => $open,
					300 => ['blockList' => '100', 'generalWall' => 1],
				],
				'canViewActivity' => true,
				'authorId' => 200, 'wallOwnerId' => 300, 'viewerId' => 100,
				'expected' => false,
			],
			'safety gate fails (symmetric): viewer has wall owner in block list' => [
				'settingsById' => [
					100 => ['buddies' => '200', 'blockList' => '300', 'generalWall' => 1],
					200 => $open, 300 => $open,
				],
				'canViewActivity' => true,
				'authorId' => 200, 'wallOwnerId' => 300, 'viewerId' => 100,
				'expected' => false,
			],
			'platform gate fails: viewer lacks activity-view permission' => [
				'settingsById' => [100 => $viewerBuddyOfAuthor, 200 => $open, 300 => $open],
				'canViewActivity' => false,
				'authorId' => 200, 'wallOwnerId' => 300, 'viewerId' => 100,
				'expected' => false,
			],
			'self-author on own wall → visible without a buddy relationship' => [
				'settingsById' => [100 => $open],
				'canViewActivity' => true,
				'authorId' => 100, 'wallOwnerId' => 100, 'viewerId' => 100,
				'expected' => true,
			],
			'self-author on a stranger wall → visible without a buddy relationship' => [
				'settingsById' => [100 => $open, 300 => $open],
				'canViewActivity' => true,
				'authorId' => 100, 'wallOwnerId' => 300, 'viewerId' => 100,
				'expected' => true,
			],
			'self-author with own generalWall = 0 → still visible to self' => [
				'settingsById' => [100 => ['generalWall' => 0]],
				'canViewActivity' => true,
				'authorId' => 100, 'wallOwnerId' => 100, 'viewerId' => 100,
				'expected' => true,
			],
			'self-author on wall of someone who blocked viewer → hidden (safety still applies)' => [
				'settingsById' => [
					100 => $open,
					300 => ['blockList' => '100', 'generalWall' => 1],
				],
				'canViewActivity' => true,
				'authorId' => 100, 'wallOwnerId' => 300, 'viewerId' => 100,
				'expected' => false,
			],
		];
	}

	#[DataProvider('filterStatusesForFeedProvider')]
	public function testFilterStatusesForFeed(
		array $settingsById,
		array $statusRows,
		int $viewerId,
		array $expectedVisibleIds
	): void {
		$this->stubSettings($settingsById);

		$statuses = [];
		foreach ($statusRows as $row) {
			$statuses[$row['id']] = StatusEntity::from($row);
		}

		$visible = $this->wallVisibilityService->filterStatusesForFeed($statuses, $viewerId);

		$this->assertSame($expectedVisibleIds, array_values(array_map(
			static fn (StatusEntity $s) => $s->getId(),
			$visible
		)));
	}

	public static function filterStatusesForFeedProvider(): array
	{
		// Status rows carry id/user_id/wall_id; body is irrelevant to visibility.
		$open = ['generalWall' => 1];
		$buddyOf200 = ['buddies' => '200', 'generalWall' => 1];
		$buddyOf300 = ['buddies' => '300', 'generalWall' => 1];

		return [
			'author is a buddy, no blocks → visible' => [
				'settingsById' => [100 => $buddyOf200, 200 => $open],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 200]],
				'viewerId' => 100,
				'expectedVisibleIds' => [1],
			],
			'wall owner is a buddy, author is a stranger → visible' => [
				'settingsById' => [100 => $buddyOf300, 200 => $open, 300 => $open],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [1],
			],
			'author has viewer in block list → hidden (even if wall owner is buddy)' => [
				'settingsById' => [
					100 => $buddyOf300,
					200 => ['blockList' => '100', 'generalWall' => 1],
					300 => $open,
				],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'viewer has author in block list → hidden (symmetric direction)' => [
				'settingsById' => [
					100 => ['buddies' => '200', 'blockList' => '200', 'generalWall' => 1],
					200 => $open,
				],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 200]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'wall owner has viewer in block list → hidden' => [
				'settingsById' => [
					100 => $buddyOf300,
					200 => $open,
					300 => ['blockList' => '100', 'generalWall' => 1],
				],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'viewer has wall owner in block list → hidden (symmetric direction)' => [
				'settingsById' => [
					100 => ['buddies' => '300', 'blockList' => '300', 'generalWall' => 1],
					200 => $open,
					300 => $open,
				],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'author has generalWall = 0 → hidden' => [
				'settingsById' => [100 => $buddyOf200, 200 => ['generalWall' => 0]],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 200]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'viewer has no buddies → empty result' => [
				'settingsById' => [100 => $open, 200 => $open],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 200]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'self-authored post on own wall → visible without a buddy relationship' => [
				'settingsById' => [100 => $open],
				'statusRows' => [['id' => 1, 'user_id' => 100, 'wall_id' => 100]],
				'viewerId' => 100,
				'expectedVisibleIds' => [1],
			],
			'self-authored post on a stranger wall → visible without a buddy relationship' => [
				'settingsById' => [100 => $open, 300 => $open],
				'statusRows' => [['id' => 1, 'user_id' => 100, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [1],
			],
			'self-authored post with own generalWall = 0 → still visible to self' => [
				'settingsById' => [100 => ['generalWall' => 0]],
				'statusRows' => [['id' => 1, 'user_id' => 100, 'wall_id' => 100]],
				'viewerId' => 100,
				'expectedVisibleIds' => [1],
			],
			'self-authored post on wall of someone who blocked viewer → hidden (safety still applies)' => [
				'settingsById' => [
					100 => $open,
					300 => ['blockList' => '100', 'generalWall' => 1],
				],
				'statusRows' => [['id' => 1, 'user_id' => 100, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
		];
	}

	/**
	 * filterStatusesForWall enforces only the safety + platform gates
	 * (gates 3, 4, 5 from §5 of the rules doc). The inclusion gate (buddy
	 * relationship) and the feature gate (generalWall opt-out) do not apply
	 * when the viewer is on a wall or single-status page by direct
	 * navigation.
	 */
	#[DataProvider('filterStatusesForWallProvider')]
	public function testFilterStatusesForWall(
		array $settingsById,
		array $statusRows,
		int $viewerId,
		array $expectedVisibleIds
	): void {
		$this->stubSettings($settingsById);

		$statuses = [];
		foreach ($statusRows as $row) {
			$statuses[$row['id']] = StatusEntity::from($row);
		}

		$visible = $this->wallVisibilityService->filterStatusesForWall($statuses, $viewerId);

		$this->assertSame($expectedVisibleIds, array_values(array_map(
			static fn (StatusEntity $s) => $s->getId(),
			$visible
		)));
	}

	public static function filterStatusesForWallProvider(): array
	{
		$open = ['generalWall' => 1];

		return [
			'stranger author on stranger wall, no blocks → visible' => [
				'settingsById' => [100 => $open, 200 => $open, 300 => $open],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [1],
			],
			'author has generalWall = 0 → still visible on a wall' => [
				'settingsById' => [100 => $open, 200 => ['generalWall' => 0]],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 200]],
				'viewerId' => 100,
				'expectedVisibleIds' => [1],
			],
			'author has viewer in block list → hidden' => [
				'settingsById' => [
					100 => $open,
					200 => ['blockList' => '100', 'generalWall' => 1],
					300 => $open,
				],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'viewer has author in block list → hidden (symmetric)' => [
				'settingsById' => [
					100 => ['blockList' => '200', 'generalWall' => 1],
					200 => $open,
					300 => $open,
				],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'wall owner has viewer in block list → hidden' => [
				'settingsById' => [
					100 => $open,
					200 => $open,
					300 => ['blockList' => '100', 'generalWall' => 1],
				],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'viewer has wall owner in block list → hidden (symmetric)' => [
				'settingsById' => [
					100 => ['blockList' => '300', 'generalWall' => 1],
					200 => $open,
					300 => $open,
				],
				'statusRows' => [['id' => 1, 'user_id' => 200, 'wall_id' => 300]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'wall owner viewing their own wall, no blocks → all visible' => [
				'settingsById' => [2 => $open, 1 => $open],
				'statusRows' => [
					['id' => 22, 'user_id' => 1, 'wall_id' => 2],
					['id' => 23, 'user_id' => 2, 'wall_id' => 2],
				],
				'viewerId' => 2,
				'expectedVisibleIds' => [22, 23],
			],
		];
	}

	public function testFilterStatusesForWallHidesAllWhenCanViewProfileWallFails(): void
	{
		$mock = $this->createStub(PermissionsServiceInterface::class);
		$mock->method('canViewProfileWall')->willReturn(false);
		$mock->method('canViewActivity')->willReturn(true);

		$service = new WallVisibilityService($this->userSettingsRepository, $mock);

		$statuses = [22 => StatusEntity::from(['id' => 22, 'user_id' => 1, 'wall_id' => 2])];

		$this->assertSame([], $service->filterStatusesForWall($statuses, 2));
	}

	/**
	 * filterVisibleComments only enforces the per-comment safety gate
	 * (symmetric block between viewer and comment author). The other four
	 * gates are evaluated at the parent-status level — the caller never
	 * passes comments of a hidden status.
	 */
	#[DataProvider('filterVisibleCommentsProvider')]
	public function testFilterVisibleComments(
		array $settingsById,
		array $commentRows,
		int $viewerId,
		array $expectedVisibleIds
	): void {
		$this->stubSettings($settingsById);

		$comments = [];
		foreach ($commentRows as $row) {
			$comments[$row['id']] = CommentEntity::from($row);
		}

		$visible = $this->wallVisibilityService->filterVisibleComments($comments, $viewerId);

		$this->assertSame($expectedVisibleIds, array_values(array_map(
			static fn (CommentEntity $c) => $c->getId(),
			$visible
		)));
	}

	public static function filterVisibleCommentsProvider(): array
	{
		$open = ['generalWall' => 1];

		return [
			'comment author has no block relationship → visible' => [
				'settingsById' => [100 => $open, 200 => $open],
				'commentRows' => [['id' => 1, 'status_id' => 1, 'user_id' => 200]],
				'viewerId' => 100,
				'expectedVisibleIds' => [1],
			],
			'comment author has viewer in block list → hidden' => [
				'settingsById' => [
					100 => $open,
					200 => ['blockList' => '100', 'generalWall' => 1],
				],
				'commentRows' => [['id' => 1, 'status_id' => 1, 'user_id' => 200]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
			'viewer has comment author in block list → hidden (symmetric)' => [
				'settingsById' => [
					100 => ['blockList' => '200', 'generalWall' => 1],
					200 => $open,
				],
				'commentRows' => [['id' => 1, 'status_id' => 1, 'user_id' => 200]],
				'viewerId' => 100,
				'expectedVisibleIds' => [],
			],
		];
	}

	// -----------------------------------------------------------------------
	// getMutualBlockIds
	// -----------------------------------------------------------------------

	#[DataProvider('getMutualBlockIdsProvider')]
	public function testGetMutualBlockIds(
		array $settingsById,
		int $viewerId,
		array $participantIds,
		array $expectedIds
	): void {
		$this->stubSettings($settingsById);

		$result = $this->wallVisibilityService->getMutualBlockIds($viewerId, $participantIds);

		$this->assertSame($expectedIds, $result);
	}

	public static function getMutualBlockIdsProvider(): array
	{
		$open = ['generalWall' => 1];

		return [
			'no blocks anywhere → empty result' => [
				'settingsById' => [100 => $open, 200 => $open, 300 => $open],
				'viewerId' => 100,
				'participantIds' => [200, 300],
				'expectedIds' => [],
			],
			'viewer blocked a participant → that id appears in result' => [
				'settingsById' => [
					100 => ['blockList' => '200', 'generalWall' => 1],
					200 => $open,
					300 => $open,
				],
				'viewerId' => 100,
				'participantIds' => [200, 300],
				'expectedIds' => [200],
			],
			'participant blocked viewer → that id appears in result' => [
				'settingsById' => [
					100 => $open,
					200 => $open,
					300 => ['blockList' => '100', 'generalWall' => 1],
				],
				'viewerId' => 100,
				'participantIds' => [200, 300],
				'expectedIds' => [300],
			],
			'both directions blocked → combined, deduplicated result' => [
				'settingsById' => [
					100 => ['blockList' => '200', 'generalWall' => 1],
					200 => $open,
					300 => ['blockList' => '100', 'generalWall' => 1],
				],
				'viewerId' => 100,
				'participantIds' => [200, 300],
				'expectedIds' => [200, 300],
			],
			'viewer is a guest (id=0) → empty result, no lookup performed' => [
				'settingsById' => [200 => $open, 300 => $open],
				'viewerId' => 0,
				'participantIds' => [200, 300],
				'expectedIds' => [],
			],
			'no participants → empty result' => [
				'settingsById' => [100 => $open],
				'viewerId' => 100,
				'participantIds' => [],
				'expectedIds' => [],
			],
		];
	}
}
