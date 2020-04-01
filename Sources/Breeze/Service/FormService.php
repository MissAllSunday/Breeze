<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\SettingsEntity;

class FormService extends BaseService implements ServiceInterface
{
	public function getConfigVarsSettings(): array
	{
		$allSettings = SettingsEntity::getColumns();


	}
}
