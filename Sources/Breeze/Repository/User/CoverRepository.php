<?php

declare(strict_types=1);


namespace Breeze\Repository\User;

use Breeze\Service\Settings as Settings;

class CoverRepository
{
	const COVER_FOLDER = '/breezeFiles/';
	const THUMB_FOLDER = '/thumbnail/';

	/**
	 * @var Settings
	 */
	protected $settings;

	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
	}

	public function deleteCover(string $fileName, int $userId): bool
	{
		if (empty($imageFileName) || empty($userId))
			return false;

		$boardDir = $this->settings->global('board_dir');
		$folder = $boardDir . self::COVER_FOLDER . $userId . '/';
		$folderThumbnail = $boardDir . self::COVER_FOLDER . $userId . self::THUMB_FOLDER;

		if (file_exists($folderThumbnail . $fileName))
			@unlink($folderThumbnail . $fileName);

		if (file_exists($folder . $fileName))
			@unlink($folder . $fileName);

		return true;
	}
}