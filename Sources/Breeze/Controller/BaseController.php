<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Service\Request;

abstract class BaseController implements ControllerInterface
{
	/**
	 * @var Request
	 */
	protected $request;

	public function subActionCall()
	{
		$subActions = array_keys($this->getSubActions());
		$subAction = $this->request->get('sa');

		if (in_array($subAction, $subActions))
			$this->$subAction();

		else
			$this->general();
	}
}
