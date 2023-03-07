<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\LikeEntity;
use Breeze\Model\LikeModelInterface;
use Breeze\Util\Permissions;

class LikeRepository extends BaseRepository implements LikeRepositoryInterface
{
	private LikeModelInterface $likeModel;

	public function __construct(
		LikeModelInterface $likeModel
	) {
		$this->likeModel = $likeModel;
	}

	public function getLikeInfo(string $type, int $contentId): array
	{
		$likeInfo = [];
		$likes = $this->likeModel->getByContent($type, $contentId);
		$userIds = array_unique(array_filter($likes, function ($like) {
			return $like[LikeEntity::ID_MEMBER];
		}));

		$usersInfo = $this->loadUsersInfo($userIds);

		foreach ($likes as $key => $like) {
			$likes[$key]['profile'] = $usersInfo[$like[LikeEntity::ID_MEMBER]];
			$likes[$key]['timestamp'] = !empty($like[LikeEntity::TIME]) ? timeformat($like[LikeEntity::TIME]) : '';
		}

		return $likes;
	}

	public function isContentAlreadyLiked(string $type, int $contentId, int $userId): bool
	{
		return (bool)$this->likeModel->checkLike($type, $contentId, $userId);
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
		return array_map(function ($item) use ($itemIdName): array {
			$item['likesInfo'] = $this->buildLikeData(
				$item[LikeEntity::IDENTIFIER . LikeEntity::TYPE],
				$item[$itemIdName],
				$item[LikeEntity::IDENTIFIER . LikeEntity::ID_MEMBER],
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
			'canLike' => Permissions::isAllowedTo(Permissions::LIKES_LIKE),
			'additionalInfo' => [],
		];
		$base = LikeEntity::IDENTIFIER;

		if (empty($contentId) ||
			empty($type)) {
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
						'scriptUrl' => $this->global('scripturl'),
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

	public function getById(int $id): void
	{
	}
}
