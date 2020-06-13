<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Controller\BaseController;
use Breeze\Traits\TextTrait;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\ValidateGateway;
use Breeze\Util\Validate\ValidateGatewayInterface;

abstract class ApiBaseController extends BaseController
{
	use TextTrait;

	/**
	 * @var ValidateGatewayInterface
	 */
	protected $gateway;

	public function subActionCall(): void
	{
		$subActions = $this->getSubActions();
		$subAction = $this->getRequest('sa', $this->getMainAction());

		try {
			$this->gateway->setValidator((string) $subAction);
		} catch (ValidateDataException $exception) {
			$this->print([
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $this->getText($exception->getMessage())
			]);

			return;
		}

		if (in_array($subAction, $subActions))
			$this->$subAction();

		else
			$this->{$this->getMainAction()}();
	}

	public function print(array $responseData): void
	{
		$smcFunc = $this->global('smcFunc');

		smf_serverResponse($smcFunc['json_encode']($responseData));
	}
}
