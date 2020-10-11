<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Controller\BaseController;
use Breeze\Traits\TextTrait;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

abstract class ApiBaseController extends BaseController
{
	use TextTrait;

	protected ValidateGatewayInterface $gateway;

	protected string $subAction;

	public function __construct(ValidateGatewayInterface $gateway)
	{
		$this->gateway = $gateway;
	}

	public function dispatch(): void
	{
		$this->subAction = $this->getRequest('sa', $this->getMainAction());
		$this->gateway->setData();

		$validator = $this->setValidator();

		$this->gateway->setValidator($validator);

		if (!$this->gateway->isValid())
			$this->print($this->gateway->response());

		$this->subActionCall();
	}

	public function print(array $responseData): void
	{
		$smcFunc = $this->global('smcFunc');

		smf_serverResponse($smcFunc['Json::encode']($responseData));
	}

	public abstract function setValidator(): ValidateDataInterface;
}
