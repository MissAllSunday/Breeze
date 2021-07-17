<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\LikeEntity;

abstract class BaseLikesService extends BaseService
{
	private LikeServiceInterface $likeService;

	public function __construct(LikeServiceInterface $likeService)
	{
		$this->likeService = $likeService;
	}

	protected function appendLikeData(array $items, string $itemIdName) : array
	{
		return array_map(function ($item) use ($itemIdName): array {
			$item['likesInfo'] = $this->likeService->buildLikeData(
				$item[LikeEntity::IDENTIFIER . LikeEntity::TYPE],
				$item[$itemIdName],
				$item[LikeEntity::IDENTIFIER . LikeEntity::ID_MEMBER],
			);

			return $item;
		}, $items);
	}
}
