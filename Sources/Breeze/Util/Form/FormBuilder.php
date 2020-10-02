<?php

declare(strict_types=1);


namespace Breeze\Util\Form;

use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Service\UserService;
use Breeze\Service\UserServiceInterface;
use Breeze\Traits\TextTrait;
use Breeze\Util\Folder;
use Breeze\Util\Form\Types\ValueFormatter;
use Breeze\Util\Form\Types\ValueFormatterInterface;

class FormBuilder
{
	use TextTrait;

	private UserServiceInterface $userSettingsService;

	private int $userId;

	private array $userSettingsColumns;

	public function __construct(UserServiceInterface $userSettingsService, int $userId)
	{
		$this->userSettingsService = $userSettingsService;
		$this->userId = $userId;
		$this->userSettingsColumns = UserSettingsEntity::getColumns();
	}

	public function setForm(array $formOptions): void
	{
		$userSettingValues = $this->userSettingsService->getUserSettings($this->userId);


	}
}
