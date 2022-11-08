<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Exceptions\ValidateException;
use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

abstract class ApiBaseController
{
	use RequestTrait;
	use TextTrait;

	protected string $subAction;

	protected array $data = [];

	protected ValidateDataInterface $validator;

	public function __construct(
		protected ValidateActionsInterface $validateActions,
		protected Response $response
	) {
		$this->subAction = $this->getRequest('sa', '');
		$this->data = $this->getData();

		$this->validateActions->setUp($this->data, $this->subAction);
	}

	public function subActionCall(): void
	{
		$subActions = $this->getSubActions();

		if (!empty($this->subAction) && in_array($this->subAction, $subActions)) {
			$this->{$this->subAction}();
		} else {
			$this->response->print([], Response::NOT_FOUND);
		}
	}

	public function subActionCheck(): bool
	{
		return (!empty($this->subAction) && !in_array($this->subAction, $this->getSubActions()));
	}

	public function dispatch(): void
	{
		if ($this->subActionCheck()) {
			$this->response->print([], Response::NOT_FOUND);
		}

		try {
			$this->validateActions->isValid();
			$this->subActionCall();
		} catch (ValidateException $validateException) {
			$this->response->error($validateException->getMessage(), $validateException->getResponseCode());
		}
	}

	abstract public function getSubActions(): array;
}
