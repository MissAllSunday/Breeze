<?php

declare(strict_types=1);

namespace Breeze\Service;

interface SecurityServiceInterface
{
	/**
	 * @param string $tokenName The action to validate the token for
	 * @param string $type The type of request (get, request, or post)
	 * @param bool $reset Whether to reset the token and display an error if validation fails
	 * @return bool returns whether the validation was successful
	 */
	public function validateToken(string $tokenName, string $type = 'post', bool $reset = true): bool;

	/**
	 * @param string $tokenName The action to create the token for
	 * @param string $type The type of token ('post', 'get' or 'request')
	 * @return array An array containing the name of the token var and the actual token
	 */
	public function createToken(string $tokenName, string $type = 'post'): array;

	public function checkSession(): void;

	public function urlWithSession(string $url): string;
}
