<?php

declare(strict_types=1);

namespace Breeze\Controller\API;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Service\SecurityServiceInterface;
use Breeze\Service\StatusServiceInterface;
use Breeze\Util\ResponseInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\EmptyDataException;
use Breeze\Util\Validate\Validations\ValidateActionsInterface;
use Exception;

class StatusController extends ApiBaseController
{
	public const string ACTION_PROFILE = 'profile';
	public const string ACTION_WALL = 'wall';
	public const string ACTION_DELETE = 'deleteStatus';
	public const string ACTION_POST = 'postStatus';
	public const string ACTION_TOTAL = 'total';
	public const string ACTION_SINGLE = 'single';

	public const array SUB_ACTIONS = [
		self::ACTION_PROFILE,
		self::ACTION_POST,
		self::ACTION_DELETE,
		self::ACTION_WALL,
		self::ACTION_TOTAL,
		self::ACTION_SINGLE,
	];

	public function __construct(
		protected StatusServiceInterface $statusService,
		protected ValidateActionsInterface $validateActions,
		protected ResponseInterface $response,
		protected SecurityServiceInterface $security
	) {
		parent::__construct($validateActions, $response, $security);
	}

	public function profile(): void
	{
		try {
			$cursor = $this->getRequest('cursor', null);

			$wallId = $this->data[StatusEntity::WALL_ID];
			$message = '';

			$statusByProfile = $this->statusService->getByProfile(
				$wallId,
				$cursor
			);

			if ($statusByProfile['data'] === [] && $cursor === null) {
				$currentUserInfo = $this->statusService->currentUserInfo();
				$message = $wallId === $currentUserInfo['id']
					? 'empty_data_own_wall'
					: 'empty_data_other_wall';
			}

			$this->response->success($message, $statusByProfile);
		} catch (Exception $exception) {
			$this->responseWithError($exception);
		}
	}

	public function wall(): void
	{
		try {
			$buddiesStatus = $this->statusService->getByBuddies(
				$this->getRequest('cursor', null)
			);

			$this->response->success('', $buddiesStatus);
		} catch (Exception $exception) {
			$this->responseWithError($exception);
		}
	}

	public function deleteStatus(): void
	{
		try {
			$statusId = $this->data[StatusEntity::ID];

			$this->statusService->deleteById($statusId);
			$this->response->success('deleted_status');
		} catch (InvalidStatusException $invalidStatusException) {
			$this->responseWithError($invalidStatusException, true);
		}
	}

	public function postStatus(): void
	{
		try {
			$statusEntities = $this->statusService->save($this->data);

			$this->response->success(
				'published_status',
				$statusEntities,
				ResponseInterface::CREATED
			);
		} catch (InvalidStatusException $exception) {
			$this->responseWithError($exception, true);
		}
	}

	public function total(): void
	{
		try {
			$wallId = $this->data[StatusEntity::WALL_ID];
			$statusByProfile = $this->statusService->getByProfile(
				$wallId,
				$this->getRequest('cursor', null)
			);

			$this->response->success('', $statusByProfile);
		} catch (Exception $exception) {
			$this->responseWithError($exception);
		}
	}

	public function single(): void
	{
		try {
			$statusId = $this->getRequest('id', 0);

			if (empty($statusId)) {
				$this->response->error('error_no_status', ResponseInterface::BAD_REQUEST);

				return;
			}

			$singleStatus = $this->statusService->getById($statusId);

			$this->response->success('', $singleStatus);
		} catch (DataNotFoundException | EmptyDataException $exception) {
			$this->responseWithError($exception, true);
		}
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMutatingActions(): array
	{
		return [
			self::ACTION_POST,
			self::ACTION_DELETE,
		];
	}

	protected function responseWithError(Exception $exception, bool $useExceptionMessage = false): void
	{
		$errorResponse = $useExceptionMessage ? $exception->getMessage() : 'error_generic';

		$this->logError($exception);
		$this->response->error($errorResponse);
	}
}
