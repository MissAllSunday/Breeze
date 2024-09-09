<?php

declare(strict_types=1);


namespace Breeze\Traits;

use Breeze\Breeze;

trait TextTrait
{
	use SettingsTrait;

	private static string $session_token = 'href';

	public function getText(string $textKey): string
	{
		$this->setLanguage(Breeze::NAME);

		$txt = $this->global('txt');

		return empty($txt[Breeze::PATTERN . $textKey]) ? '' : $txt[Breeze::PATTERN . $textKey];
	}

	public function getSmfText(string $textKey): string
	{
		$txt = $this->global('txt');

		return empty($txt[$textKey]) ? '' : $txt[$textKey];
	}

	public function parserText(string $text, array $replacements = []): string
	{
		$context = $this->global('context');

		if ($text === '' || $text === '0') {
			return '';
		}

		if ($replacements === [] || !is_array($replacements)) {
			return $text;
		}

		$session_var = ';' . $context['session_var'] . '=' . $context['session_id'];

		$toFind = [];
		$replaceWith = [];

		foreach ($replacements as $find => $replace) {
			$toFind[] = '{' . $find . '}';
			$replaceWith[] = $replace . ((strpos($find, self::$session_token) !== false) ? $session_var : '');
		}

		return str_replace($toFind, $replaceWith, $text);
	}

	public function commaSeparated(string $dirtyString = '', string $type = 'alphanumeric'): string
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

		return $dirtyString === '' || $dirtyString === '0' ? '' : implode(',', array_filter(explode(',', preg_replace(
			[
				'/[^' . $t . ',]/',
				'/(?<=,),+/',
				'/^,+/',
				'/,+$/',
			],
			'',
			$dirtyString
		))));
	}

	public function normalizeString(string $string = ''): string
	{
		$string = htmlentities($string, \ENT_QUOTES);

		$string = preg_replace(
			'~&([a-z]{1,2})(amp|acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
			'$1',
			$string
		);

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

	public function truncateText(string $string = '', int $limit = 30, string $break = ' ', string $pad = '...'): string
	{
		if (strlen($string) <= $limit) {
			return $string;
		}

		if (false !== ($breakpoint = strpos($string, $break, $limit)) && $breakpoint < strlen($string) - 1) {
	  return substr($string, 0, $breakpoint) . $pad;
  }

		return $string;
	}

	public function timeElapsed(int $timeInSeconds): string
	{
		$txt = $this->global('txt');
		$sinceTime = time() - $timeInSeconds;
		$timeElapsed = '';

		if ($sinceTime < 1) {
			return $txt['time_just_now'];
		}

		$timePeriods = [
			12 * 30 * 24 * 60 * 60 => $txt['time_year'],
			30 * 24 * 60 * 60 => $txt['time_month'],
			24 * 60 * 60 => $txt['time_day'],
			60 * 60 => $txt['time_hour'],
			60 => $txt['time_minute'],
			1 => $txt['time_second'],
		];

		foreach ($timePeriods as $seconds => $timeString) {
			$timeCount = $sinceTime / $seconds;
			if ($timeCount >= 1) {
				$timeCountRounded = round($timeCount);

				$timeElapsed = $timeCountRounded . ' ' . $timeString .
					($timeCountRounded > 1 ? 's ' : ' ') . $txt['time_ago'];

				break;
			}
		}

		return $timeElapsed;
	}

	public function setLanguage(string $languageName): void
	{
		loadLanguage($languageName);
	}

	public function commaFormat(string $number): string
	{
		return comma_format($number);
	}

	public function tokenTxtReplace(string $text = ''): string
	{
		return tokenTxtReplace($text);
	}

	public function snakeToCamel($input): string
	{
		return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
	}
}
