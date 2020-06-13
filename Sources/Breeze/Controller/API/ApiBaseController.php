<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Controller\BaseController;
use Breeze\Traits\TextTrait;
use Breeze\Util\Validate\ValidateGatewayInterface;

abstract class ApiBaseController extends BaseController
{
	use TextTrait;

	/**
	 * @var ValidateGatewayInterface
	 */
	protected $gateway;

	public function print(array $responseData): void
	{
		$smcFunc = $this->global('smcFunc');

		smf_serverResponse($smcFunc['json_encode']($responseData));
	}
}
