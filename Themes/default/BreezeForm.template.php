<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breezeForm_Check(array $checkOptions, array $formOptions): string
{
	return '
	<input type="hidden" name="' . $formOptions['name'] . '[' . $checkOptions['name'] . ']' . '" value="0" />
	<input type="checkbox" name="' . $formOptions['name'] . '[' . $checkOptions['name'] . ']' . '" id="' .
		$checkOptions['name'] . '" value="1" ' . (empty($checkOptions ['value']) ? '' : 'checked="checked"') .
		' class="input_check" />';
}

function template_breezeForm_TextArea(array $textAreaOptions, array $formOptions): string
{
	return '
	<input type="hidden" name="' . $formOptions['name'] . '[' . $textAreaOptions['name'] . ']' . '" value="0" />
	<textarea name="' . $formOptions['name'] . '[' . $textAreaOptions['name'] . ']' .
		'" id="' . $textAreaOptions['name'] . '" rows="10" cols="40"  maxlength="1024">' .
		$textAreaOptions['value'] .
		'</textarea>';

}

function template_breezeForm_Text(array $textOptions, array $formOptions): string
{
	return '
	<input type="hidden" name="' . $formOptions['name'] . '[' . $textOptions['name'] . ']' . '" value="0" />
	<input type="text" name="' . $formOptions['name'] . '[' . $textOptions['name'] . ']' . '" id="' .
		$textOptions['name'] . '" value="' . $textOptions['value'] . '" size="20" maxlength="20" class="input_text" />';
}

function template_breezeForm_Select(array $select, array $formOptions): string
{
	if(empty($select['value']))
		return '';

	$selectString = '
	<input type="hidden" name="' . $formOptions['name'] . '[' . $select['name'] . ']' . '" value="0" />
	<select name="' . $formOptions['name'] . '[' . $select['name'] . ']' . '">';

	foreach($select['options'] as $optionKey => $optionValue)
		$selectString .= '<option value="' . $optionKey . '" ' .
			(isset($optionValue[1]) && $optionValue[1] === 'selected' ? 'selected="selected"' : '') . '>' .
			$optionValue[0] . '</option>';

	$selectString .= '</select>';

	return $selectString;
}

function template_breezeForm_Desc(array $elementOptions): string
{
	if (empty($elementOptions) ||
		empty($elementOptions['text']) ||
		empty($elementOptions['desc']))
		return '';

	return '
	<span style="font-weight:bold;">
		'. $elementOptions['text'] .'
	</span>
	<br />
	<span class="smalltext">
		'. $elementOptions['desc'] .'
	</span>';
}

function template_breezeForm_Display($formOptions): string
{
	global $txt;

	$return = '';

	$return .= '
<form action="'. $formOptions['url'] .'" 
	method="post" 
	accept-charset="UTF-8" 
	name="'. $formOptions['name'] .'" 
	id="'. $formOptions['name'] .'">';

	if (!empty($formOptions['title']))
		$return .= '
	<div class="cat_bar">
		<h3 class="catbg profile_hd">
				'. $formOptions['title'] .'
		</h3>
	</div>';

	if (!empty($formOptions['desc']))
		$return .= '
	<p class="information">
		'. $formOptions['desc'] .'
	</p>';

	$return .= '
	<div class="windowbg2">
		<div class="content">
			<dl class="settings">';

	foreach($formOptions['elements'] as $element)
	{
		$templateCall = 'template_breezeForm_'. ucfirst($element['type']);
		$return .= '
				<dt>
					'. template_breezeForm_Desc($element) .'
				</dt>
				<dd>
					'. $templateCall($element, $formOptions) .'
				</dd>';
	}

	$return .= '
			</dl>
		</div>
	</div>
	<input type="submit" name="save" value="'. $txt['Breeze_user_settings_submit'] .'" class="button floatright">
</form>';

	return $return;
}