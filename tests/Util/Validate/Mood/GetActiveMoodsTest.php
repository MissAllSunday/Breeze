<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Mood;

use Breeze\Service\MoodService;
use Breeze\Service\UserService;
use Breeze\Util\Validate\Validations\Mood\GetActiveMoods;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetActiveMoodsTest extends TestCase
{
	private GetActiveMoods $getActiveMoods;

	public function setUp(): void
	{
		/**  @var MockObject&UserService $userService */
		$userService = $this->createMock(UserService::class);

		/**  @var MockObject&MoodService $moodService */
		$moodService = $this->createMock(MoodService::class);

		$this->getActiveMoods = new GetActiveMoods($userService, $moodService);
	}

	public function testSuccessKeyString(): void
	{
		$this->assertEquals('moodCreated', $this->getActiveMoods->successKeyString());
	}
}
