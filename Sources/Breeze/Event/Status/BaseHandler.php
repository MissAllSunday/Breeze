<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

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
}
