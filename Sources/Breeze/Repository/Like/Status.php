<?php

declare(strict_types=1);


namespace Breeze\Repository\Like;

use Breeze\Repository\BaseRepository;
use Breeze\Repository\RepositoryInterface;


class Status extends BaseRepository implements RepositoryInterface
{

	public function update($smfLikesObject): void
	{
		$likedType = $smfLikesObject->get('type');
		$statusId = $smfLikesObject->get('content');
		$likedExtraParams = $smfLikesObject->get('extra');
		$likedNumLikes = $smfLikesObject->get('numLikes');
		$likedUserData = $smfLikesObject->get('user');

		$originalLikedData = $this->statusModel->getById([$statusId]);

		if (!empty($originalLikedData[$this->statusModel->getColumnPosterId()]))
			$originalLikedAuthorId = $originalLikedData[$this->statusModel->getColumnPosterId()];

		$likedUserSettings = $this->userModel->getUserSettings($likedUserData['id']);

		if (!empty($likedUserSettings['alert_like']) && !empty($originalLikedData))
			$this['query']->createLog([
				'member' => $user['id'],
				'content_type' => 'like',
				'content_id' => $content,
				'time' => time(),
				'extra' => [
					'contentData' => $originalAuthorData,
					'type' => $this->likeTypes[$type],
					'toLoad' => [$user['id'], $originalAuthor],
				],
			]);

		// Fire up a notification.
		$this['query']->insertNoti([
			'user' => $user['id'],
			'like_type' => $this->likeTypes[$type],
			'content' => $content,
			'numLikes' => $numLikes,
			'extra' => $extra,
			'alreadyLiked' => (bool) $object->get('alreadyLiked'),
			'validLikes' => $object->get('validLikes'),
			'time' => time(),
		], 'like');

		$this['query']->updateLikes($this->likeTypes[$type], $content, $numLikes);
	}

	public function getType(): string
	{
		return self::LIKE_TYPE_STATUS;
	}
}
