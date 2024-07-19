<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\NotificationRepositoryInterface;

class NotificationService implements NotificationServiceInterface
{
	public function __construct(private NotificationRepositoryInterface $notificationRepository)
	{
	}

	public function create(array $data): void
	{

	}
}
