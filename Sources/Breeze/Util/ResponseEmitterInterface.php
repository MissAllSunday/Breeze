<?php

declare(strict_types=1);

namespace Breeze\Util;

interface ResponseEmitterInterface
{
	/**
	 * Emits the response headers, formats the data, and terminates the request.
	 */
	public function emit(array $responseData, int $responseCode, string $contentType): void;

	/**
	 * @param string $setLocation The URL to redirect them to
	 * @param bool $refresh Whether to use a meta refresh instead
	 * @param bool $permanent Whether to send a 301 Moved Permanently instead of a 302 Moved Temporarily
	 */
	public function redirectExit(string $setLocation = '', bool $refresh = false, bool $permanent = false): void;
}
