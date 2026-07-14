<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Traits\SettingsTrait;

/**
 * @codeCoverageIgnore
 */
class ResponseEmitter implements ResponseEmitterInterface
{
	use SettingsTrait;

	public function emit(array $responseData, int $responseCode, string $contentType): void
	{
		$this->setGlobal('db_show_debug', false);

		ob_end_clean();

		if (!$this->global('enableCompressedOutput')) {
			@ob_start('ob_gzhandler');
		} else {
			ob_start();
		}

		header($contentType);
		http_response_code($responseCode);

		echo Json::encode($responseData);

		exit(obExit(false));
	}

	public function redirectExit(string $setLocation = '', bool $refresh = false, bool $permanent = false): void
	{
		redirectexit($setLocation, $refresh, $permanent);
	}
}
