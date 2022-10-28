<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Comment;

use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Util\Validate\Validations\Comment\PostComment;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class PostCommentTest extends TestCase
{
	use ProphecyTrait;

	public function testGetParams(): void
	{
		$commentRepository = $this->prophesize(CommentRepositoryInterface::class);
		$validateAllow = $this->prophesize(Allow::class);
		$validateUser = $this->prophesize(User::class);
		$validateData = $this->prophesize(Data::class);

		$postComment = new PostComment(
			$validateData->reveal(),
			$validateUser->reveal(),
			$validateAllow->reveal(),
			$commentRepository->reveal()
		);

		$this->assertEquals([
			'statusId' => 0,
			'userId' => 0,
			'body' => '',
		], $postComment->getParams());
	}
}
