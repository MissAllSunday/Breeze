<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;

interface WallVisibilityServiceInterface
{
	public const IDENTIFIER = 'WallVisibility';

	public function isVisibleToViewer(int $authorId, int $wallOwnerId, int $viewerId): bool;

	/**
	 * Filter statuses for the general wall feed: enforces all five gates
	 * from §5 of docs/WALL_VISIBILITY_RULES.md.
	 *
	 * @param array<int, StatusEntity> $statuses
	 * @return array<int, StatusEntity>
	 */
	public function filterStatusesForFeed(array $statuses, int $viewerId): array;

	/**
	 * Filter statuses for direct-navigation surfaces (profile wall, single
	 * status view): enforces only the safety + platform gates (gates 3, 4,
	 * 5). The buddy-inclusion and generalWall gates do not apply when the
	 * viewer reached the page on purpose.
	 *
	 * @param array<int, StatusEntity> $statuses
	 * @return array<int, StatusEntity>
	 */
	public function filterStatusesForWall(array $statuses, int $viewerId): array;

	/**
	 * @param array<int, CommentEntity> $comments
	 * @return array<int, CommentEntity>
	 */
	public function filterVisibleComments(array $comments, int $viewerId): array;

	/**
	 * Compute the mutual block set for a viewer relative to a list of known
	 * participants (e.g. the viewer's buddy list).
	 *
	 * Returns the union of:
	 *   (a) IDs the viewer has blocked, and
	 *   (b) participant IDs whose own block list contains the viewer.
	 *
	 * Intended for SQL-level pre-exclusion: pass the result to
	 * StatusRepositoryInterface::getByBuddyActivity() as $excludeIds.
	 *
	 * Non-participant strangers are NOT covered here; the PHP-level
	 * filterStatusesForFeed() remains the correctness backstop.
	 *
	 * Guests (viewerId = 0) and empty participant lists return [] immediately.
	 *
	 * @param int[] $participantIds
	 * @return int[]
	 */
	public function getMutualBlockIds(int $viewerId, array $participantIds): array;
}
