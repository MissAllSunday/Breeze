<?php

declare(strict_types=1);

// Handle Breeze notifications on our own
class Breeze_Notify_Background extends SMF_BackgroundTask // @phpstan-ignore-line
{
	public function execute()
	{
		$app = new \Breeze\Breeze();

		$notifications = $app->getContainer()->get(\Breeze\Service\NotificationService::class);

		$notifications->handle($this->_details); // @phpstan-ignore-line

		unset($app);

		return true;
	}
}
