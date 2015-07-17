<?php

/**
 * BreezeForm
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2015, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeForm
{
	public $elements = array();
	public $buffer = '';
	protected $_app;
	protected $_textPrefix = 'user_settings_';

	function __construct($app)
	{
		$this->_app = $app;
		$this->_options = array('name' => '', 'url' => '', 'title' => '', 'desc' => '', 'character_set' => '',);
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
		$param['desc']  = !empty($param['fullDesc']) ? $param['fullDesc'] : $this->setText($param['text'] .'_sub');
	}

	function addSelect($param = array())
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'select';
		$param['html_start'] = '<'. $param['type'] .' name="'. (!empty($this->_options['name']) ? $this->_options['name'] .'['. $param['name'] .']' : $param['name']) .'">';
		$param['html_end'] = '</'. $param['type'] .'>';

		foreach($values as $k => $v)
			$param['values'][$k] = '<option value="' .$k. '" '. (isset($v[1]) && $v[1] == 'selected' ? 'selected="selected"' : '') .'>'. $this->_app['tools']->text($v[0]) .'</option>';

		return $this->addElement($param);
	}

	function addCheckBox($param = array())
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'checkbox';
		$param['value'] = 1;
		$param['checked'] = empty($param['checked']) ? '' : 'checked="checked"';
		$param['html'] = '<input type="'. $param['type'] .'" name="'. (!empty($this->_options['name']) ? $this->_options['name'] .'['. $param['name'] .']' : $param['name']) .'" id="'. $param['name'] .'" value="'. $param['value'] .'" '. $param['checked'] .' class="input_check" />';

		return $this->addElement($param);
	}

	function addText($param = array())
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'text';
		$param['size'] = empty($param['size'] ) ? 'size="20"' : 'size="'. $param['size'] .'"';
		$param['maxlength'] = empty($param['maxlength']) ? 'maxlength="20"' : 'maxlength="'. $param['maxlength'] .'"';
		$param['html'] = '<input type="'. $param['type'] .'" name="'. (!empty($this->_options['name']) ? $this->_options['name'] .'['. $param['name'] .']' : $param['name']) .'" id="'. $param['name'] .'" value="'. $param['value'] .'" '. $param['size'] .' '. $param['maxlength'] .' class="input_text" />';

		return $this->addElement($param);
	}

	function addTextArea($param = array())
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'textarea';
		$param['value'] = empty($param['value']) ? '' : $param['value'];

		// To a void having a large and complicate ternary, split these options.
		$rows = 'rows="'. (!empty($param['size'] ) && !empty($param['size']['rows']) ? $param['size']['rows'] : 10) .'"';
		$cols = 'cols="'. (!empty($param['size'] ) && !empty($param['size']['cols']) ? $param['size']['cols'] : 40) .'"';
		$param['maxlength'] = 'maxlength="'. (!empty($param['size'] ) && !empty($param['size']['maxlength']) ? $param['size']['maxlength'] : 1024) .'"';

		$param['html'] = '<'. $param['type'] .' name="'. (!empty($this->_options['name']) ? $this->_options['name'] .'['. $param['name'] .']' : $param['name']) .'" id="'. $param['name'] .'" '. $rows .' '. $cols .' '. $param['maxlength'] .'>'. $param['value'] .'</'. $param['type'] .'>';

		return $this->addElement($param);
	}

	function addHiddenField($name, $value)
	{
		// Kinda needs this...
		if (empty($param))
			return;

		$param['type'] = 'hidden';
		$param['html'] = '<input type="'. $param['type'] .'" name="'. $name .'" id="'. $name .'" value="'. $value .'" />';

		return $this->addElement($param);
	}

	function addHr()
	{
		$param['type'] = 'hr';
		$param['html'] = '<br /><hr /><br />';

		return $this->addElement($param);
	}

	function addHTML($param = array())
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'html';

		return $this->addElement($param);
	}

	function addButton($param = array())
	{
		// Kinda needs this...
		if (empty($param) || empty($param['name']))
			return;

		$this->setParamValues($param);
		$param['type'] = 'button';

		return $this->addElement($param);
	}

	function addSection($param = array())
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
		$this->buffer = '
<form action="'. $this->_options['url'] .'" method="post" accept-charset="'. $this->_options['character_set'] .'" name="'. $this->_options['name'] .'" id="'. $this->_options['name'] .'">';

		// Any title and/or description?
		if (!empty($this->_options['title']))
			$this->buffer .= '
	<div class="cat_bar">
		<h3 class="catbg profile_hd">
				'. $this->_options['title'] .'
		</h3>
	</div>';

		if (!empty($this->_options['desc']))
			$this->buffer .= '
	<p class="info">
		'. $this->_options['desc'] .'
	</p>';

		$this->buffer .= '
	<div class="windowbg2">
		<div class="content">
			<dl class="settings">';

		foreach($this->elements as $el)
		{
			switch($el['type'])
			{
				case 'textarea':
				case 'checkbox':
				case 'text':
					$this->buffer .= '
				<dt>
					<span style="font-weight:bold;">'. $el['text'] .'</span>
					<br /><span class="smalltext">'. $el['desc'] .'</span>
				</dt>
				<dd>
					<input type="hidden" name="'. (!empty($this->_options['name']) ? $this->_options['name'] .'['. $el['name'] .']' : $el['name']) .'" value="0" />'. $el['html'] .'
				</dd>';
					break;
				case 'select':
					$this->buffer .= '
				<dt>
					<span style="font-weight:bold;">'. $el['text'] .'</span>
					<br /><span class="smalltext">'. $el['desc'] .'</span>
				</dt>
				<dd>
					<input type="hidden" name="'. (!empty($this->_options['name']) ? $this->_options['name'] .'['. $el['name'] .']' : $el['name']) .'" value="0" />'. $el['html_start'] .'';

					foreach($el['values'] as $k => $v)
						$this->buffer .= $v .'';

					$this->buffer .= $el['html_end'] .'
				</dd>';
					break;
				case 'hidden':
				case 'submit':
					$this->buffer .= '
				<dt></dt>
				<dd>
					'. $el['html'] .'
				</dd>';
					break;
				case 'hr':
					$this->buffer .= '
				</dl>
					'. $el['html'] .'
				<dl class="settings">';
					break;
				case 'html':
					$this->buffer .= '
				<dt>
					<span style="font-weight:bold;">'. $el['text'] .'</span>
					<br /><span class="smalltext">'. $el['desc'] .'</span>
				</dt>
				<dd>
					'. $el['html'] .'
				</dd>';
					break;
				case 'section':
				$this->buffer .= '
				</dl>
				<div class="cat_bar">
					<h3 class="catbg">'. $el['text'] .'</h3>
				</div>
				<br />
				<dl class="settings">';
					break;
			}
		}

		$this->buffer .= '
			</dl>';

		// Any buttons?
		foreach($this->elements as $el)
			if ($el['type'] == 'button')
				$this->buffer .= '<input type="submit" name="'. $el['name'] .'" value="'. $el['text'] .'" class="button_submit"/>';

		// Close it.
		$this->buffer .= '
		</div>
	</div>
	<br />
</form>';

		return $this->buffer;
	}
}
