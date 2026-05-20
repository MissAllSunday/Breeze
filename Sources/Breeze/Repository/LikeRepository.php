<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Breeze;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Enums\PermissionsEnum;

class LikeRepository extends BaseRepository implements LikeRepositoryInterface
{
	public const string CACHE_BY_CONTENT = 'getByContent';

	public function __construct(
		\Breeze\Database\ClientInterface $dbClient
	) {
		parent::__construct($dbClient);
	}

	public function getTableName(): string
	{
		return LikeEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return LikeEntity::ID;
	}

	public function getColumnPosterId(): string
	{
		return LikeEntity::ID_MEMBER;
	}

	public function getColumns(): array
	{
		return LikeEntity::getColumns();
	}

	/**
	 * @param array $contentIds [int]
	 * @return array [LikeInfoEntity]
	 */
	public function getByContent(LikesEnum $type, array $contentIds): array
	{
		$cacheKey = sprintf(
			'%s_%s_%s',
			self::CACHE_BY_CONTENT,
			$type->value,
			implode('_', $contentIds)
		);

		$cached = $this->getCache($cacheKey);
		if ($cached !== []) {
			return array_map(function ($item) {
				return is_array($item) ? LikeInfoEntity::from($item) : $item;
			}, $cached);
		}

		$likes = [];

		$request = $this->dbClient->query(
			'
			SELECT ' . implode(', ', LikeEntity::getColumns()) . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::TYPE . ' = {string:type}
				AND ' . LikeEntity::ID . ' IN({array_int:contentIds})',
			[
				'contentIds' => array_map('intval', $contentIds),
				'type' => $type->value,
			]
		);

		foreach ($contentIds as $contentId) {
			$likes[$contentId] = [];
		}

		while ($row = $this->dbClient->fetchAssoc($request)) {
			$likes[$row[LikeEntity::ID]][$row[LikeEntity::ID_MEMBER]] = LikeEntity::from($row);
		}
		$this->dbClient->freeResult($request);

		array_walk($likes, function (&$likeData, $contentId) use ($type): void {
			$likeData = $this->buildLikeInfo($likeData, $type, (int) $contentId);
		});

		$this->setCache($cacheKey, $likes);

		return $likes;
	}

	public function isContentAlreadyLiked(LikeEntity $likeEntity): bool
	{
		$request = $this->dbClient->query(
			'
			SELECT ' . LikeEntity::ID . '
			FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::ID_MEMBER . ' = {int:userId}
				AND ' . LikeEntity::TYPE . ' = {string:type}
				AND ' . LikeEntity::ID . ' = {int:contentId}',
			[
				'userId' => $likeEntity->getIdMember(),
				'type' => $likeEntity->getContentType()->value,
				'contentId' => $likeEntity->getContentId(),
			]
		);

		$numRows = $this->dbClient->numRows($request);

		$this->dbClient->freeResult($request);

		return $numRows > 0;
	}

	/**
	 * @throws InvalidLikeException
	 */
	public function deleteByContent(LikeEntity $likeEntity): void
	{
		$wasDeleted = $this->dbClient->delete(
			LikeEntity::TABLE,
			'WHERE ' . LikeEntity::ID . ' = {int:contentId}
				AND ' . LikeEntity::TYPE . ' = {string:type}
				AND ' . LikeEntity::ID_MEMBER . ' = {int:userId}',
			[
				'contentId' => $likeEntity->getContentId(),
				'type' => $likeEntity->getContentType()->value,
				'userId' => $likeEntity->getIdMember(),
			]
		);

		if (!$wasDeleted) {
			throw new InvalidLikeException('error_no_like');
		}

		// Invalidate cache for this content
		$this->invalidateContentCache($likeEntity->getContentType(), $likeEntity->getContentId());
	}

	public function insert(LikeEntity $likeEntity): LikeInfoEntity
	{
		$this->dbClient->insert(LikeEntity::TABLE, [
			LikeEntity::ID => 'int',
			LikeEntity::TYPE => 'string',
			LikeEntity::ID_MEMBER => 'int',
			LikeEntity::TIME => 'int',
		], $likeEntity->toInsert(), [LikeEntity::ID, LikeEntity::TYPE, LikeEntity::ID_MEMBER]);

		// Invalidate cache for this content
		$this->invalidateContentCache($likeEntity->getContentType(), $likeEntity->getContentId());

		return $this->buildLikeInfo([$likeEntity], $likeEntity->getContentType(), $likeEntity->getContentId());
	}

	public function count(LikeEntity $likeEntity): int
	{
		$result = $this->dbClient->query(
			'
			SELECT {int:contentId}
			FROM {db_prefix}' . LikeEntity::TABLE . '
				WHERE ' . LikeEntity::ID . ' = {int:contentId}
				AND ' . LikeEntity::TYPE . ' = {string:type}',
			[
				'contentId' => $likeEntity->getContentId(),
				'type' => $likeEntity->getContentType()->value,
			]
		);

		$rowCount = $this->dbClient->numRows($result);

		$this->dbClient->freeResult($result);

		return $rowCount;
	}

	/**
	 * @param array $likeEntities [LikeEntity]
	 */
	public function buildLikeInfo(array $likeEntities, LikesEnum $type, int $contentId): LikeInfoEntity
	{
		// Only count data with actual likes
		$likeFilledEntities = array_filter($likeEntities, function ($like): bool {
			return !empty($like);
		});

		$likesCount = count($likeFilledEntities);
		$likesTextCount = $likesCount;

		$usersIds = array_column($likeFilledEntities, LikeEntity::ID_MEMBER);
		$alreadyLiked = in_array($this->global('user_info')['id'], $usersIds);
		$usersData = $this->loadUsersInfo($usersIds);

		$likeInfo = LikeInfoEntity::from();

		array_walk($likeFilledEntities, function ($like) use ($usersData): void {
			$like->setUserData($usersData[$like->getIdMember()] ?? []);
		});

		$likeInfo->setContentType($type);
		$likeInfo->setLikes($likeFilledEntities);
		$likeInfo->setContentId($contentId);
		$likeInfo->setCanLike($this->isAllowedTo(PermissionsEnum::LIKES_LIKE));
		$likeInfo->setAlreadyLiked($alreadyLiked);

		$base = LikeEntity::IDENTIFIER;
		if ($alreadyLiked) {
			$base = 'you_' . $base;
			$likesTextCount = $likesCount - 1;
		}

		$base .= ($this->getText($base . $likesTextCount) !== '') ? $likesTextCount : 'n';

		$likeInfo->setText(sprintf(
			$this->getText($base),
			$this->commaFormat((string) $likesTextCount)
		));
		$likeInfo->setHref($this->parserText(
			'{scriptUrl}?action=likes;sa=view;ltype={ltype};like={likeId}',
			[
				'ltype' => $type->value,
				'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
				'likeId' => $contentId,
			]
		));

		return $likeInfo;
	}

	/**
	 *@throws InvalidDataException
	 * @throws InvalidLikeException
	 */
	public function likeContent(LikeEntity $likeEntity): ?LikeInfoEntity
	{
		$isContentAlreadyLiked = $this->isContentAlreadyLiked($likeEntity);
		$contentId = $likeEntity->getContentId();

		if ($isContentAlreadyLiked) {
			$this->deleteByContent($likeEntity);

			return $this->getByContent($likeEntity->getContentType(), [$contentId])[$contentId];
		}

		return $this->insert($likeEntity);
	}

	public function getById(int $id): null
	{
		return null;
	}

	public function countOrphans(): int
	{
		$request = $this->dbClient->query(
			'
			SELECT COUNT(*)
			FROM {db_prefix}' . LikeEntity::TABLE . ' AS l
			LEFT JOIN {db_prefix}' . StatusEntity::TABLE . ' AS s ON (s.id = l.content_id AND l.content_type = {string:status_type})
			WHERE s.id IS NULL AND l.content_type = {string:status_type}',
			[
				'status_type' => LikesEnum::Status->value,
			]
		);
		[$orphanStatusLikes] = $this->dbClient->fetchRow($request);
		$this->dbClient->freeResult($request);

		$request = $this->dbClient->query(
			'
			SELECT COUNT(*)
			FROM {db_prefix}' . LikeEntity::TABLE . ' AS l
			LEFT JOIN {db_prefix}' . CommentEntity::TABLE . ' AS c ON (c.id = l.content_id AND l.content_type = {string:comment_type})
			WHERE c.id IS NULL AND l.content_type = {string:comment_type}',
			[
				'comment_type' => LikesEnum::Comments->value,
			]
		);
		[$orphanCommentLikes] = $this->dbClient->fetchRow($request);
		$this->dbClient->freeResult($request);

		return (int) $orphanStatusLikes + (int) $orphanCommentLikes;
	}

	public function deleteOrphans(): void
	{
		$this->dbClient->query(
			'DELETE FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::TYPE . ' = {string:status_type}
				AND ' . LikeEntity::ID . ' NOT IN (
					SELECT id FROM {db_prefix}' . StatusEntity::TABLE . '
				)',
			[
				'status_type' => LikesEnum::Status->value,
			]
		);

		$this->dbClient->query(
			'DELETE FROM {db_prefix}' . LikeEntity::TABLE . '
			WHERE ' . LikeEntity::TYPE . ' = {string:comment_type}
				AND ' . LikeEntity::ID . ' NOT IN (
					SELECT id FROM {db_prefix}' . CommentEntity::TABLE . '
				)',
			[
				'comment_type' => LikesEnum::Comments->value,
			]
		);
	}

	/**
	 * Invalidate cache for a specific content
	 */
	protected function invalidateContentCache(LikesEnum $type, int $contentId): void
	{
		// Invalidate cache for this specific content
		$this->setCache(
			sprintf('%s_%s_%d', self::CACHE_BY_CONTENT, $type->value, $contentId),
			null
		);
	}
}
