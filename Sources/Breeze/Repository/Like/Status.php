<?php

declare(strict_types=1);


namespace Breeze\Repository\Like;

class Status extends Base
{
	public function likesUpdate($smfLikesObject): void
	{
		$type = $smfLikesObject->get('type');
		$content = $smfLikesObject->get('content');
		$extra = $smfLikesObject->get('extra');
		$numLikes = $smfLikesObject->get('numLikes');

		// Try and get the user who posted this content.
		$originalAuthor = 0;
		$originalAuthorData = [];
		$row = $this->likeTypes[$type] . '_id';
		$authorColumn = 'poster_id';

		// With the given values, try to fetch the data of the liked content.
		$originalAuthorData = $this['query']->getSingleValue($this->likeTypes[$type], $row, $content);

		if (!empty($originalAuthorData[$authorColumn]))
			$originalAuthor = $originalAuthorData[$authorColumn];

		// Get the userdata.
		$user = $object->get('user');

		// Get the user's options.
		$uOptions = $this['query']->getUserSettings($user['id']);

		// Insert an inner alert if the user wants to and if the data still is there...
		if (!empty($uOptions['alert_like']) && !empty($originalAuthorData))
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
