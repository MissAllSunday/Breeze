<?php

declare(strict_types=1);


namespace Breeze\Service\Actions;

interface AdminServiceInterface extends ActionsServiceInterface
{
	public const IDENTIFIER = 'Admin';
	public const AREA = 'breezeAdmin';
	public const POST_URL = 'action=admin;area=breezeAdmin;sa=';

	public function configVars(bool $save = false): void;

	public function permissionsConfigVars(bool $save = false): void;

	public function saveConfigVars(): void;

	public function isEnableFeature(string $featureName = '', string $redirectUrl = ''): bool;

	public function loadComponents(array $components = []): void;
}
