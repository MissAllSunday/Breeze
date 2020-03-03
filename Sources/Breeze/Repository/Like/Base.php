<?php

declare(strict_types=1);


namespace Breeze\Repository\Like;

use Breeze\Model\LikeModel as LikeModel;
use Breeze\Model\LogModel as LogModel;
use Breeze\Model\NotificationModel as NotificationModel;
use Breeze\Model\UserModel as UserModel;

abstract class Base
{
	public const LIKE_TYPE_STATUS = 'breSta';
	public const LIKE_TYPE_COMMENT = 'breCom';

	/**
	 * @var LikeModel
	 */
	protected $likeModel;

	/**
	 * @var UserModel
	 */
	protected $userModel;

	/**
	 * @var NotificationModel
	 */
	protected $notificationModel;

	/**
	 * @var LogModel
	 */
	private $logModel;

	public function __construct(LikeModel $likeModel, UserModel $userModel, NotificationModel $notificationModel, LogModel $logModel)
	{
		$this->likeModel = $likeModel;
		$this->userModel = $userModel;
		$this->notificationModel = $notificationModel;
		$this->logModel = $logModel;
	}

	public function handleLikes($type, $content): array
	{
		if (!in_array($type, self::getAllTypes()))
			return false;

		$row = $this->likeTypes[$type] . '_id';
		$authorColumn = 'poster_id';

		// With the given values, try to find who is the owner of the liked content.
		$data = $this['query']->getSingleValue($this->likeTypes[$type], $row, $content);

		if (!empty($data[$authorColumn]))
			return $data[$authorColumn];

		// Return false if the status/comment is no longer on the DB.

		return false;
	}

	public abstract function getType(): string;

	public static function getAllTypes(): array
	{
		return [
		    self::LIKE_TYPE_STATUS,
		    self::LIKE_TYPE_COMMENT
		];
	}
}
