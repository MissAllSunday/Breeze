<?php

declare(strict_types=1);


namespace Breeze\Repository\Like;

use Breeze\Model\Like as LikeModel;
use Breeze\Model\Log as LogModel;
use Breeze\Model\Notification as NotificationModel;
use Breeze\Model\Status as StatusModel;
use Breeze\Model\User as UserModel;


class Status extends Base
{
	/**
	 * @var StatusModel
	 */
	protected $statusModel;

	public function __construct(
	    LikeModel $likeModel,
	    UserModel $userModel,
	    NotificationModel $notificationModel,
	    StatusModel $statusModel,
	    LogModel $logModel
	)
	{
		parent::__construct($likeModel, $userModel, $notificationModel, $logModel);

		$this->statusModel = $statusModel;
	}

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
