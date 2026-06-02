<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Repository\BaseRepositoryInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class MentionServiceTest extends TestCase
{
	private MockObject|BaseRepositoryInterface $userRepository;

	private MockObject|MentionService $mentionService;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->userRepository = $this->createMock(BaseRepositoryInterface::class);

		// Stub requireOnce so the constructor does not attempt require_once on Mentions.php
		$this->mentionService = $this->getMockBuilder(MentionService::class)
			->setConstructorArgs([$this->userRepository, null])
			->onlyMethods(['requireOnce'])
			->getMock();
	}

	public function testProcessBodyReturnsUnchangedBodyWhenMentionIdsAreEmpty(): void
	{
		$body = 'Hello @Alice, how are you?';

		$this->userRepository->expects($this->never())
			->method('loadUsersInfo');

		$result = $this->mentionService->processBody($body, []);

		$this->assertSame($body, $result['body']);
		$this->assertSame([], $result['members']);
	}

	public function testProcessBodyRewritesMentionToBBCWhenNameMatchesBody(): void
	{
		$memberId   = 42;
		$memberName = 'Alice';
		$body           = 'Hey @Alice, welcome!';
		$expectedBody   = 'Hey [member=42]Alice[/member], welcome!';

		$this->userRepository->expects($this->once())
			->method('loadUsersInfo')
			->with([$memberId])
			->willReturn([$memberId => ['name' => $memberName]]);

		$result = $this->mentionService->processBody($body, [$memberId]);

		$this->assertSame($expectedBody, $result['body']);
		$this->assertSame(
			[$memberId => ['id' => $memberId, 'real_name' => $memberName]],
			$result['members']
		);
	}

	public function testProcessBodyDropsMemberWhenNameNotInBody(): void
	{
		// Tampered: ID 42 belongs to "Alice", but the body only mentions "@Bob"
		$memberId = 42;
		$body     = 'Hey @Bob, check this out!';

		$this->userRepository->expects($this->once())
			->method('loadUsersInfo')
			->with([$memberId])
			->willReturn([$memberId => ['name' => 'Alice']]);

		$result = $this->mentionService->processBody($body, [$memberId]);

		$this->assertSame($body, $result['body']);
		$this->assertSame([], $result['members']);
	}

	public function testProcessBodyFiltersOutNonPositiveIds(): void
	{
		$body = 'Some post without real mentions';

		$this->userRepository->expects($this->never())
			->method('loadUsersInfo');

		$result = $this->mentionService->processBody($body, [0, -5, -1]);

		$this->assertSame($body, $result['body']);
		$this->assertSame([], $result['members']);
	}

	public function testProcessBodySkipsMemberWithEmptyNameFromRepository(): void
	{
		$memberId = 99;
		$body     = 'Post with @Someone mentioned';

		$this->userRepository->expects($this->once())
			->method('loadUsersInfo')
			->with([$memberId])
			->willReturn([$memberId => ['name' => '']]);

		$result = $this->mentionService->processBody($body, [$memberId]);

		$this->assertSame($body, $result['body']);
		$this->assertSame([], $result['members']);
	}
}
