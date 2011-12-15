<?php

/**
 * Breeze_
 * 
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
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
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

	class Breeze_Form
	{
		var $method;
		var $action;
		var $name;
		var $id_css;
		var $class;
		array $elements;
		var $status;

		function __construct($method, $action, $id_css, $name, $class_css)
		{
			global $scripturl;

			$this->action = . $scripturl . '?action=' . $action;
			$this->method = $method;
			$this->id_css= $id;
			$this->name = $name;
			$this->class_css = $class_css;
			$elements = array();
			$this->status = 0;
		}

		private function AddElement($element)
		{
			$element['id'] = CountElements()++;
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

			$element = $this->GetElement($this->getStatus);
			$this->status++;
		}

		function AddSelect($name, $text, $values = array())
		{
			$element['type'] = 'select';
			$element['name'] = $name;
			$element['values'] = array();
			$element['text']  = $text;
			$element['html_start'] = '<'. $element['type'] .' name="' .$element['name']. '">';
			$element['html_end'] = '</'. $element['type'] .'>';

			foreach($values as $k => $v)
			{
				$element['values'][$k] = '<option value="' .$k. '">' .$v. '</option>';
			}

			return $this->AddElement($element);
		}

		function AddHiddenField($name,$value)
		{
			$element['type'] = 'hidden';
			$element['name'] = $name;
			$element['value'] = $value;
			$element['html'] = '<input type="'. $element['type'] .'" name="'. $element['name'] .'" value="'. $element['value'] .'">';

			return $this->AddElement($element);
		}

		function AddSubmitButton($name,$value)
		{
			$element['type'] = 'submit';
			$element['name'] = $name;
			$element['value']= $value;
			$element['code'] = '<input class="'. $element['type'] .'" type="'. $element['type'] .'" name="'. $element['name'] .'" value="'. $element['value'] .'">';

			return $this->AddElement($element);
		}
	}