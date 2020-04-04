<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Service\AdminService;
use Breeze\Service\RequestService;
use Breeze\Service\ServiceInterface;
use Breeze\Traits\PersistenceTrait;

class AdminController extends BaseController implements ControllerInterface
{
	use PersistenceTrait;

	public const SUB_ACTIONS = [
		'main',
		'settings',
		'permissions',
		'donate',
		'moodList',
	];

	/**
	 * @var ServiceInterface
	 */
	protected $moodService;

	public function __construct(
		RequestService $request,
		ServiceInterface $service,
		ServiceInterface $moodService
	)
	{
		$this->moodService = $moodService;

		parent::__construct($request, $service);
	}

	public function dispatch(): void
	{
		$this->service->initSettingsPage($this->getSubActions());

		$this->subActionCall();
	}

	public function main(): void
	{
		$this->render(__FUNCTION__, [
			Breeze::NAME => [
				'credits' => Breeze::credits(),
				'version' => Breeze::VERSION,
			],
		]);
	}

	public function settings(): void
	{
		$this->render(__FUNCTION__, [], 'show_settings');

		$saving = $this->request->isSet('save');

		$this->service->configVars($saving);

		if ($saving)
			$this->service->redirect(AdminService::POST_URL . __FUNCTION__);
	}

	public function permissions(): void
	{
		$this->render(__FUNCTION__, [], 'show_settings');

		$saving = $this->request->isSet('save');

		$this->service->permissionsConfigVars($saving);

		if ($saving)
			$this->service->redirect(AdminService::POST_URL . __FUNCTION__);
	}

	public function cover(): void
	{
		$this->render(__FUNCTION__, [], 'show_settings');
		$saving = $this->request->isSet('save');

		$coverImageTypesName = Breeze::NAME . '_cover_image_types';
		$coverImageTypesValue = $this->request->get($coverImageTypesName, 'alpha');

		if ($saving && !empty($coverImageTypesValue))
			$this->request->setPost($coverImageTypesName, $coverImageTypesValue);

		$this->service->coverConfigVars($saving);

		if ($saving)
			$this->service->redirect(AdminService::POST_URL . __FUNCTION__);
	}

	public function donate(): void
	{
		$this->render(__FUNCTION__);
	}

	public function moodList(): void
	{
		$this->service->isEnableFeature('mood', __FUNCTION__ . 'general');

		$this->render(__FUNCTION__, [
			Breeze::NAME => [
				'notice' => $this->getMessage(),
				'formId' => __FUNCTION__,
			],
		]);

		$start = $this->request->get('start') ? : 0;
		$this->moodService->createMoodList([
			'id' => __FUNCTION__,
			'base_href' => $this->service->global('scripturl') .
				'?' . AdminService::POST_URL . __FUNCTION__,
		], $start);

		$toDeleteMoodIds = $this->request->get('checked_icons');

		if ($this->request->isSet('delete') &&
			!empty($toDeleteMoodIds))
		{
			$this->moodService->deleteMoods($toDeleteMoodIds);
			$this->service->redirect(AdminService::POST_URL . __FUNCTION__);
		}
	}

	public function render(string $subTemplate, array $params = [], string $smfTemplate = ''): void
	{
		$this->service->setSubActionContent($subTemplate, $params, $smfTemplate);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
