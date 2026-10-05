<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Likes;

use Breeze\Entity\LikeEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\Likes\Like;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LikeTest extends TestCase
{
	private MockObject|LikeRepositoryInterface $repository;

	private MockObject|User $validateUser;

	private Like $like;

	/**
	 * @throws Exception
	 */
	public function setUp(): void
	{
		$this->repository = $this->createStub(LikeRepositoryInterface::class);
		$this->validateUser = $this->createMock(User::class);

		$this->like = new Like(
			$this->createStub(Data::class),
			$this->validateUser,
			$this->createStub(Allow::class),
			$this->repository
		);
	}

	#[DataProvider('checkTypeProvider')]
	public function testCheckType(string $type, bool $isExpectedException): void
	{
		$this->like->setData([
			LikeEntity::ID => 123,
			LikeEntity::TYPE => $type,
			LikeEntity::ID_MEMBER => 456,
		]);

		if ($isExpectedException) {
			$this->expectException(DataNotFoundException::class);
			$this->expectExceptionMessage('likesTypeInvalid');
		} else {
			$this->expectNotToPerformAssertions();
		}

		$this->like->checkType();
	}

	public static function checkTypeProvider(): array
	{
		return [
			'status' => ['type' => LikesEnum::Status->value, 'isExpectedException' => false],
			'comments' => ['type' => LikesEnum::Comments->value, 'isExpectedException' => false],
			'invalid' => ['type' => 'br_unknown', 'isExpectedException' => true],
		];
	}

	/**
	 * Regression: the liker must be the session user, otherwise any member
	 * can like or unlike content on someone else's behalf.
	 */
	public function testCheckUserAssertsLikerIsSessionUser(): void
	{
		$this->like->setData([
			LikeEntity::ID => 123,
			LikeEntity::TYPE => LikesEnum::Status->value,
			LikeEntity::ID_MEMBER => 456,
		]);

		$this->validateUser->expects($this->once())
			->method('isSameUser')
			->with(456);

		$this->validateUser->expects($this->once())
			->method('areValidUsers')
			->with([456]);

		$this->like->checkUser();
	}
}
