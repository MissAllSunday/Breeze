<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Repository\RepositoryInterface;

interface AdminServiceInterface
{
	public const IDENTIFIER = 'Admin';
	public const AREA = 'breezeAdmin';
	public const POST_URL = 'action=admin;area=breezeAdmin;sa=';

	public function initSettingsPage(array $subActions): void;

	public function configVars(bool $save = false): void;

	public function permissionsConfigVars(bool $save = false): void;

	public function saveConfigVars(): void;

	public function setSubActionContent(
		string $actionName,
		array $templateParams = [],
		string $smfTemplate = ''
	): void;

	public function isEnableFeature(string $featureName = '', string $redirectUrl = ''): bool;
}
