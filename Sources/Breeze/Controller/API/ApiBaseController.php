<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Controller\BaseController;
use Breeze\Traits\TextTrait;

abstract class ApiBaseController extends BaseController
{
	use TextTrait;

	public function print(array $data): void
	{
		$smcFunc = $this->global('smcFunc');

		smf_serverResponse($smcFunc['json_encode']($data));
	}
}
