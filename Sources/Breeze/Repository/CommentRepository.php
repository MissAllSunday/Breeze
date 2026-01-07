<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\SharedEntity;
use Breeze\Entity\StatusEntity;
use Breeze\LikesEnum;
use Breeze\Util\Parser;
use Breeze\Util\Validate\DataNotFoundException;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
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
	 * @return array [CommentEntity]
	 */
	public function insert(CommentEntity $commentEntity): array
	{
		$this->dbClient->insert(
			CommentEntity::TABLE,
			[
				CommentEntity::STATUS_ID => 'int',
				CommentEntity::USER_ID => 'int',
				SharedEntity::CREATED_AT => 'int',
				CommentEntity::BODY => 'string',
				CommentEntity::LIKES => 'int',
			],
			$commentEntity->toInsert(),
			CommentEntity::ID
		);

		$newCommentId = $this->dbClient->getInsertedId(
			CommentEntity::TABLE,
			CommentEntity::ID
		);

		if ($newCommentId === 0) {
			throw new InvalidCommentException('error_save_comment');
		}

		$commentEntity->setBody(Parser::bbc($commentEntity->getBody()));
		$commentEntity->setId($newCommentId);

		return $this->setLikes(
			$this->setUsers([$commentEntity], [$commentEntity->getUserId()]),
			LikesEnum::Comments
		);
	}

	public function getByProfile(array $userProfiles = []): array
	{
		$queryParams = array_merge($this->getDefaultQueryParams(), [
			'columnName' => StatusEntity::WALL_ID,
			'profileIds' => $userProfiles,
			'statusTable' => StatusEntity::TABLE,
			'commentTable' => CommentEntity::TABLE,
			'compare' => StatusEntity::TABLE .
				'.' . StatusEntity::ID . ' = ' . self::PARENT_LIKE_IDENTIFIER . '.' . CommentEntity::STATUS_ID,
		]);

		$request = $this->dbClient->query(
			'
			SELECT {raw:columns}
			FROM {db_prefix}{raw:from}
				JOIN {db_prefix}{raw:statusTable} AS {raw:statusTable} ON {raw:compare}
			WHERE {raw:columnName} IN({array_int:profileIds})',
			$queryParams
		);

		return $this->prepareData($request);
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

		return $this->prepareData($request);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $id = 0): CommentEntity
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

		$comments = $this->prepareData($request);

		return array_shift($comments);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function deleteById(int $commentId): bool
	{
		if (!$this->delete([$commentId])) {
			throw new DataNotFoundException('error_no_comment');
		}

		// @todo handle cache clen up via event
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
			$row[CommentEntity::BODY] = Parser::bbc($row[CommentEntity::BODY]);
			$comments[$row[CommentEntity::ID]] = CommentEntity::from($row);
			$usersIds[] = (int) $row[CommentEntity::USER_ID];
		}

		$this->dbClient->freeResult($request);

		return $this->setLikes($this->setUsers($comments, $usersIds), LikesEnum::Comments);
	}

	/**
	 * @param array $comments [CommentEntity]
	 * @param array $usersIds [int]
	 * @return array [CommentEntity]
	 */
	protected function setUsers(array $comments, array $usersIds): array
	{
		// Return early if there is nothing to work on
		if (empty($comments) || empty($usersIds)) {
			return [];
		}

		$loadedUsers = $this->loadUsersInfo($usersIds);

		/** @var CommentEntity[] $comments */
		array_walk($comments, function ($comment, $id) use ($loadedUsers): void {
			$commentsLoadedUsers = [$comment->getUserId()];

			if (!empty($loadedUsers)) {
				$comment->setUsersInfo(array_intersect_key($loadedUsers, array_flip($commentsLoadedUsers)));
			}
		});

		return $comments;
	}
}
