<?php

declare(strict_types=1);


namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Enums\PermissionsEnum;
use Breeze\Service\CommentServiceInterface;
use Breeze\Service\LikeServiceInterface;
use Breeze\Service\PermissionsServiceInterface;
use Breeze\Service\StatusServiceInterface;
use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Form\SettingsBuilderInterface;

class AdminService implements AdminServiceInterface
{
	use TextTrait;
	use RequestTrait;

	protected array $configVars = [];

	public function __construct(
		protected SettingsBuilderInterface $settingsBuilder,
		protected StatusServiceInterface $statusService,
		protected CommentServiceInterface $commentService,
		protected LikeServiceInterface $likeService
	) {
	}

	public function init(array $subActions): void
	{
		$context = $this->global('context');
		$scriptUrl = $this->global(Breeze::SCRIPT_URL);

		$this->requireOnce('ManageSettings');
		$this->requireOnce('ManageServer');

		$this->setLanguage(Breeze::NAME . self::IDENTIFIER);
		$this->setTemplate(Breeze::NAME . self::IDENTIFIER);

		loadGeneralSettingParameters(array_combine($subActions, $subActions), 'main');

		$tabs = [];

		foreach ($subActions as $subActionName) {
			$tabs[$subActionName] = [
				'url' => $scriptUrl . '?' . AdminServiceInterface::POST_URL . $subActionName,
				'description' => $this->getText('breezeAdmin_' . $subActionName . '_description'),
				'label' => $this->getText('breezeAdmin_' . $subActionName . '_title'),
			];
		}

		$context[$context['admin_menu_name']]['tab_data']['tabs'] = $tabs;

		$this->setGlobal('context', $context);
	}

	public function defaultSubActionContent(
		string $subActionName,
		array  $templateParams = [],
		string $smfTemplate = ''
	): void {
		if ($subActionName === '' || $subActionName === '0') {
			return;
		}

		$context = $this->global('context');
		$scriptUrl = $this->global(Breeze::SCRIPT_URL);

		$context['post_url'] = $scriptUrl . '?' .
			AdminServiceInterface::POST_URL . $subActionName . ';' .
			$context['session_var'] . '=' . $context['session_id'] . ';save';

		if (!isset($context[Breeze::NAME])) {
			$context[Breeze::NAME] = [];
		}

		if ($templateParams !== []) {
			$context = array_merge($context, $templateParams);
		}

		$context['page_title'] = $this->getText($this->getActionName() . '_' . $subActionName . '_title');
		$context['sub_template'] = $smfTemplate === '' || $smfTemplate === '0' ?
			$subActionName : ($smfTemplate);

		$context[$context['admin_menu_name']]['tab_data'] += [
			'title' => $context['page_title'],
			'description' => $this->getText($this->getActionName() . '_' . $subActionName . '_description'),
		];

		$this->setGlobal('context', $context);
	}

	public function configVars(bool $save = false): void
	{
		$this->requireOnce('ManageServer');

		$this->configVars = $this->settingsBuilder->getConfigVarsSettings();

		array_unshift($this->configVars, [
			'title',
			Breeze::PATTERN . self::AREA . '_settings_title',
		]);

		if ($save) {
			$this->saveConfigVars();
		}

		prepareDBSettingContext($this->configVars);
	}

	public function permissionsConfigVars(bool $save = false): void
	{
		$this->setLanguage(Breeze::NAME . PermissionsServiceInterface::IDENTIFIER);

		$this->configVars = [
			['title', Breeze::PATTERN . self::AREA . '_permissions_title'],
		];

		foreach (PermissionsEnum::ALL_PERMISSIONS as $permission) {
			$this->configVars[] = [
				'permissions',
				'breeze_' . $permission,
				0,
				$this->getSmfText('permissionname_breeze_' . $permission),
			];
		}

		if ($save) {
			$this->saveConfigVars();
		}

		prepareDBSettingContext($this->configVars);
	}

	public function maintenance(bool $fix = false): void
	{
		$context = $this->global('context');
		$scriptUrl = $this->global(Breeze::SCRIPT_URL);

		$context['post_url'] = $scriptUrl . '?' .
			AdminServiceInterface::POST_URL . 'maintenance;' .
			$context['session_var'] . '=' . $context['session_id'] . ';fix';

		if ($fix) {
			checkSession();

			$fixType = $this->getRequest('type', 'all');

			if ($fixType === 'comments' || $fixType === 'all') {
				$this->commentService->deleteOrphans();
				$this->statusService->recountComments();
			}

			if ($fixType === 'likes' || $fixType === 'all') {
				$this->likeService->deleteOrphans();
				$this->statusService->recountLikes();
				$this->commentService->recountLikes();
			}
		}

		$orphanComments = $this->commentService->countOrphans();
		$orphanLikes = $this->likeService->countOrphans();

		$context[Breeze::NAME]['maintenance_stats'] = [
			'orphan_comments' => $orphanComments,
			'orphan_likes' => $orphanLikes,
		];

		$this->setGlobal('context', $context);
	}

	protected function saveConfigVars(): void
	{
		checkSession();
		saveDBSettings($this->configVars);
	}

	public function getActionName(): string
	{
		return self::AREA;
	}

	public function loadComponents(array $components = []): void
	{
	}
}
