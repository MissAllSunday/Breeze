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