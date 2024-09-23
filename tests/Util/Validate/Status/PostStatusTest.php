<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\Validations\Status\PostStatus;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\TestCase;

class PostStatusTest extends TestCase
{
	public function testGetParams(): void
	{
		$repository = $this->createMock(StatusRepositoryInterface::class);
		$validateAllow = $this->createMock(Allow::class);
		$validateUser = $this->createMock(User::class);
		$validateData = $this->createMock(Data::class);

		$postStatus = new PostStatus(
			$validateData,
			$validateUser,
			$validateAllow,
			$repository
		);

		$this->assertEquals([
			'wallId' => 0,
			'userId' => 0,
			'body' => '',
		], $postStatus->getParams());
	}
}
