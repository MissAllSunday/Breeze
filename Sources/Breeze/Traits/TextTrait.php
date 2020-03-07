<?php

declare(strict_types=1);


namespace Breeze\Traits;

use Breeze\Breeze;

trait TextTrait
{
	use SettingsTrait;

	private static $session_parser = 'href';

	public function setLanguage(string $languageName): void
	{
		loadLanguage($languageName);
	}

	public function getText(string $textKey): string
	{
		$txt = $this->global('txt');

		return !empty($txt[Breeze::PATTERN . $textKey]) ? $txt[Breeze::PATTERN . $textKey] : '';
	}

    public function getSmfText(string $textKey): string
    {
        $txt = $this->global('txt');

        return !empty($txt[$textKey]) ? $txt[$textKey] : '';
    }

	public function parserText(string $text, array $replacements = []): string
	{
		$context = $this->global('context');

		if (empty($text))
			return '';

		if (empty($replacements) || !is_array($replacements))
			return $text;

		$session_var = ';' . $context['session_var'] . '=' . $context['session_id'];

		$toFind = [];
		$replaceWith = [];

		foreach ($replacements as $find => $replace)
		{
			$toFind[] = '{' . $find . '}';
			$replaceWith[] = $replace . ((false !== strpos($find, self::$session_parser)) ? $session_var : '');
		}

		return str_replace($toFind, $replaceWith, $text);
	}

	public function commaSeparated(string $dirtyString, string $type = 'alphanumeric'): string
	{
		if (!is_string($dirtyString))
			return '';

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

		return empty($dirtyString) ? '' : implode(',', array_filter(explode(',', preg_replace(
			[
				'/[^' . $t . ',]/',
				'/(?<=,),+/',
				'/^,+/',
				'/,+$/'
			],
			'',
			$dirtyString
		))));
	}

	public function normalizeString(string $string = ''): string
	{
		if (empty($string))
			return '';

		$string = htmlentities($string, ENT_QUOTES);

		$string = preg_replace('~&([a-z]{1,2})(amp|acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);

		return trim($string);
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

	public function truncateText(string $string, int $limit = 30, string $break = ' ', string $pad = '...'): string
	{
		if(empty($string))
			return '';

		if(strlen($string) <= $limit)
			return $string;

		if(false !== ($breakpoint = strpos($string, $break, $limit)))
			if($breakpoint < strlen($string) - 1)
				$string = substr($string, 0, $breakpoint) . $pad;

		return $string;
	}

	public function timeElapsed(int $timeInSeconds): string
	{
		$txt = $this->global('txt');
		$sinceTime = time() - $timeInSeconds;
		$timeElapsed = '';

		if (1 > $sinceTime)
			return $txt['time_just_now'];

		$timePeriods = [
			12 * 30 * 24 * 60 * 60	=> $txt['time_year'],
			30 * 24 * 60 * 60		=> $txt['time_month'],
			24 * 60 * 60			=> $txt['time_day'],
			60 * 60					=> $txt['time_hour'],
			60						=> $txt['time_minute'],
			1						=> $txt['time_second']
		];

		foreach ($timePeriods as $seconds => $timeString)
		{
			$timeCount = $sinceTime / $seconds;
			if (1 <= $timeCount)
			{
				$timeCountRounded = round($timeCount);

				$timeElapsed = $timeCountRounded . ' ' . $timeString . (1 < $timeCountRounded ? 's ' : ' ') . $txt['time_ago'];
				break;
			}
		}

		return $timeElapsed;
	}
}
