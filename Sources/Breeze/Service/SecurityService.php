<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Traits\SettingsTrait;

class SecurityService implements SecurityServiceInterface
{
	use SettingsTrait;

	public function validateToken(string $tokenName, string $type = 'post', bool $reset = true): bool
	{
		return validateToken($tokenName, $type, $reset);
	}

	public function createToken(string $tokenName, string $type = 'post'): array
	{
		return createToken($tokenName, $type);
	}

	public function checkSession(): void
	{
		checkSession();
	}

	public function urlWithSession(string $url): string
	{
		$context = $this->global('context');

		return $url . ';' .
			$context['session_var'] . '=' . $context['session_id'];
	}
}
