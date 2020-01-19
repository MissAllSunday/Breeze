<?php

declare(strict_types=1);


namespace Breeze\Actions;


class Status
{
	const CREATE = 'create';
	const DELETE = 'delete';

	const ACTIONS = [
	    self::CREATE,
	    self::DELETE,
	];

}