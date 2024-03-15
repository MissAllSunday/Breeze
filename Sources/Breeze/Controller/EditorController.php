<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Service\EditorServiceInterface;

class EditorController extends BaseController implements ControllerInterface
{

	public const ACTION_MAIN = 'showEditor';

	public const SUB_ACTIONS = [
		self::ACTION_MAIN,
	];

	public function __construct(
		protected EditorServiceInterface $editorService
	) {
	}

	public function showEditor(): void
	{
		$this->editorService->setEditor();

		$this->render(__FUNCTION__);
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
