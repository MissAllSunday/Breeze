<?php

declare(strict_types=1);


namespace Breeze\Entity;

class Members extends Base
{
	const COLUMN_PROFILE_VIEWS = 'breeze_profile_views';
	const COLUMN_IGNORE_LIST = 'pm_ignore_list';
	const COLUMN_BUDDY_LIST = 'buddy_list';

	function getColumns(): array
	{
		return [
		    self::COLUMN_PROFILE_VIEWS,
		    self::COLUMN_IGNORE_LIST,
		    self::COLUMN_BUDDY_LIST
		];

	}
}