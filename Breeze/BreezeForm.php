<?php

/**
 * BreezeForm
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0 Beta 2
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

	class BreezeForm
	{
		public $method;
		public $action;
		public $name;
		public $id_css;
		public $class;
		public $elements;
		public $status;
		public $buffer;
		public $onsubmit;
		public $text;

		function __construct($form = array())
		{
			global $scripturl, $txt;

			LoadLanguage('Breeze');

			if (empty($form) || !is_array($form))
				return;

			/* Load the text strings */
			$this->text = $txt;

			$this->action = $scripturl . '?action=' . $form['action'];
			$this->method = $form['method'];
			$this->id_css= $form['id_css'];
			$this->name = $form['name'];
			$this->onsubmit = empty($form['onsubmit']) ? '' : 'onsubmit="'. $form['onsubmit'] .'"';
			$this->class_css = $form['class_css'];
			$elements = array();
			$this->status = 0;
			$this->buffer = '';
		}

		public function ReturnElementNames()
		{
			$this->Returnelementsnames = array();

			if (!empty($this->elements))
				foreach ($this->elements as $e)
					if (isset($e['name']) && !empty($e['name']))
						$this->Returnelementsnames[$e['name']] = $e['name'];

			return $this->Returnelementsnames;
		}

		private function AddElement($element)
		{
			$plus = $this->CountElements();
			$element['id'] = $this->CountElements();
			$this->elements[$element['id']] = $element;
		}

		private function CountElements()
		{
			return count($this->elements);
		}

		private function GetElement($id)
		{
			return $this->elements[$id];
		}

		private function GetNextElement()
		{
			if( $this->status == $this->CountElements())
				$this->status = 0;

			$element = $this->GetElement($this->status);
			$this->status++;
		}

		function AddSelect($name, $text, $values = array())
		{
			$element['type'] = 'select';
			$element['name'] = $name;
			$element['values'] = $values;
			$element['text']  = $text;
			$element['html_start'] = '<'. $element['type'] .' name="' .$element['name']. '">';
			$element['html_end'] = '</'. $element['type'] .'>';

			foreach($values as $k => $v)
				$element['values'][$k] = '<option value="' .$k. '" '. (isset($v[1]) && $v[1] == 'selected' ? 'selected="selected"' : '') .'>'. $this->text['BreezeMod_user_settings_'.$v[0]] .'</option>';

			return $this->AddElement($element);
		}

		function AddCheckBox($name,$value, $text, $checked = false)
		{
			$element['type'] = 'checkbox';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['checked'] = empty($checked) ? '' : 'checked="checked"';
			$element['text'] = $text;
			$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" id="'. $element['name'] .'" value="'. (int)$element['value'] .'" '. $element['checked'] .' class="input_check" />';

			return $this->AddElement($element);
		}

		function AddText($name,$value, $text, $size = false, $maxlength = false)
		{
			$element['type'] = 'text';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['text'] = $text;
			$element['size'] = empty($size) ? 'size="20"' : 'size="' .$size. '"';
			$element['maxlength'] = empty($maxlength) ? 'maxlength="20"' : 'maxlength="' .$maxlength. '"';
			$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" id="'. $element['name'] .'" value="'. $element['value'] .'" '. $element['size'] .' '. $element['maxlength'] .' class="input_text" />';

			return $this->AddElement($element);
		}

		function AddTextArea($name,$value, $text)
		{
			$element['type'] = 'textarea';
			$element['name'] = $name;
			$element['value'] = empty($value) ? '' : $value;
			$element['text'] = $text;
			$element['html'] = '<'. $element['type'] .' name="'. $element['name'] .'" id="'. $element['name'] .'">'. $element['value'] .'</'. $element['type'] .'>';

			return $this->AddElement($element);
		}

		function AddHiddenField($name,$value)
		{
			$element['type'] = 'hidden';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" id="'. $element['name'] .'" value="'. $element['value'] .'" />';

			return $this->AddElement($element);
		}

		function AddSubmitButton($value)
		{
			$element['type'] = 'submit';
			$element['value']= $this->text[$value];
			$element['html'] = '<input class="button_submit" type="'. $element['type'] .'"  value="'. $element['value'] .'" />';

			return $this->AddElement($element);
		}

		function AddHr()
		{
			$element['type'] = 'hr';
			$element['html'] = '<hr />';

			return $this->AddElement($element);
		}

		function Display()
		{
			$this->buffer = '<form action="'. $this->action .'" method="'. $this->method .'" id="'. $this->id_css .'" class="'. $this->class_css .'"  '. $this->onsubmit .' >';
			$this->buffer .= '<dl class="settings">';
			$element = $this->GetNextElement();

			foreach($this->elements as $el)
			{
				switch($el['type'])
				{
					case 'textarea':
					case 'checkbox':
					case 'text':
						$this->buffer .= '<dt>
							<span style="font-weight:bold;">'. $this->text['BreezeMod_user_settings_'. $el['text'][0]] .'</span>
							<br /><span class="smalltext">'. $this->text['BreezeMod_user_settings_'.$el['text'][1]] .'</span>
						</dt>
						<dd>
							'. $el['html'] .'
						</dd>';
						break;
					case 'select':
						$this->buffer .= '<dt>
							<span style="font-weight:bold;">'. $this->text['BreezeMod_user_settings_'.$el['text'][0]] .'</span>
							<br /><span class="smalltext">'. $this->text['BreezeMod_user_settings_'.$el['text'][1]] .'</span>
						</dt>
						<dd>
							'. $el['html_start'] .'';

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
				}
			}

			$this->buffer .= '</dl></form>';

			return $this->buffer;
		}
	}