<?php

declare(strict_types=1);

use Breeze\Service\CommentService as CommentService;
use Breeze\Service\StatusService as StatusService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\PostComment;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PostCommentTest extends TestCase
{
	/**
	 * @var MockBuilder|UserService
	 */
	private $userService;

	/**
	 * @var PostComment
	 */
	private $postComment;

	/**
	 * @var MockObject|StatusService
	 */
	private $statusService;

	public function setUp(): void
	{
		$this->userService = $this->getMockInstance(UserService::class);
		$this->statusService = $this->getMockInstance(StatusService::class);

		/**  @var MockObject|CommentService $commentService */
		$commentService = $this->getMockInstance(CommentService::class);

		$this->postComment = new PostComment($this->userService, $this->statusService, $commentService);
	}

	/**
	 * @dataProvider cleanProvider
	 */
	public function testClean(array $data, bool $isExpectedException): void
	{
		$this->postComment->setData($data);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$this->postComment->clean();
		}

		else
			$this->assertNull($this->postComment->clean());
	}

	public function cleanProvider(): array
	{
		return [
			'empty values' => [
				'data' => [
					'comments_poster_id' => 0,
					'comments_status_owner_id' => '0',
					'comments_profile_id' => '',
					'comments_status_id' => '666',
					'comments_body' => 'LOL',
				],
				'isExpectedException' => true,
			],
			'happy path' => [
				'data' => [
					'comments_poster_id' => 1,
					'comments_status_owner_id' => 2,
					'comments_profile_id' => 3,
					'comments_status_id' => 666,
					'comments_body' => 'Happy Path',
				],
				'isExpectedException' => false,
			],
			'incomplete data' => [
				'data' => [
					'comments_poster_id' => '1'
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider IsValidIntProvider
	 */
	public function testIsValidInt(array $data, bool $isExpectedException): void
	{
		$this->postComment->setData($data);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$this->postComment->isInt();
		}

		else
			$this->assertNull($this->postComment->isInt());
	}

	public function IsValidIntProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'comments_poster_id' => 1,
					'comments_status_owner_id' => 2,
					'comments_profile_id' => 3,
					'comments_status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'not ints' => [
				'data' => [
					'comments_poster_id' => 'lol',
					'comments_status_owner_id' => 'fail',
					'comments_profile_id' => '',
					'comments_status_id' => '666',
					'comments_body' => 'LOL',
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider IsValidStringProvider
	 */
	public function testIsValidString(array $data, bool $isExpectedException): void
	{
		$this->postComment->setData($data);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$this->postComment->isString();
		}

		else
			$this->assertNull($this->postComment->isString());
	}

	public function IsValidStringProvider(): array
	{
		return [
			'happy path' => [
				'data' => [
					'comments_poster_id' => 1,
					'comments_status_owner_id' => 2,
					'comments_profile_id' => 3,
					'comments_status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'not a string' => [
				'data' => [
					'comments_poster_id' => 1,
					'comments_status_owner_id' => 2,
					'comments_profile_id' => 3,
					'comments_status_id' => 666,
					'comments_body' => 666,
				],
				'isExpectedException' => true,
			],
		];
	}

	/**
	 * @dataProvider floodControlProvider
	 */
	public function testFloodControl(array $data, bool $isExpectedException): void
	{
		$this->postComment->setData($data);

		if ($isExpectedException)
		{
			$this->expectException(ValidateDataException::class);

			$this->postComment->floodControl();
		}

		else
			$this->assertNull($this->postComment->floodControl());
	}

	public function floodControlProvider(): array
	{
		return [
			'happy happy joy joy' => [
				'data' => [
					'comments_poster_id' => 1,
					'comments_status_owner_id' => 2,
					'comments_profile_id' => 3,
					'comments_status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'time has not expired, too much messages' => [
				'data' => [
					'comments_poster_id' => 2,
					'comments_status_owner_id' => 2,
					'comments_profile_id' => 3,
					'comments_status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => true,
			],
			'time has expired, too much messages' => [
				'data' => [
					'comments_poster_id' => 3,
					'comments_status_owner_id' => 2,
					'comments_profile_id' => 3,
					'comments_status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
			'time has not expired,  allowed messages' => [
				'data' => [
					'comments_poster_id' => 4,
					'comments_status_owner_id' => 2,
					'comments_profile_id' => 3,
					'comments_status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
				],
				'isExpectedException' => false,
			],
		];
	}

	/**
	 * @dataProvider permissionsProvider
	 */
	public function testPermissions(array $data): void
	{
		$this->postComment->setData($data);

		$this->expectException(ValidateDataException::class);
		$this->postComment->permissions();
	}

	public function permissionsProvider(): array
	{
		return [
			'not allowed' => [
				'data' => [
					'comments_poster_id' => 1,
					'comments_status_owner_id' => 2,
					'comments_profile_id' => 3,
					'comments_status_id' => 666,
					'comments_body' => 'Kaizoku ou ni ore wa naru',
				],
			],
		];
	}

	private function getMockInstance(string $class): MockObject
	{
		return $this->getMockBuilder($class)
			->disableOriginalConstructor()
			->getMock();
	}
}
