<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\AlertEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\BuddyRequestRepositoryInterface;
use Breeze\Repository\User\SettingsRepositoryInterface as UserSettingsRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class BuddyServiceTest extends TestCase
{
	private AlertServiceInterface|MockObject $alertService;

	private UserSettingsRepository|MockObject $userSettingsRepository;

	private BuddyRequestRepositoryInterface|MockObject $buddyRequestRepository;

	private BuddyService $buddyService;

	private array $currentUserInfo;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertService = $this->createMock(AlertServiceInterface::class);
		$this->userSettingsRepository = $this->createMock(UserSettingsRepository::class);
		$this->buddyRequestRepository = $this->createMock(BuddyRequestRepositoryInterface::class);

		$this->buddyService = new BuddyService(
			$this->userSettingsRepository,
			$this->alertService,
			$this->buddyRequestRepository
		);

		$this->currentUserInfo = [
			'id' => 10,
			'is_guest' => false,
			'buddies' => [],
		];
	}

	public function testAddBuddySendsInviteAlert(): void
	{
		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function (AlertEntity $alert) {
				return $alert->getIdMember() === 5
					&& $alert->getIdMemberStarted() === 10
					&& $alert->getContentType() === 'Breeze_buddy'
					&& $alert->getContentAction() === 'Breeze_invite';
			}));

		$this->buddyService->addBuddy(5, $this->currentUserInfo);
	}

	public function testRemoveBuddyUpdatesMemberData(): void
	{
		$this->currentUserInfo['buddies'] = [5, 7, 12];

		$this->buddyRequestRepository->expects($this->exactly(2))
			->method('deleteByUsers');

		$this->buddyService->removeBuddy(5, $this->currentUserInfo);
	}

	public function testConfirmBuddyAddsSenderAndSendsAlert(): void
	{
		$GLOBALS['modSettings']['Breeze_allowAutoFollowBack'] = false;

		$this->alertService->expects($this->once())
			->method('send')
			->with($this->callback(function (AlertEntity $alert) {
				return $alert->getIdMember() === 5
					&& $alert->getIdMemberStarted() === 10
					&& $alert->getContentType() === 'Breeze_buddy'
					&& $alert->getContentAction() === 'Breeze_confirmed';
			}));

		$this->buddyService->confirmBuddy(5, $this->currentUserInfo);
	}

	public function testConfirmBuddyWithAutoFollowBackEnabled(): void
	{
		$GLOBALS['modSettings']['Breeze_allowAutoFollowBack'] = true;

		$currentUserSettings = UserSettingsEntity::from(['autoFollowBack' => 1]);
		$senderSettings = UserSettingsEntity::from(['buddies' => '3,7']);

		$this->userSettingsRepository->expects($this->exactly(2))
			->method('getById')
			->willReturnCallback(fn (int $userId) => match ($userId) {
				10 => $currentUserSettings,
				5 => $senderSettings,
			});

		$this->alertService->expects($this->once())
			->method('send');

		$this->buddyService->confirmBuddy(5, $this->currentUserInfo);
	}

	public function testConfirmBuddyWithAutoFollowBackDisabledByUser(): void
	{
		$GLOBALS['modSettings']['Breeze_allowAutoFollowBack'] = true;

		$currentUserSettings = UserSettingsEntity::from(['autoFollowBack' => 0]);

		$this->userSettingsRepository->expects($this->once())
			->method('getById')
			->with(10)
			->willReturn($currentUserSettings);

		$this->alertService->expects($this->once())
			->method('send');

		$this->buddyService->confirmBuddy(5, $this->currentUserInfo);
	}

	public function testConfirmBuddyDoesNotDuplicateSenderBuddy(): void
	{
		$GLOBALS['modSettings']['Breeze_allowAutoFollowBack'] = true;

		$currentUserSettings = UserSettingsEntity::from(['autoFollowBack' => 1]);
		$senderSettings = UserSettingsEntity::from(['buddies' => '10,7']);

		$this->userSettingsRepository->expects($this->exactly(2))
			->method('getById')
			->willReturnCallback(fn (int $userId) => match ($userId) {
				10 => $currentUserSettings,
				5 => $senderSettings,
			});

		$this->buddyService->confirmBuddy(5, $this->currentUserInfo);
	}
}
