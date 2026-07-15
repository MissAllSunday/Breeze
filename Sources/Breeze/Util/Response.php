<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Entity\EntityInterface;
use Breeze\Service\SecurityServiceInterface;
use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;

class Response implements ResponseInterface
{
	use RequestTrait;
	use TextTrait;

	protected array $response = [
		'message' => '',
		'content' => [],
	];

	public function __construct(
		protected SecurityServiceInterface $security,
		protected ResponseEmitterInterface $emitter
	) {}

	public function success(
		string $message = '',
		EntityInterface | array $content = [],
		int $responseCode = ResponseInterface::OK
	): void {
		$payload = array_merge($this->response, [
			'message' => $this->getText(ResponseInterface::SUCCESS_TYPE . '_' . $message),
			'content' => $content,
		]);

		$this->print($payload, $responseCode);
	}

	public function print(array $responseData, int $responseCode = 200, string $type = ''): void
	{
		$responseData = $this->appendToken($responseData);
		$contentType = ($type === '' || $type === '0') ? ResponseInterface::CONTENT_TYPE : $type;

		$this->emitter->emit($responseData, $responseCode, $contentType);
	}

	public function error(string $message = '', int $responseCode = ResponseInterface::NOT_FOUND): void
	{
		$payload = array_merge($this->response, [
			'message' => $message === '' || $message === '0' ? $message : sprintf(
				$this->getText(ResponseInterface::DEFAULT_ERROR_KEY),
				$this->getText(ResponseInterface::ERROR_TYPE . '_' . $message)
			),
		]);

		$this->print($payload, $responseCode);
	}

	public function appendToken(array $responseData): array
	{
		$token = $this->security->createToken(ResponseInterface::CSRF_TOKEN_ACTION, 'get');

		$responseData['token'] = [
			'var' => $token[ResponseInterface::CSRF_TOKEN_ACTION . '_token_var'],
			'value' => $token[ResponseInterface::CSRF_TOKEN_ACTION . '_token'],
		];

		return $responseData;
	}

	public function redirect(string $uri): void
	{
		$this->emitter->redirectExit($uri);
	}
}
