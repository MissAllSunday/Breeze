<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Entity\EntityInterface;
use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;

class Response
{
	use RequestTrait;
	use TextTrait;

	protected array $response = [
		'message' => '',
		'content' => [],
	];

	public function success(
		string $message = '',
		EntityInterface | array $content = [],
		int $responseCode = self::OK
	): void
	{
		$this->print(array_merge($this->response, [
			'message' => $this->getText(self::SUCCESS_TYPE . '_' . $message),
			'content' => $content,
		]), $responseCode);
	}

	public function print(array $responseData, int $responseCode = 200, string $type = ''): void
	{
		$this->setGlobal('db_show_debug', false);

		$responseData = $this->appendToken($responseData);

		ob_end_clean();

		if (!$this->global('enableCompressedOutput')) {
			@ob_start('ob_gzhandler');
		} else {
			ob_start();
		}

		header($type === '' || $type === '0' ? self::CONTENT_TYPE : $type);
		http_response_code($responseCode);

		echo Json::encode($responseData);

		exit(obExit(false));
	}

	public function error(string $message = '', int $responseCode = self::NOT_FOUND): void
	{
		$this->print(array_merge($this->response, [
			'message' => $message === '' || $message === '0' ? $message : sprintf(
				$this->getText(self::DEFAULT_ERROR_KEY),
				$this->getText(self::ERROR_TYPE . '_' . $message)
			),
		]), $responseCode);
	}

	protected function appendToken(array $responseData): array
	{
		$token = createToken(self::CSRF_TOKEN_ACTION, 'get');

		$responseData['token'] = [
			'var' => $token[self::CSRF_TOKEN_ACTION . '_token_var'],
			'value' => $token[self::CSRF_TOKEN_ACTION . '_token'],
		];

		return $responseData;
	}

	public function redirect(string $uri): void
	{
		redirectexit($uri);
	}
}
