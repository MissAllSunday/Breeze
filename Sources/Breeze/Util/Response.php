<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;

class Response
{
	use RequestTrait;
	use TextTrait;

	public const string CONTENT_TYPE = 'content-type: application/json';
	public const string ERROR_TYPE = 'error';
	public const string INFO_TYPE = 'info';
	public const string SUCCESS_TYPE = 'success';
	public const string DEFAULT_ERROR_KEY = self::ERROR_TYPE . '_server';
	public const int OK = 200;
	public const int CREATED = 201;
	public const int ACCEPTED = 202;
	public const int NO_CONTENT = 204;
	public const int NOT_FOUND = 404;
	public const int BAD_REQUEST = 400;
	public const int UNAUTHORIZED = 401;
	public const int METHOD_NOT_ALLOWED = 405;
	public const int NOT_ACCEPTABLE = 406;

	public const array MESSAGE_TYPES = [
		self::ERROR_TYPE,
		self::INFO_TYPE,
		self::SUCCESS_TYPE,
	];

	protected array $response = [
		'message' => '',
		'content' => [],
	];

	public function success(string $message = '', array $content = [], int $responseCode = self::OK): void
	{
		$this->print(array_merge($this->response, [
			'message' => $this->getText(self::SUCCESS_TYPE . '_' . $message),
			'content' => $content,
		]), $responseCode);
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

	public function redirect(string $uri): void
	{
		redirectexit($uri);
	}
}
