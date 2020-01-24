<?php

declare(strict_types=1);


namespace Breeze\Service;


class Data
{
	protected $request;

    /**
     * @var Tools
     */
    protected $tools;

    public function __construct(Tools $tools)
	{
		$this->request = $_REQUEST;
        $this->tools = $tools;
    }


	public function get(string $value)
	{
	    return isset($this->request[$value]) ? $this->tools->sanitize($this->request[$value]) : false;
	}

	public function getAll()
	{
		return array_map(function($k, $v)
		{
			return $this->tools->sanitize($v);
		}, $this->request);
	}

	public function normalizeString($string = '')
	{
		global $context, $smcFunc;

		if (empty($string))
			return '';

		$string = $smcFunc['htmlspecialchars']($string, \ENT_QUOTES, $context['character_set']);
		$string = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);
		$string = html_entity_decode($string, \ENT_QUOTES, $context['character_set']);
		$string = preg_replace(['~[^0-9a-z]~i', '~[ -]+~'], ' ', $string);

		return trim($string, ' -');
	}
}
