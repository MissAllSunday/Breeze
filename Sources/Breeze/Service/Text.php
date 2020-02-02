<?php

declare(strict_types=1);


namespace Breeze\Service;

class Text extends Base
{
	protected const SESSION_PARSER = 'href';

	public function setLanguage(string $languageName): void
	{
		loadLanguage($languageName);
	}

	public function get(string $textKey): string
	{
		$txt = $this->global('txt');

		return !empty($txt[$textKey]) ? $txt[$textKey] : '';
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
			$replace[] = $r . ((false !== strpos($f, self::SESSION_PARSER)) ? $s : '');
		}

		return str_replace($find, $replace, $text);
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

		return empty($string) ? '' : implode(',', array_filter(explode(',', preg_replace(
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

	public function normalizeString(string $string = ''): string
	{
		$smcFunc = $this->global('smcFunc');

		if (empty($string))
			return '';

		$string = $smcFunc['htmlspecialchars']($string, ENT_QUOTES);
		$string = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);
		$string = html_entity_decode($string, ENT_QUOTES);
		$string = preg_replace(['~[^0-9a-z]~i', '~[ -]+~'], ' ', $string);

		return trim($string, ' -');
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

	public function truncate(string $string, int $limit, string $break = ' ', string $pad = '...'): string
	{
		if(empty($string))
			return '';

		if(empty($limit))
			$limit = 30;

		if(strlen($string) <= $limit)
			return $string;

		if(false !== ($breakpoint = strpos($string, $break, $limit)))
			if($breakpoint < strlen($string) - 1)
				$string = substr($string, 0, $breakpoint) . $pad;

		return $string;
	}

	public function timeElapsed($ptime): string
	{
		$txt = $this->global('txt');
		$etime = time() - $ptime;
		$timeElapsed = '';

		if (1 > $etime)
			return $txt['time_just_now'];

		$a = [
		    12 * 30 * 24 * 60 * 60	=> $txt['time_year'],
		    30 * 24 * 60 * 60		=> $txt['time_month'],
		    24 * 60 * 60			=> $txt['time_day'],
		    60 * 60					=> $txt['time_hour'],
		    60						=> $txt['time_minute'],
		    1						=> $txt['time_second']
		];

		foreach ($a as $secs => $str)
		{
			$d = $etime / $secs;
			if (1 <= $d)
			{
				$r = round($d);

				$timeElapsed = $r . ' ' . $str . (1 < $r ? 's ' : ' ') . $txt['time_ago'];
				break;
			}
		}

		return $timeElapsed;
	}
}
