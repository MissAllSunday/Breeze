<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Entity\EntityInterface;

interface ResponseInterface
{
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

	public const string CSRF_TOKEN_ACTION = 'breeze';

	public function success(
		string $message = '',
		EntityInterface | array $content = [],
		int $responseCode = self::OK
	): void;

	public function print(array $responseData, int $responseCode = 200, string $type = ''): void;

	public function error(string $message = '', int $responseCode = self::NOT_FOUND): void;

	public function redirect(string $uri): void;
}
