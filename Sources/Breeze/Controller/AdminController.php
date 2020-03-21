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
		'general',
		'settings',
		'permissions',
		'cover',
		'donate',
		'moodList',
		'moodEdit',
	];

	/**
	 * @var ServiceInterface
	 */
	protected $moodService;
	
	/**
	 * @var ServiceInterface
	 */
	private $formService;

	public function __construct(
		RequestService $request,
		ServiceInterface $service,
		ServiceInterface $moodService,
		ServiceInterface $formService
	)
	{
		$this->moodService = $moodService;
		$this->formService = $formService;

		parent::__construct($request, $service);
	}

	public function dispatch(): void
	{
		$this->service->initSettingsPage($this->getSubActions());

		$this->subActionCall();
	}

	public function general(): void
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

	public function moodEdit(): void
	{
		$this->service->isEnableFeature('mood', __FUNCTION__ . 'general');

		$mood = [];
		$moodId = 0;

		if ($this->request->isSet('moodId'))
		{
			$moodId = $this->request->get('moodId');
			$mood = $this->moodService->getMoodById($moodId);
		}

		$this->formService->setOptions([
			'name' => 'mood',
			'url' => $this->service->global('scripturl') .
				'?action=admin;area=' . AdminService::AREA . ';sa=' . __FUNCTION__ . ';save' .
				(!empty($moodId) ? (';moodId=' . $moodId) : ''),
		]);

		$this->formService->setTextPrefix('mood_');

		$this->formService->addText([
			'name' => 'emoji',
			'value' => !empty($mood['emoji']) ? $mood['emoji'] : '',
			'size' => 15,
			'maxlength' => 15,
		]);

		$this->formService->addTextArea([
			'name' => 'description',
			'value' => !empty($mood['description']) ? $mood['description'] : '',
			'size' => ['rows' => 10, 'cols' => 50, 'maxLength' => 1024]
		]);

		$this->formService->addCheckBox([
			'name' => 'enable',
			'value' => !empty($mood['enable']) ? true : false
		]);

		$this->formService->addSessionField();

		$this->formService->addHr();
		$this->formService->addButton(['name' => 'submit']);

		$this->render(__FUNCTION__, [
			Breeze::NAME => [
				'notice' => $this->getMessage(),
				'mood' => $mood,
				'form' => $this->formService->display(),
			],
		]);

		if (!$this->request->isSet('save'))
			return;

		$this->moodService->saveMood($this->request->get('mood'), $moodId);
		$this->service->redirect(AdminService::POST_URL . __FUNCTION__);
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
