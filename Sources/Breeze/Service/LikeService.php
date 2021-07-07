<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\LikeEntity;
use Breeze\Repository\InvalidLikeException;
use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateGateway;

class LikeService extends BaseService implements LikeServiceInterface
{
	private LikeRepositoryInterface $likeRepository;

	public function __construct(
		LikeRepositoryInterface $likeRepository
	) {

		$this->likeRepository = $likeRepository;
	}

	public function getByContent(string $type, int $contentId): array
	{
		return $this->likeRepository->getByContent($type, $contentId);
	}

	public function isContentAlreadyLiked(string $type, int $contentId, int $userId): bool
	{
		return $this->likeRepository->isContentAlreadyLiked($type, $contentId, $userId);
	}

	public function likeContent(string $type, int $contentId, int $userId): array
	{
		$isContentAlreadyLiked = $this->likeRepository->isContentAlreadyLiked($type, $contentId, $userId);

		try {
			$isContentAlreadyLiked ?
				$this->likeRepository->delete($type, $contentId, $userId) :
				$this->likeRepository->insert($type, $contentId, $userId);
		} catch (InvalidLikeException $e) {
			return [
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $e->getMessage(),
			];
		}

		return $this->buildLikeData($type, $contentId, $userId, !$isContentAlreadyLiked);
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

		if (null === $isContentAlreadyLiked) {
			$isContentAlreadyLiked = $this->isContentAlreadyLiked($type, $contentId, $userId);
		}

		$likesCount = $likesTextCount = $this->likeRepository->count($type, $contentId);

		if ($isContentAlreadyLiked) {
			$base = 'you_' . $base;
			$likesTextCount = $likesCount - 1;
		}

		$base .= ('' !== $this->getSmfText($base . $likesTextCount)) ? $likesTextCount : 'n';

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
}
