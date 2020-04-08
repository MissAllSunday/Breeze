<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Traits\TextTrait;

interface AdminServiceInterface extends BaseServiceInterface
{
	public const IDENTIFIER = 'Admin';
	public const AREA = 'breezeAdmin';
	public const POST_URL = 'action=admin;area=breezeAdmin;sa=';

	public function configVars(bool $save = false): void;

	public function permissionsConfigVars(bool $save = false): void;

	public function saveConfigVars(): void;

	public function init(array $subActions):void;

	public function defaultSubActionContent(string $subTemplate, array $params, string $smfTemplate);

	public function isEnableFeature(string $featureName = '', string $redirectUrl = ''): bool;
}
