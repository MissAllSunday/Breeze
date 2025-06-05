<?php

declare(strict_types=1);

namespace Breeze\Event;

use Breeze\Breeze;
use Breeze\Entity\AlertHandledEntity;
use Breeze\Event\Status\StatusCreatedHandler;
use Breeze\Util\Validate\DataNotFoundException;

class HandlerServiceProvider
{
	public function __construct(
	) {}

	/**
	 * @throws DataNotFoundException
	 */
	public function getHandler(AlertHandledEntity $alertHandledEntity): EventHandlerInterface
	{
		$handlerClass = $this->getHandlerClass($alertHandledEntity);

		return new $handlerClass($alertHandledEntity);
	}

	protected function buildHandlerName(AlertHandledEntity $alertHandledEntity): string
	{
		return ucfirst(str_replace(Breeze::PATTERN, '', $alertHandledEntity->getContentType())) .
			ucfirst(str_replace(Breeze::PATTERN, '', $alertHandledEntity->getContentAction()));
	}

	/**
	 * @throws DataNotFoundException
	 */
	protected function getHandlerClass(AlertHandledEntity $alertHandledEntity): string
	{
		return match ($this->buildHandlerName($alertHandledEntity)) {
			'StatusCreated' => StatusCreatedHandler::class,
			default => throw new DataNotFoundException('Handler not found'),
		};
	}
}
