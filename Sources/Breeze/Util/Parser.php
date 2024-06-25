<?php

declare(strict_types=1);


namespace Breeze\Util;

class Parser
{
	public static function bbc(string $content): string
	{
		return parse_bbc($content);
	}
}
