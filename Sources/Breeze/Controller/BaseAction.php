<?php

declare(strict_types=1);

namespace BreezeController;

abstract class Base
{
	const STATUS = 'status';
	const COMMENT = 'comment';
	const MENTION = 'mention';
	const LOG = 'log';
	// const FETCH_STATUS = 'fetchStatus';
	// const FETCH_COMMENT = 'fetchComment';
	// const FETCH_LOG = 'fetchLog';
	const SETTINGS = 'settings';
	const COVER ='cover';
	const MOOD = 'mood';

	const ACTIONS = [
	    self::STATUS,
	    self::COMMENT,
	    self::DELETE,
	    self::MENTION,
	    self::LOG,
	    self::SETTINGS,
	    self::COVER,
	    self::MOOD,
	];
}
