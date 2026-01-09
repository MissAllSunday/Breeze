<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\Validations\Status\PostStatus;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class PostStatusTest extends TestCase
{
	/**
	 * @throws Exception
	 */
	public function testGetParams(): void
	{
		$repository = $this->createStub(StatusRepositoryInterface::class);
		$validateAllow = $this->createStub(Allow::class);
		$validateUser = $this->createStub(User::class);
		$validateData = $this->createStub(Data::class);

		$postStatus = new PostStatus(
			$validateData,
			$validateUser,
			$validateAllow,
			$repository
		);

		$this->assertEquals([
			'wall_id' => 0,
			'user_id' => 0,
			'body' => '',
		], $postStatus->getParams());
	}
}
