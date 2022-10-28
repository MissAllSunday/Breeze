<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Status;

use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\Validations\Status\StatusByProfile;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class StatusByProfileTest extends TestCase
{
	use ProphecyTrait;

	public function testGetParams(): void
	{
		$repository = $this->prophesize(StatusRepositoryInterface::class);
		$validateAllow = $this->prophesize(Allow::class);
		$validateUser = $this->prophesize(User::class);
		$validateData = $this->prophesize(Data::class);

		$statusByProfile = new StatusByProfile(
			$validateData->reveal(),
			$validateUser->reveal(),
			$validateAllow->reveal(),
			$repository->reveal()
		);

		$this->assertEquals([
			'wallId' => 0,
		], $statusByProfile->getParams());
	}
}
