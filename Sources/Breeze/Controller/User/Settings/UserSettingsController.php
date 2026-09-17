<?php

declare(strict_types=1);

namespace Breeze\Controller\User\Settings;

use Breeze\Breeze;
use Breeze\Controller\BaseController;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Exceptions\ValidateException;
use Breeze\Repository\User\SettingsRepositoryInterface;
use Breeze\Service\SecurityServiceInterface;
use Breeze\Traits\PermissionsTrait;
use Breeze\Util\Error;
use Breeze\Util\Form\UserSettingsBuilderInterface;
use Breeze\Util\ResponseInterface;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;

class UserSettingsController extends BaseController
{
	use PermissionsTrait;

	public const string ACTION = 'profile';
	public const string AREA = 'breezeSettings';
	public const string TEMPLATE = 'UserSettings';
	public const string URL = '?action=profile;area=breezeSettings';
	public const string ACTION_MAIN = 'main';
	public const string ACTION_SAVE = 'save';

	public const array SUB_ACTIONS = [
		self::ACTION_MAIN,
		self::ACTION_SAVE,
	];

	protected string $subAction;

	public function __construct(
		protected SettingsRepositoryInterface $userRepository,
		protected ResponseInterface $response,
		protected UserSettingsBuilderInterface $userSettingsBuilder,
		protected SecurityServiceInterface $security,
		protected ValidateActionsInterface $validateActions
	) {}

	public function dispatch(): void
	{
		$this->isNotGuest($this->getText('error_no_access'));

		if (!$this->isEnable(SettingsEntity::MASTER)) {
			Error::show('no_valid_action');
		}

		$this->setLanguage(Breeze::NAME);
		$this->setTemplate(Breeze::NAME . self::TEMPLATE);

		$this->subActionCall();
	}

	public function main(): void
	{
		$scriptUrl = $this->global(Breeze::SCRIPT_URL);
		$userId = $this->getRequest('u', 0);

		$this->assertCanManage($userId);

		$this->security->createToken(self::AREA);

		$this->userSettingsBuilder->setForm([
			'name' => UserSettingsEntity::IDENTIFIER,
			'url' => $scriptUrl . self::URL . ';u=' . $userId . ';sa=' . self::ACTION_SAVE,
			'token' => self::AREA,
		], $this->userRepository->getById($userId)->toArray());

		$this->render(__FUNCTION__, [
			'form' => $this->userSettingsBuilder->display(),
			'msg' => $this->getPersistenceMessage(),
		]);
	}

	public function save(): void
	{
		$this->security->validateToken(self::AREA);

		$scriptUrl = $this->global(Breeze::SCRIPT_URL);
		$userId = (int) $this->getRequest('u', 0);
		$requestedSettings = $this->getRequest('user_settings');

		// Authorization first: never validate or persist on behalf of a member
		$this->assertCanManage($userId);

		$userSettings = is_array($requestedSettings) ? $requestedSettings : [];

		$this->validateActions->setUp($userSettings, self::ACTION_SAVE);

		try {
			$this->validateActions->isValid();
		} catch (ValidateException $validateException) {
			Error::show($validateException->getMessage());
		}

		$this->userRepository->insert(
			$userSettings,
			$userId
		);

		$this->setPersistenceMessage($this->getText('info_updated_settings'));
		$this->response->redirect($scriptUrl . self::URL . ';u=' .
			$userId . ';sa=' . self::ACTION_MAIN);
	}

	/**
	 * Authorization gate for both sub-actions.
	 *
	 * A member may only read or write their own Breeze settings; `admin_forum`
	 * is the sole fallback, so forum administrators can manage another
	 * member's options.
	 *
	 * @throws \Error via Error::show() when the caller is neither the profile
	 *                owner nor a forum administrator.
	 */
	protected function assertCanManage(int $userId): void
	{
		$currentUserInfo = $this->global('user_info');
		$currentUserId = (int) ($currentUserInfo['id'] ?? 0);

		if ($userId === $currentUserId && $currentUserId !== 0) {
			return;
		}

		if ($this->isAllowedTo(PermissionsEnum::ADMIN_FORUM)) {
			return;
		}

		Error::show('no_access');
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_MAIN;
	}

	public function getActionName(): string
	{
		return self::ACTION_MAIN;
	}
}
