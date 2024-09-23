<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Comment;

use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Util\Validate\Validations\Comment\PostComment;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\TestCase;

class PostCommentTest extends TestCase
{
	public function testGetParams(): void
	{
		$commentRepository = $this->createMock(CommentRepositoryInterface::class);
		$validateAllow = $this->createMock(Allow::class);
		$validateUser = $this->createMock(User::class);
		$validateData = $this->createMock(Data::class);

		$postComment = new PostComment(
			$validateData,
			$validateUser,
			$validateAllow,
			$commentRepository
		);

		$this->assertEquals([
			'statusId' => 0,
			'userId' => 0,
			'body' => '',
		], $postComment->getParams());
	}
}
