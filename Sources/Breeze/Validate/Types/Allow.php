<?php

declare(strict_types=1);

namespace Breeze\Validate\Types;

use Breeze\Entity\SettingsEntity;
use Breeze\Traits\PermissionsTrait;
use Breeze\Traits\PersistenceTrait;
use Breeze\Traits\SettingsTrait;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;

class Allow
{
	use SettingsTrait;
	use PersistenceTrait;
	use PermissionsTrait;

	/**
	 * @throws NotAllowedException
	 */
	public function permissions(string $permissionName, string $permissionMessageKey): void
	{
		if (!$this->isAllowedTo($permissionName)) {
			throw new NotAllowedException($permissionMessageKey);
		}
	}

	/**
	 * @throws NotAllowedException
	 */
	public function floodControl(int $posterId): void
	{
		$seconds = 60 * ($this->getSetting(SettingsEntity::MAX_FLOOD_MINUTES, 5));
		$messages = $this->getSetting(SettingsEntity::MAX_FLOOD_NUM, 10);
		$floodKeyName = 'flood_' . $posterId;

		$floodData = $this->getPersistenceValue($floodKeyName);

		// If the time window has expired, reset everything.
		if (!empty($floodData) && time() > $floodData['time']) {
			$this->unsetPersistenceValue($floodKeyName);
			$floodData = null;
		}

		// If we don't have flood data, this is the first post in a while.
		if (empty($floodData)) {
			$floodData = [
				'time' => time() + $seconds,
				'msgCount' => 0,
			];
		}

		$floodData['msgCount']++;

		// Persist the new count *before* checking.
		// This way, even if we throw, the incremented count is saved for the next check.
		$this->setPersistenceValue($floodKeyName, $floodData);

		if ($floodData['msgCount'] > $messages && time() <= $floodData['time']) {
			throw new NotAllowedException('flood');
		}
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function isFeatureEnable(string $featureName = '', string $featureNotEnabledMessageKey = ''): void
	{
		if (!$this->modSetting($featureName)) {
			throw new DataNotFoundException($featureNotEnabledMessageKey);
		}
	}
}
