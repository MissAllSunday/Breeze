<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Breeze;
use Breeze\Entity\LikeEntity;
use Breeze\Model\LikeModelInterface;
use Breeze\PermissionsEnum;
use Breeze\Traits\PermissionsTrait;
use Breeze\Traits\TimeTrait;

class LikeRepository extends BaseRepository implements LikeRepositoryInterface
{
 use PermissionsTrait;
 use TimeTrait;

	public function __construct(
		private readonly LikeModelInterface $likeModel
	) {
	}

	public function getLikeInfo(string $type, int $contentId): array
	{
		$likeInfo = [];
		$likes = $this->likeModel->getByContent($type, $contentId);
		$usersInfo = $this->loadUsersInfo(array_unique(array_column($likes, LikeEntity::COLUMN_ID_MEMBER)));

		foreach ($likes as $key => $like) {
			$likeInfo[$key]['profile'] = $usersInfo[$like[LikeEntity::COLUMN_ID_MEMBER]];
			$likeInfo[$key]['timestamp'] = empty($like[LikeEntity::COLUMN_TIME]) ? '' : timeFormat($like[LikeEntity::COLUMN_TIME]);
		}

		return $likeInfo;
	}

	public function isContentAlreadyLiked(string $type, int $contentId, int $userId): bool
	{
		return (bool) $this->likeModel->checkLike($type, $contentId, $userId);
	}

	/**
	 * @throws InvalidLikeException
	 */
	public function delete(string $type, int $contentId, int $userId): void
	{
		$wasDeleted = $this->likeModel->deleteContent($type, $contentId, $userId);

		if (!$wasDeleted) {
			throw new InvalidLikeException('error_no_like');
		}
	}

	/**
	 * @throws InvalidLikeException
	 */
	public function insert(string $type, int $contentId, int $userId): void
	{
		$this->likeModel->insertContent($type, $contentId, $userId);

		if (!$this->isContentAlreadyLiked($type, $contentId, $userId)) {
			throw new InvalidLikeException('error_save_like');
		}
	}

	public function count(string $type, int $contentId): int
	{
		return $this->likeModel->countContent($type, $contentId);
	}

	public function appendLikeData(array $items, string $itemIdName): array
	{
		return array_map(function (array $item) use ($itemIdName): array {
			$item['likesInfo'] = $this->buildLikeData(
				$item[LikeEntity::IDENTIFIER . LikeEntity::COLUMN_TYPE],
				$item[$itemIdName],
				$item[LikeEntity::IDENTIFIER . LikeEntity::COLUMN_ID_MEMBER],
			);

			return $item;
		}, $items);
	}

	public function buildLikeData(
		?string $type,
		?int    $contentId,
		?int    $userId,
		?bool   $isContentAlreadyLiked = null
	): array {
		$likeData = [
			'contentId' => $contentId,
			'count' => 0,
			'alreadyLiked' => false,
			'type' => $type,
			'canLike' => $this->isAllowedTo(PermissionsEnum::LIKES_LIKE),
			'additionalInfo' => [],
		];
		$base = LikeEntity::IDENTIFIER;

		if ($contentId === null ||
			($type === null || $type === '' || $type === '0')) {
			return $likeData;
		}

		if ($isContentAlreadyLiked === null) {
			$isContentAlreadyLiked = $this->isContentAlreadyLiked($type, $contentId, $userId);
		}

		$likesCount = $likesTextCount = $this->count($type, $contentId);

		if ($isContentAlreadyLiked) {
			$base = 'you_' . $base;
			$likesTextCount = $likesCount - 1;
		}

		$base .= ($this->getText($base . $likesTextCount) !== '') ? $likesTextCount : 'n';

		return array_merge($likeData, [
			'count' => $likesCount,
			'additionalInfo' => [
				'text' => sprintf(
					$this->getText($base),
					$this->commaFormat((string) $likesTextCount)
				),
				'href' => $this->parserText(
					'{scriptUrl}?action=likes;sa=view;ltype={ltype};like={likeId}',
					[
						'ltype' => $type,
						'scriptUrl' => $this->global(Breeze::SCRIPT_URL),
						'likeId' => $contentId,
					]
				),
			],
			'alreadyLiked' => $isContentAlreadyLiked,
		]);
	}

	/**
	 * @throws InvalidDataException
	 */
	public function likeContent(string $type, int $contentId, int $userId): array
	{
		$isContentAlreadyLiked = $this->isContentAlreadyLiked($type, $contentId, $userId);
		$isContentAlreadyLiked ?
			$this->delete($type, $contentId, $userId) :
			$this->insert($type, $contentId, $userId);

		return $this->buildLikeData($type, $contentId, $userId, !$isContentAlreadyLiked);
	}

	public function getById(int $id): array
	{
		return [];
	}
}
