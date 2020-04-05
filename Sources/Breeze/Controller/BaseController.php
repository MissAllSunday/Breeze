<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Service\Request;
use Breeze\Service\ServiceInterface;

abstract class BaseController implements ControllerInterface
{
	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var ServiceInterface
	 */
	protected $service;

	public function __construct(Request $request, ServiceInterface $service)
	{
		$this->request = $request;
		$this->service = $service;
	}

	public function subActionCall(): void
	{
		$subActions = $this->getSubActions();
		$subAction = $this->request->get('sa');

		if (in_array($subAction, $subActions))
			$this->$subAction();

		else
			$this->main();
	}

	public abstract function getSubActions(): array;
}
