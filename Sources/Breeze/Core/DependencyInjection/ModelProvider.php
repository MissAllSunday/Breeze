<?php

declare(strict_types=1);


namespace Breeze\Core\DependencyInjection;

use Breeze\Database\Client;
use Breeze\Model\AlertModel;
use Breeze\Model\CommentModel;
use Breeze\Model\LikeModel;
use Breeze\Model\LogModel;
use Breeze\Model\MentionModel;
use Breeze\Model\MoodModel;
use Breeze\Model\NotificationModel;
use Breeze\Model\StatusModel;
use Breeze\Model\UserModel;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ModelProvider extends AbstractServiceProvider
{
	protected $provides = [
	    AlertModel::class,
	    CommentModel::class,
	    LikeModel::class,
	    LogModel::class,
	    MentionModel::class,
	    MoodModel::class,
	    NotificationModel::class,
	    StatusModel::class,
	    UserModel::class,
	];

	public function register(): void
	{
		foreach ($this->provides as $entity)
			$this->getContainer()->add($entity)->addArgument(Client::class);
	}
}
