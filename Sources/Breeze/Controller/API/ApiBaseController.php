<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Breeze;
use Breeze\Exceptions\ValidateException;
use Breeze\Service\SecurityServiceInterface;
use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\ResponseInterface;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

abstract class ApiBaseController
{
	use RequestTrait;
	use TextTrait;

	protected string $subAction;

	protected string $action;

	protected array $data = [];

	protected ValidateDataInterface $validator;

	public function __construct(
		protected ValidateActionsInterface $validateActions,
		protected ResponseInterface $response,
		protected SecurityServiceInterface $security
	) {
		$this->subAction = $this->getRequest('sa', '');
		$this->action = $this->getRequest('action', '');
	}

	public function subActionCall(): void
	{
		$subActions = $this->getSubActions();

		if ($this->subAction !== '' && $this->subAction !== '0' && in_array($this->subAction, $subActions, true)) {
			$this->{$this->subAction}();
		} else {
			$this->response->print([], ResponseInterface::NOT_FOUND);
		}
	}

	public function subActionCheck(): bool
	{
		return ($this->subAction !== '' && $this->subAction !== '0' && !in_array($this->subAction, $this->getSubActions(), true));
	}

	public function dispatch(): void
	{
		if (!array_key_exists($this->action, Breeze::ACTIONS)) {
			return;
		}

		$this->data = $this->getData();
		$this->validateActions->setUp($this->data, $this->subAction);

		if ($this->subActionCheck()) {
			$this->response->print([], ResponseInterface::NOT_FOUND);
		}

		if ($this->isMutatingAction()) {
			$this->validateCsrfToken();
		}

		try {
			$this->validateActions->isValid();
			$this->subActionCall();
		} catch (ValidateException $validateException) {
			$this->response->error($validateException->getMessage(), $validateException->getResponseCode());
		}
	}

	protected function isMutatingAction(): bool
	{
		return in_array($this->subAction, $this->getMutatingActions(), true);
	}

	protected function validateCsrfToken(): void
	{
		$this->security->validateToken(ResponseInterface::CSRF_TOKEN_ACTION, 'get');
	}

	abstract public function getSubActions(): array;

	abstract public function getMutatingActions(): array;
}
