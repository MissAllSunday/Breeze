<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Database\ClientInterface;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\CommentHandledEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\StatusEntity;
use Breeze\LikesEnum;
use Breeze\Util\Parser;
use Breeze\Util\Validate\DataNotFoundException;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
	public function __construct(
		ClientInterface $dbClient,
		protected readonly LikeRepositoryInterface $likeRepository
	) {
		parent::__construct($dbClient);
	}

	public function getTableName(): string
	{
		return CommentEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return CommentEntity::ID;
	}

	public function getColumnPosterId(): string
	{
		return CommentEntity::USER_ID;
	}

	public function getColumns(): array
	{
		return CommentEntity::getColumns();
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function insert(CommentEntity $commentEntity): CommentHandledEntity
	{
		$commentEntity->unsetId();
		$commentEntity->setCreatedAt(time());

		$this->dbClient->insert(
			CommentEntity::TABLE,
			[
				CommentEntity::STATUS_ID => 'int',
				CommentEntity::USER_ID => 'int',
				CommentEntity::CREATED_AT => 'int',
				CommentEntity::BODY => 'string',
				CommentEntity::LIKES => 'int',
			],
			$commentEntity->toArray(),
			CommentEntity::ID
		);

		$newCommentId = $this->dbClient->getInsertedId(CommentEntity::TABLE, CommentEntity::ID);

		if ($newCommentId === 0) {
			throw new InvalidCommentException('error_save_comment');
		}

		$this->loadedUsers = $this->loadUsersInfo([$commentEntity->getUserId()]);
		$commentEntity->setBody(Parser::bbc($commentEntity->getBody()));

		$commentEntity->setId($newCommentId);

		return $this->buildHandledComments([$commentEntity])[$newCommentId];
	}

	public function getByProfile(array $userProfiles = []): array
	{
		$queryParams = array_merge($this->getDefaultQueryParamsWithLikes(LikesEnum::Comments), [
			'columnName' => StatusEntity::WALL_ID,
			'profileIds' => $userProfiles,
			'statusTable' => StatusEntity::TABLE,
			'compare' => StatusEntity::TABLE .
				'.' . StatusEntity::ID . ' = ' . self::PARENT_LIKE_IDENTIFIER . '.' . CommentEntity::STATUS_ID,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
				JOIN {db_prefix}{raw:statusTable} AS {raw:statusTable} ON {raw:compare}
				LEFT JOIN {db_prefix}{raw:likeJoin}
			WHERE {raw:columnName} IN({array_int:profileIds})',
			$queryParams
		);

		return $this->buildHandledComments($this->prepareData($request));
	}

	public function getByStatus(array $statusIds = []): array
	{
		$queryParams = array_merge($this->getDefaultQueryParamsWithLikes(LikesEnum::Comments), [
			'columnName' => CommentEntity::STATUS_ID,
			'statusIds' => $statusIds,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
				JOIN {db_prefix}{raw:likeJoin}
			WHERE {raw:columnName} IN({array_int:statusIds})',
			$queryParams
		);

		return $this->buildHandledComments($this->prepareData($request));
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $id): CommentHandledEntity
	{
		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
				LEFT JOIN {db_prefix}{raw:likeJoin}
			WHERE {raw:columnName} = ({int:id})
			LIMIT {int:limit}',
			array_merge($this->getDefaultQueryParamsWithLikes(LikesEnum::Comments), [
				'limit' => 1,
				'id' => $id,
				'columnName' => self::PARENT_LIKE_IDENTIFIER . '.' . CommentEntity::ID,
			])
		);

		if (!$request) {
			throw new DataNotFoundException('error_no_comment');
		}

		return $this->buildHandledComments($this->prepareData($request))[$id];
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function deleteById(int $commentId): bool
	{
		if (!$this->delete([$commentId])) {
			throw new DataNotFoundException('error_no_comment');
		}

		$this->setCache(self::class . '::getById' . $commentId, null);

		return true;
	}

	public function deleteByStatusId(int $statusId): bool
	{
		return $this->dbClient->delete(
			CommentEntity::TABLE,
			'WHERE ' . CommentEntity::STATUS_ID . ' ={int:statusId}',
			['statusId' => $statusId]
		);
	}

	protected function prepareData($request): array
	{
		$comments = [];
		$usersIds = [];

		while ($row = $this->dbClient->fetchAssoc($request)) {
			// Set apart the like info
			$likeInfo = [];
			$row = array_filter($row, function ($key) use (&$likeInfo, $row) {
				if (str_starts_with($key, LikeEntity::IDENTIFIER)) {
					$likeInfo[str_replace(LikeEntity::IDENTIFIER, '', $key)] = $row[$key];
				}

				return !str_starts_with($key, LikeEntity::IDENTIFIER);
			}, \ARRAY_FILTER_USE_KEY);
			$likeInfo[LikeEntity::COLUMN_ID] = $row[CommentEntity::ID];

			$comments[$row[CommentEntity::ID]] = new CommentHandledEntity(array_map(function ($rowValue) {
				return ctype_digit((string) $rowValue) ? ((int)$rowValue) : $rowValue;
			}, $row));
			$comments[$row[CommentEntity::ID]]->setLikesInfo($this->likeRepository->buildLikeDataFromRequest($likeInfo));

			$usersIds[] = (int)$row[CommentEntity::USER_ID];
		}
		die;
		$this->loadedUsers = $this->loadUsersInfo($usersIds);

		$this->dbClient->freeResult($request);

		return $comments;
	}

	/**
	 * @param array $comments [CommentHandledEntity]
	 * @return array [CommentHandledEntity]
	 */
	protected function buildHandledComments(array $comments): array
	{
		/** @var CommentHandledEntity[] $comments */
		array_walk($comments, function ($comment, $id): void {

			$commentsLoadedUsers = [$comment->getUserId()];

			if (!empty($this->loadedUsers)) {
				$comment->setUsersInfo(array_intersect_key($this->loadedUsers, array_flip($commentsLoadedUsers)));
			}
		});

		return $comments;
	}
}
