<?php

declare(strict_types=1);


namespace Breeze\Service;

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

		$likesCount = $this->likeRepository->count($type, $contentId);

		return [
			'contentId' => $contentId,
			'count' => $likesCount,
			'alreadyLiked' => $isContentAlreadyLiked,
			'type' => $type,
			'canLike' => Permissions::isAllowedTo(Permissions::LIKES_LIKE),
			'additionalInfo' => $this->buildAdditionalInfo($contentId, $likesCount, $isContentAlreadyLiked),
		];
	}

	public function buildAdditionalInfo(int $contentId, int $likesCount = 0, bool $alreadyLiked = false): string
	{
		$additionalInfo = '';
		$base = 'likes_';

		if (empty($likesCount)) {
			return $additionalInfo;
		}

		if (!empty($alreadyLiked)) {
			$base = 'you_' . $base;
			$likesCount--;
		}

		$base .= ('' !== $this->getSmfText($base . $likesCount)) ? $likesCount : 'n';

		return sprintf(
			$this->getSmfText($base),
			$this->parserText(
				'{scriptUrl}?action=likes;sa=view;ltype=msg;like={href}',
				[
					'scriptUrl' => $this->global('scripturl'),
					'href' => $contentId,
				]
			),
			$this->commaFormat((string) $likesCount)
		);
	}
}
