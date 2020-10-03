<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breezeForm_Check(array $checkOptions): string
{
	return '
	<input type="checkbox" name="' . $checkOptions['formName'] . '[' . $checkOptions['name'] . ']' . '" id="' .
		$checkOptions['name'] . '" value="1" ' . (empty($checkOptions ['checked']) ? '' : 'checked="checked"') .
		' class="input_check" />';
}

function template_breezeForm_TextArea(array $textAreaOptions): string
{
	return '
	<textarea name="' . $textAreaOptions['formName'] . '[' . $textAreaOptions['name'] . ']' .
		'" id="' . $textAreaOptions['name'] . '" rows="10" cols="40"  maxlength="1024">' .
		$textAreaOptions['value'] .
		'</textarea>';

}

function template_breezeForm_Text(array $textOptions): string
{
	return '<input type="text" name="' . $textOptions['formName'] . '[' . $textOptions['name'] . ']' . '" id="' .
		$textOptions['name'] . '" value="' . $textOptions['value'] . '" size="20" maxlength="20" class="input_text" />';
}


function template_breezeForm_Select(array $select): string
{

	if(empty($select['options']))
		return '';
	
	$selectString = '<select name="' . $select['formName'] . '[' . $select['name'] . ']' . '">';

	foreach($select['options'] as $optionKey => $optionValue)
		$selectString .= '<option value="' . $optionKey . '" ' .
			(isset($optionValue[1]) && $optionValue[1] === 'selected' ? 'selected="selected"' : '') . '>' .
			$optionValue[0] . '</option>';

	$selectString .= '</select>';

	return $selectString;
}