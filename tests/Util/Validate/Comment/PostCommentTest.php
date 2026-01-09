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
		$commentRepository = $this->createStub(CommentRepositoryInterface::class);
		$validateAllow = $this->createStub(Allow::class);
		$validateUser = $this->createStub(User::class);
		$validateData = $this->createStub(Data::class);

		$postComment = new PostComment(
			$validateData,
			$validateUser,
			$validateAllow,
			$commentRepository
		);

		$this->assertEquals([
			'body' => '',
			'status_id' => 0,
			'user_id' => 0,
		], $postComment->getParams());
	}
}
