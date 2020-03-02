<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Service\RequestService;
use Breeze\Service\ServiceInterface;

abstract class BaseController implements ControllerInterface
{
	/**
	 * @var RequestService
	 */
	protected $request;

	/**
	 * @var ServiceInterface
	 */
	protected $service;

	public function __construct(RequestService $request, ServiceInterface $service)
	{
		$this->request = $request;
		$this->service = $service;
	}

	public function subActionCall(): void
	{
		$subActions = array_keys($this->getSubActions());
		$subAction = $this->request->get('sa');

		if (in_array($subAction, $subActions))
			$this->$subAction();

		else
			$this->general();
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
