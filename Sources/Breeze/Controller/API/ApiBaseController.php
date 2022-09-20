<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Controller\BaseController;
use Breeze\Traits\TextTrait;
use Breeze\Util\Json;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

abstract class ApiBaseController extends BaseController
{
	public const DEFAULT_CONTENT_TYPE = 'content-type: application/json';

	use TextTrait;

	protected string $subAction;

	public function __construct(protected ValidateGatewayInterface $gateway)
	{
		$this->subAction = $this->getRequest('sa', '');
	}

	public function subActionCall(): void
	{
		$subActions = $this->getSubActions();

		if (!empty($this->subAction) && in_array($this->subAction, $subActions)) {
			$this->{$this->subAction}();
		} else {
			$this->print([], 404);
		}
	}

	public function subActionCheck(): bool
	{
		$subActions = $this->getSubActions();

		return (!empty($this->subAction) && !in_array($this->subAction, $subActions));
	}

	public function dispatch(): void
	{
		if ($this->subActionCheck()) {
			$this->print([], 404);
		}

		$this->setValidator();
		$this->gateway->setValidator($this->getValidator());

		if (!$this->gateway->isValid()) {
			$this->print($this->gateway->response(), $this->gateway->getStatusCode());
		}

		$this->subActionCall();
	}

	public function print(array $responseData, int $responseCode = 200, string $type = ''): void
	{
		$this->setGlobal('db_show_debug', false);
		ob_end_clean();

		if (!$this->global('enableCompressedOutput')) {
			@ob_start('ob_gzhandler');
		} else {
			ob_start();
		}

		header(!empty($type) ? $type : self::DEFAULT_CONTENT_TYPE);
		http_response_code($responseCode);

		echo Json::encode($responseData);

		exit(obExit(false));
	}

	public function render(string $subTemplate, array $params): void
	{
	}

	abstract public function setValidator(): void;

	abstract public function getValidator(): ValidateDataInterface;
}
