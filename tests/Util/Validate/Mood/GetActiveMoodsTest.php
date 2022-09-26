<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Mood;

use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Validate\Validations\Mood\GetActiveMoods;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class GetActiveMoodsTest extends TestCase
{
	use ProphecyTrait;

	public function testSuccessKeyString(): void
	{
		$moodRepository = $this->prophesize(MoodRepositoryInterface::class);
		$getActiveMoods = new GetActiveMoods($moodRepository->reveal());

		$this->assertEquals('moodCreated', $getActiveMoods->successKeyString());
	}
}
