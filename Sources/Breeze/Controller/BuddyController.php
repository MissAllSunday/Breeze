<?php

declare(strict_types=1);


namespace Breeze\Controller;

use Breeze\Service\BuddyServiceInterface;
use Breeze\Util\Error;
use Breeze\Util\Response;

class BuddyController extends BaseController implements ControllerInterface
{
	protected const string ACTION_HANDLE = 'handle';
	protected const string ACTION_CONFIRM = 'confirm';
	protected const string ACTION_DECLINE = 'decline';
	protected const string ACTION_REQUESTS = 'requests';
	protected const array SUB_ACTIONS = [
		self::ACTION_HANDLE,
		self::ACTION_CONFIRM,
		self::ACTION_DECLINE,
		self::ACTION_REQUESTS,
	];

	protected int $userReceivingId;

	public function __construct(
		protected Response $response,
		protected BuddyServiceInterface $buddyService
	)
	{
		$this->userReceivingId = $this->getRequest('u', 0);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_HANDLE;
	}

	public function getActionName(): string
	{
		return self::ACTION_HANDLE;
	}

	public function handle(): void
	{
		$this->checkMutation();

		$currentUserInfo = $this->global('user_info');
		$statuses = $this->buddyService->getBuddyStatusForUsers(
			$currentUserInfo['id'],
			[$this->userReceivingId]
		);
		$status = $statuses[$this->userReceivingId] ?? 'none';

		if ($status === 'confirmed' || in_array($this->userReceivingId, $currentUserInfo['buddies'])) {
			$this->buddyService->removeBuddy($this->userReceivingId, $currentUserInfo);
		} else {
			$this->buddyService->addBuddy($this->userReceivingId, $currentUserInfo);
		}

		$this->response->redirect('action=profile;u=' . $this->userReceivingId);
	}

	public function confirm(): void
	{
		$this->checkMutation();

		$currentUserInfo = $this->global('user_info');
		$this->buddyService->confirmBuddy($this->userReceivingId, $currentUserInfo);

		$this->response->redirect('action=buddy;sa=requests');
	}

	public function decline(): void
	{
		$this->checkMutation();

		$currentUserInfo = $this->global('user_info');
		$this->buddyService->declineBuddyRequest(
			$this->userReceivingId,
			$currentUserInfo['id']
		);

		$this->response->redirect('action=buddy;sa=requests');
	}

	public function requests(): void
	{
		$this->isAllowedTo('profile_extra_own');

		$currentUserInfo = $this->global('user_info');
		$pendingRequests = $this->buddyService->getPendingRequests($currentUserInfo['id']);
		$buddyToken = createToken('buddy', 'get');

		$this->render(self::ACTION_REQUESTS, [
			'pendingRequests' => $pendingRequests,
			'buddyToken' => $buddyToken,
			'scriptUrl' => $this->global('scripturl'),
			'sessionVar' => $this->global('session_var'),
			'sessionId' => $this->global('session_id'),
		]);
	}

	protected function checkMutation(): void
	{
		checkSession('get');
		validateToken('buddy', 'get');

		$this->isAllowedTo('profile_extra_own');

		if ($this->userReceivingId === 0) {
			Error::show('no_access');
		}
	}
}
