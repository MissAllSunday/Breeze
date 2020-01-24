<?php

declare(strict_types=1);


namespace Breeze\Repository\User;

use Breeze\Service\Tools;

class Cover
{
	const COVER_FOLDER = '/breezeFiles/';

	/**
	 * @var Tools
	 */
	protected $tools;

	public function __construct(Tools $tools)
	{
		$this->tools = $tools;
	}

	public function deleteCover(string $imageFileName, int $userId): bool
	{
		if (empty($imageFileName) || empty($userId))
			return false;

		$boardDir = $this->tools->global('board_dir');
		$folder = $boardDir . self::COVER_FOLDER . $userId . '/';
		$folderThumbnail = $this->boardDir . Breeze::$coversFolder . $user . '/thumbnail/';

		if (file_exists($folderThumbnail . $image))
			@unlink($folderThumbnail . $image);

		if (file_exists($folder . $image))
			@unlink($folder . $image);

		return true;
	}
}
