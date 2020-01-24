<?php


namespace Breeze\Repository\User;


use Breeze\Breeze;

class Cover
{
	public function deleteCover($image, $user): void
	{
		if (empty($image) || empty($user))
			return;

		// This makes things easier.
		$folder = $this->boardDir . Breeze::$coversFolder . $user . '/';
		$folderThumbnail = $this->boardDir . Breeze::$coversFolder . $user . '/thumbnail/';

		if (file_exists($folderThumbnail . $image))
			@unlink($folderThumbnail . $image);

		if (file_exists($folder . $image))
			@unlink($folder . $image);
	}
}