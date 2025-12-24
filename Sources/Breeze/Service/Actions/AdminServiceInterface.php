<?php

declare(strict_types=1);


namespace Breeze\Service\Actions;

interface AdminServiceInterface extends ActionsServiceInterface
{
	public const string IDENTIFIER = 'Admin';
	public const string AREA = 'breezeAdmin';
	public const string POST_URL = 'action=admin;area=breezeAdmin;sa=';

	public function configVars(bool $save = false): void;

	public function permissionsConfigVars(bool $save = false): void;

	public function loadComponents(array $components = []): void;
}
