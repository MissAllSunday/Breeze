<?php

/**
 * BreezeForm
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeForm
{
	public $method;
	public $action;
	public $name;
	public $id_css;
	public $class;
	public $elements = array();
	public $status = 0;
	public $buffer = '';
	public $onsubmit;
	protected $_tools;
	protected $formName;

	function __construct($tools)
	{
		$this->_tools = $tools;
	}

	public function setFormName($string)
	{
		if (empty($string))
			return false;

		$this->formName = trim($string);
	}

	public function returnElementNames()
	{
		$this->returnelementsnames = array();

		if (!empty($this->elements))
			foreach ($this->elements as $e)
				if (isset($e['name']) && !empty($e['name']))
					$this->returnelementsnames[$e['name']] = $e['name'];

		return $this->returnelementsnames;
	}

	private function addElement($element)
	{
		$plus = $this->countElements();
		$element['id'] = $this->countElements();
		$this->elements[$element['id']] = $element;
	}

	private function countElements()
	{
		return count($this->elements);
	}

	private function getElement($id)
	{
		return $this->elements[$id];
	}

	private function getNextElement()
	{
		if( $this->status == $this->countElements())
			$this->status = 0;

		$element = $this->getElement($this->status);
		$this->status++;
	}

	function addSelect($name,$values = array())
	{
		$element['type'] = 'select';
		$element['name'] = $name;
		$element['values'] = $values;
		$element['text']  = $name;
		$element['html_start'] = '<'. $element['type'] .' name="'. (!empty($this->formName) ? $this->formName .'['. $element['name'] .']' : $element['name']) .'">';
		$element['html_end'] = '</'. $element['type'] .'>';

		foreach($values as $k => $v)
			$element['values'][$k] = '<option value="' .$k. '" '. (isset($v[1]) && $v[1] == 'selected' ? 'selected="selected"' : '') .'>'. $this->_tools->text($v[0]) .'</option>';

		return $this->addElement($element);
	}

	function addCheckBox($name,$checked = false)
	{
		$element['type'] = 'checkbox';
		$element['name'] = $name;
		$element['value'] = 1;
		$element['checked'] = empty($checked) ? '' : 'checked="checked"';
		$element['text'] = $name;
		$element['html'] = '<input type="'. $element['type'] .'" name="'. (!empty($this->formName) ? $this->formName .'['. $element['name'] .']' : $element['name']) .'" id="'. $element['name'] .'" value="'. (int)$element['value'] .'" '. $element['checked'] .' class="input_check" />';

		return $this->addElement($element);
	}

	function addText($name,$value, $size = false, $maxlength = false)
	{
		$element['type'] = 'text';
		$element['name'] = $name;
		$element['value'] = $value;
		$element['text'] = $name;
		$element['size'] = empty($size) ? 'size="20"' : 'size="' .$size. '"';
		$element['maxlength'] = empty($maxlength) ? 'maxlength="20"' : 'maxlength="' .$maxlength. '"';
		$element['html'] = '<input type="'. $element['type'] .'" name="'. (!empty($this->formName) ? $this->formName .'['. $element['name'] .']' : $element['name']) .'" id="'. $element['name'] .'" value="'. $element['value'] .'" '. $element['size'] .' '. $element['maxlength'] .' class="input_text" />';

		return $this->addElement($element);
	}

	function addTextArea($name, $value, $size = array('cols' => 40, 'rows' =>10, 'maxLength' => 1024))
	{
		$element['type'] = 'textarea';
		$element['name'] = $name;
		$element['value'] = empty($value) ? '' : $value;
		$element['text'] = $name;

		// To a void having a large and complicate ternary, split these options.
		$rows = 'rows="'. (!empty($size) && !empty($size['rows']) ? $size['rows'] : 10) .'"';
		$cols = 'cols="'. (!empty($size) && !empty($size['cols']) ? $size['cols'] : 40) .'"';
		$maxLength = 'maxlength="'. (!empty($size) && !empty($size['maxLength']) ? $size['maxLength'] : 1024) .'"';

		$element['html'] = '<'. $element['type'] .' name="'. (!empty($this->formName) ? $this->formName .'['. $element['name'] .']' : $element['name']) .'" id="'. $element['name'] .'" '. $rows .' '. $cols .' '. $maxLength .'>'. $element['value'] .'</'. $element['type'] .'>';

		return $this->addElement($element);
	}

	function addHiddenField($name, $value)
	{
		$element['type'] = 'hidden';
		$element['name'] = $name;
		$element['value'] = $value;
		$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" id="'. $element['name'] .'" value="'. $element['value'] .'" />';

		return $this->addElement($element);
	}

	function addHr()
	{
		$element['type'] = 'hr';
		$element['html'] = '<br /><hr /><br />';

		return $this->addElement($element);
	}

	function addHTML($text, $html)
	{
		$element['type'] = 'html';
		$element['text'] = $text;
		$element['html'] = $html;

		return $this->addElement($element);
	}

	function addSection($text)
	{
		$element['type'] = 'section';
		$element['text'] = $text;

		return $this->addElement($element);
	}

	function display()
	{
		$this->buffer .= '<dl class="settings">';
		$element = $this->getNextElement();

		foreach($this->elements as $el)
		{
			switch($el['type'])
			{
				case 'textarea':
				case 'checkbox':
				case 'text':
					$this->buffer .= '<dt>
						<span style="font-weight:bold;">'. $this->_tools->text('user_settings_'. $el['text']) .'</span>
						<br /><span class="smalltext">'. $this->_tools->text('user_settings_'. $el['text'] .'_sub') .'</span>
					</dt>
					<dd>
						<input type="hidden" name="'. (!empty($this->formName) ? $this->formName .'['. $el['name'] .']' : $el['name']) .'" value="0" />'. $el['html'] .'
					</dd>';
					break;
				case 'select':
					$this->buffer .= '<dt>
						<span style="font-weight:bold;">'. $this->_tools->text('user_settings_'. $el['text']) .'</span>
						<br /><span class="smalltext">'. $this->_tools->text('user_settings_'.$el['text'] .'_sub') .'</span>
					</dt>
					<dd>
						<input type="hidden" name="'. (!empty($this->formName) ? $this->formName .'['. $el['name'] .']' : $el['name']) .'" value="0" />'. $el['html_start'] .'';

					foreach($el['values'] as $k => $v)
						$this->buffer .= $v .'';

					$this->buffer .= $el['html_end'] .'
					</dd>';
					break;
				case 'hidden':
				case 'submit':
					$this->buffer .= '<dt></dt>
					<dd>
						'. $el['html'] .'
					</dd>';
					break;
				case 'hr':
					$this->buffer .= '</dl>
						'. $el['html'] .'
					<dl class="settings">';
					break;
				case 'html':
					$this->buffer .= '<dt>
						<span style="font-weight:bold;">'. $this->_tools->text('user_settings_'. $el['text']) .'</span>
						<br /><span class="smalltext">'. $this->_tools->text('user_settings_'.$el['text'] .'_sub') .'</span>
					</dt>
					<dd>
						'. sprintf($el['html'], $this->_tools->text('user_settings_'. $el['text'])) .'
					</dd>';
					break;
				case 'section':
				$this->buffer .= '
				</dl>
				<div class="cat_bar">
					<h3 class="catbg">'. $this->_tools->text('user_settings_'. $el['text']) .'</h3>
				</div>
				<br />
				<dl class="settings">';
					break;
			}
		}

		$this->buffer .= '</dl>';

		return $this->buffer;
	}
}
