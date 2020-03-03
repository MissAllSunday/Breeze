<?php

namespace Breeze\Core\DependencyInjection;

use Breeze\Entity\AlertEntity;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LogEntity;
use Breeze\Entity\MemberEntity;
use Breeze\Entity\MentionEntity;
use Breeze\Entity\MoodEntity;
use Breeze\Entity\NotificationEntity;
use Breeze\Entity\OptionsEntity;
use Breeze\Entity\StatusEntity;
use League\Container\ServiceProvider\AbstractServiceProvider;

class EntityProvider extends AbstractServiceProvider
{
	protected $provides = [
		AlertEntity::class,
		CommentEntity::class,
		LikeEntity::class,
		LogEntity::class,
		MemberEntity::class,
		MentionEntity::class,
		MoodEntity::class,
		NotificationEntity::class,
		OptionsEntity::class,
		StatusEntity::class,
	];

	/**
	 *
	 * @return void
	 */
	public function register()
	{
		foreach ($this->provides as $entity)
			$this->getContainer()->add($entity);
	}
}