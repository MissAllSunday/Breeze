<?php

declare(strict_types=1);


namespace Breeze\Entity;

class UserSettingsHandledEntity extends UserSettingsEntity
{
	protected array $buddies = [];

	protected array $blockList = [];

	/**
	 * @return array [int]
	 */
	public function getBuddies(): array
	{
		return $this->buddies;
	}

	public function setBuddies(array | string $buddies): void
	{
		if (is_string($buddies)) {
			$buddies = explode(',', $buddies);
		}

		$this->buddies = array_map('intval', $buddies);
	}

	/**
	 * @return array [int]
	 */
	public function getBlockList(): array
	{
		return $this->blockList;
	}

	public function setBlockList(array | string $blockList): void
	{
		if (is_string($blockList)) {
			$blockList = explode(',', $blockList);
		}

		$this->blockList = array_map('intval', $blockList);
	}
}
