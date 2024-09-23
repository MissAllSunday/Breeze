<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\Validations\Status\StatusByProfile;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\TestCase;

class StatusByProfileTest extends TestCase
{
	public function testGetParams(): void
	{
		$repository = $this->createMock(StatusRepositoryInterface::class);
		$validateAllow = $this->createMock(Allow::class);
		$validateUser = $this->createMock(User::class);
		$validateData = $this->createMock(Data::class);

		$statusByProfile = new StatusByProfile(
			$validateData,
			$validateUser,
			$validateAllow,
			$repository
		);

		$this->assertEquals([
			'wallId' => 0,
		], $statusByProfile->getParams());
	}
}
