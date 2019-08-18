<?php

declare(strict_types=1);

/**
 * BreezeForm
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2017, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

namespace Breeze;

if (!defined('SMF'))
	die('No direct access...');

class BreezeForm
{
	public $elements = [];
	public $buffer = '';
	protected $_app;
	protected $_textPrefix = 'user_settings_';

	function __construct(Breeze $app)
	{
		$this->_app = $app;
		$this->_options = ['name' => '', 'url' => '', 'title' => '', 'desc' => '', 'character_set' => '',];
	}

	public function setOptions($options)
	{
		if (empty($options))
			return false;

		$this->_options = array_merge($this->_options, $options);
	}

	public function setTextPrefix($string, $loadLanguage = false)
	{
		$this->_textPrefix = $string;

		if ($loadLanguage)
			$this->_app['tools']->loadLanguage($loadLanguage);
	}

	private function setText($text)
	{
		return $this->_app['tools']->text($this->_textPrefix . $text);
	}

	private function addElement($element)
	{
		$this->elements[] = $element;
	}

	private function getElement($id)
	{
		return $this->elements[$id];
	}

	protected function setParamValues(&$param)
	{
		// No text? use the name as a $txt key then!
		if (empty($param['text']))
			$param['text'] = $param['name'];

		// Give it a chance to use a full text string.
		$param['text']  = !empty($param['fullText']) ? $param['fullText'] : $this->setText($param['text']);
		$param['desc']  = !empty($param['fullDesc']) ? $param['fullDesc'] : $this->setText($param['name'] . '_sub');
	}

	function addSelect($param = [])
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'select';
		$param['html_start'] = '<' . $param['type'] . ' name="' . (!empty($this->_options['name']) ? $this->_options['name'] . '[' . $param['name'] . ']' : $param['name']) . '">';
		$param['html_end'] = '</' . $param['type'] . '>';

		foreach($values as $k => $v)
			$param['values'][$k] = '<option value="' . $k . '" ' . (isset($v[1]) && $v[1] == 'selected' ? 'selected="selected"' : '') . '>' . $this->_app['tools']->text($v[0]) . '</option>';

		return $this->addElement($param);
	}

	function addCheckBox($param = [])
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'checkbox';
		$param['value'] = 1;
		$param['checked'] = empty($param['checked']) ? '' : 'checked="checked"';
		$param['html'] = '<input type="' . $param['type'] . '" name="' . (!empty($this->_options['name']) ? $this->_options['name'] . '[' . $param['name'] . ']' : $param['name']) . '" id="' . $param['name'] . '" value="' . $param['value'] . '" ' . $param['checked'] . ' class="input_check" />';

		return $this->addElement($param);
	}

	function addText($param = [])
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'text';
		$param['size'] = empty($param['size'] ) ? 'size="20"' : 'size="' . $param['size'] . '"';
		$param['maxlength'] = empty($param['maxlength']) ? 'maxlength="20"' : 'maxlength="' . $param['maxlength'] . '"';
		$param['html'] = '<input type="' . $param['type'] . '" name="' . (!empty($this->_options['name']) ? $this->_options['name'] . '[' . $param['name'] . ']' : $param['name']) . '" id="' . $param['name'] . '" value="' . $param['value'] . '" ' . $param['size'] . ' ' . $param['maxlength'] . ' class="input_text" />';

		return $this->addElement($param);
	}

	function addTextArea($param = [])
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'textarea';
		$param['value'] = empty($param['value']) ? '' : $param['value'];

		// To a void having a large and complicate ternary, split these options.
		$rows = 'rows="' . (!empty($param['size'] ) && !empty($param['size']['rows']) ? $param['size']['rows'] : 10) . '"';
		$cols = 'cols="' . (!empty($param['size'] ) && !empty($param['size']['cols']) ? $param['size']['cols'] : 40) . '"';
		$param['maxlength'] = 'maxlength="' . (!empty($param['size'] ) && !empty($param['size']['maxlength']) ? $param['size']['maxlength'] : 1024) . '"';

		$param['html'] = '<' . $param['type'] . ' name="' . (!empty($this->_options['name']) ? $this->_options['name'] . '[' . $param['name'] . ']' : $param['name']) . '" id="' . $param['name'] . '" ' . $rows . ' ' . $cols . ' ' . $param['maxlength'] . '>' . $param['value'] . '</' . $param['type'] . '>';

		return $this->addElement($param);
	}

	function addHiddenField($name, $value)
	{
		$param['type'] = 'hidden';
		$param['html'] = '<input type="' . $param['type'] . '" name="' . $name . '" id="' . $name . '" value="' . $value . '" />';

		return $this->addElement($param);
	}

	function addHr()
	{
		$param['type'] = 'hr';
		$param['html'] = '<br /><hr /><br />';

		return $this->addElement($param);
	}

	function addHTML($param = [])
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'html';

		return $this->addElement($param);
	}

	function addButton($param = [])
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'button';

		return $this->addElement($param);
	}

	function addSection($param = [])
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'section';

		return $this->addElement($param);
	}

	function display()
	{
		global $context;

		loadtemplate(Breeze::$name . 'Form');

		$context['form'] = [
		    'options' => $this->_options,
		    'elements' => $this->elements,
		];

		return template_breeze_form();
	}
}
