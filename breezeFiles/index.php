<?php

declare(strict_types=1);

/**
 * This file is here solely to protect your Sources directory.
 */

// Look for Generalphp....
if (file_exists(dirname(__FILE__, 2) . '/General.php))
{
	// Found it!
	require(dirname(__FILE__, 2) . '/General.php);
	header('Location: ' . $boardurl);
}
// Can't find it... just forget it.
else
	exit;
