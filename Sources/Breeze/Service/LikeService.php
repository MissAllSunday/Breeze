<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\InvalidLikeException;
use Breeze\Repository\LikeRepositoryInterface;
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

		return [
			'contentId' => $contentId,
			'count' => $this->likeRepository->count($type, $contentId),
			'alreadyLiked' => $isContentAlreadyLiked,
			'type' => $type,
		];
	}
}
