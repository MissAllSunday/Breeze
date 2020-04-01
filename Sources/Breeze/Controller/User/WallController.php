<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Breeze;
use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;

class WallController extends BaseController implements ControllerInterface
{
	public const SUB_ACTIONS = [
		'main',
		'status',
		'comment',
	];

	protected $profileOwnerId = 0;
	protected $currentUserInfo = [];

	public function dispatch(): void
	{
		$this->currentUserInfo = $this->service->global('user_info');
		if (!$this->service->enable('master'))
			fatal_lang_error('Breeze_error_no_valid_action', false);

		is_not_guest($this->service->getText('error_no_access'));

		$this->service->setLanguage(Breeze::NAME);
		$this->service->setTemplate(Breeze::NAME);

		$this->profileOwnerId = $this->request->get('u', 0);
		$this->service->setUsersToLoad([$this->profileOwnerId]);

		$this->subActionCall();
	}

	public function main(): void
	{
		$this->render(__FUNCTION__);
	}

	public function render(string $subTemplate, array $params = []): void
	{
		$this->service->setSubActionContent($subTemplate);
		$this->service->loadUsersInfo();
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	protected function isCurrentUserOwner(): bool
	{
		if (!isset($this->currentUserInfo['id']))
			return false;

		return $this->currentUserInfo['id'] === $this->profileOwnerId;
	}
}
