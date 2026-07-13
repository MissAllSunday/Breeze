<?php

declare(strict_types=1);

namespace Breeze\Service;

interface SecurityServiceInterface
{
	public function validateToken(string $tokenName): void;

	/**
	 * @param string $tokenName The action to create the token for
	 * @param string $type The type of token ('post', 'get' or 'request')
	 * @return array An array containing the name of the token var and the actual token
	 */
	public function createToken(string $tokenName, string $type = 'post'): array;

	public function checkSession(): void;

	public function urlWithSession(string $url): string;
}
