<?php

namespace Breeze\Service;

use Breeze\Breeze;

class Tools
{
    protected $smcFunc;

    public function __construct()
    {

    }

	public function isJson(string $string): bool
	{
		json_decode($string);

		return (\JSON_ERROR_NONE == json_last_error());
	}

	public function truncateString(string $string, int $limit, string $break = ' ', string $pad = '...'): string
	{
		if(empty($string))
			return false;

		if(empty($limit))
			$limit = 30;

		if(strlen($string) <= $limit)
			return $string;

		if(false !== ($breakpoint = strpos($string, $break, $limit)))
			if($breakpoint < strlen($string) - 1)
				$string = substr($string, 0, $breakpoint) . $pad;

		return $string;
	}



    public function sanitize($variable)
    {
    	$smFunc = $this->global('smcFunc');

        if (is_array($variable))
        {
            foreach ($variable as $k => $v)
				$variable[$k] = $this->sanitize($v);

            return $variable;
        }

        $var = (string) $smFunc['htmltrim']($smFunc['htmlspecialchars']($variable, \ENT_QUOTES));

        if (ctype_digit($var))
            $var = (int) $var;

        if (empty($var))
            $var = false;


        return $var;
    }

	public function parser(string $text, array $replacements = []): string
	{
		$context = $this->global('context');

		if (empty($text) || empty($replacements) || !is_array($replacements))
			return '';

		$s = ';' . $context['session_var'] . '=' . $context['session_id'];

		$find = [];
		$replace = [];

		foreach ($replacements as $f => $r)
		{
			$find[] = '{' . $f . '}';
			$replace[] = $r . ((false !== strpos($f, 'href')) ? $s : '');
		}

		return str_replace($find, $replace, $text);
	}

	public function formatBytes(int $bytes, bool $showUnits = false): string
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= (1 << (10 * $pow));

		return round($bytes, 4) . ($showUnits ? ' ' . $units[$pow] : '');
	}

	public function global(string $variableName)
	{
		return $GLOBALS[$variableName] ?? false;
	}

	public function commaSeparated(string $string, string $type = 'alphanumeric'): string
	{
		switch ($type) {
			case 'numeric':
				$t = '\d';
				break;
			case 'alpha':
				$t = '[:alpha:]';
				break;
			case 'alphanumeric':
			default:
				$t = '[:alnum:]';
				break;
		}

		return empty($string) ? false : implode(',', array_filter(explode(',', preg_replace(
			[
				'/[^' . $t . ',]/',
				'/(?<=,),+/',
				'/^,+/',
				'/,+$/'
			],
			'',
			$string
		))));
	}

}