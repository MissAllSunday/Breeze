<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Comment;

use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Util\Validate\Validations\Comment\DeleteComment;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DeleteCommentTest extends TestCase
{
	use ProphecyTrait;

	public function testGetParams(): void
	{
		$commentRepository = $this->prophesize(CommentRepositoryInterface::class);
		$validateAllow = $this->prophesize(Allow::class);
		$validateUser = $this->prophesize(User::class);
		$validateData = $this->prophesize(Data::class);

		$deleteComment = new DeleteComment(
			$validateData->reveal(),
			$validateUser->reveal(),
			$validateAllow->reveal(),
			$commentRepository->reveal()
		);

		$this->assertEquals([
			'id' => 0,
			'userId' => 0,
		], $deleteComment->getParams());
	}
}
