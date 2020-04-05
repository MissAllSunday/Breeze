<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\SettingsBaseEntity;
use Breeze\Util\Form\ValueFormatter;
use Breeze\Util\Form\ValueFormatterInterface;

interface FormServiceInterface
{
	public function getConfigVarsSettings(): array;

	public function getCoverConfigVarsSettings(): array;
}
