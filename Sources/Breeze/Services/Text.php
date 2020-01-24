<?php


namespace Breeze\Service;


class Text
{
	/**
	 * @var Tools
	 */
	protected $tools;

	public function __construct(Tools $tools)
	{
		$this->tools = $tools;
	}

	public function setLanguage()
	{
	}

	public function get(string $textKey): string
	{
		$txt = $this->tools->global('txt');

		return $txt[$textKey];
	}

	public function timeElapsed($ptime): string
	{
		$txt = $this->tools->global('txt');
		$etime = time() - $ptime;

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

				return $r . ' ' . $str . (1 < $r ? 's ' : ' ') . $txt['time_ago'];
			}
		}
	}

}