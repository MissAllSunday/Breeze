<?php

declare(strict_types=1);

namespace Breeze\Event\Buddy;

use Breeze\Entity\AlertEntity;
use Breeze\Repository\AlertRepository;
use Breeze\Traits\TextTrait;

class BaseHandler
{
	use TextTrait;

	protected array $extra = [];

	public function __construct(
		protected AlertEntity $alertEntity,
		protected AlertRepository $alertRepository
	) {}

	protected function setPeopleIcon(): void
	{
		$this->alertEntity->setIcon('<span class="alert_icon main_icons people"></span>');
	}
}
