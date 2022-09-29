<?php

declare(strict_types=1);


namespace Breeze\Entity;

class UserDataEntity
{
	public static function getColumns(): array
	{
		return [
			'avatar',
			'buddies',
			'custom_fields',
			'email',
			'group',
			'group_color',
			'group_icons',
			'group_id',
			'href',
			'id',
			'is_activated',
			'is_banned',
			'is_buddy',
			'is_guest',
			'is_reverse_buddy',
			'last_login_timestamp',
			'link',
			'link_color',
			'name',
			'name_color',
			'online',
			'signature',
			'title',
			'username',
			'username_color',
		];
	}
}
