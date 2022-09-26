<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\LikeEntity;
use Breeze\Model\LikeModelInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateGateway;

class LikeRepository extends BaseRepository implements LikeRepositoryInterface
{
	private LikeModelInterface $likeModel;

	public function __construct(
		LikeModelInterface $likeModel
	) {
		$this->likeModel = $likeModel;
	}

	public function getByContent(string $type, int $contentId): array
	{
		return $this->likeModel->getByContent($type, $contentId);
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

	public function appendLikeData(array $items, string $itemIdName) : array
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
		?int $contentId,
		?int $userId,
		?bool $isContentAlreadyLiked = null
	): array {
		$likeData = [
			'contentId' => $contentId,
			'count' => 0,
			'alreadyLiked' => false,
			'type' => $type,
			'canLike' => Permissions::isAllowedTo(Permissions::LIKES_LIKE),
			'additionalInfo' => '',
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

		$base .= ($this->getSmfText($base . $likesTextCount) !== '') ? $likesTextCount : 'n';

		$additionalInfo =  sprintf(
			$this->getSmfText($base),
			$this->parserText(
				'{scriptUrl}?action=likes;sa=view;ltype=msg;like={href}',
				[
					'scriptUrl' => $this->global('scripturl'),
					'href' => $contentId,
				]
			),
			$this->commaFormat((string) $likesTextCount)
		);

		return array_merge($likeData, [
			'count' => $likesCount,
			'additionalInfo' => $additionalInfo,
			'alreadyLiked' => $isContentAlreadyLiked,
		]);
	}

	public function likeContent(string $type, int $contentId, int $userId): array
	{
		$isContentAlreadyLiked = $this->isContentAlreadyLiked($type, $contentId, $userId);

		try {
			$isContentAlreadyLiked ?
				$this->delete($type, $contentId, $userId) :
				$this->insert($type, $contentId, $userId);
		} catch (InvalidLikeException $invalidLikeException) {
			return [
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $invalidLikeException->getMessage(),
			];
		}

		return $this->buildLikeData($type, $contentId, $userId, !$isContentAlreadyLiked);
	}
}
