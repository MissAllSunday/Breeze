<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breezeForm_Check(array $options): string
{
	return '
	<input type="checkbox" name="' . $options['formName'] . '[' . $options['name'] . ']' . '" id="' .
		$options['name'] . '" value="1" ' . (empty($options['checked']) ? '' : 'checked="checked"') .
		' class="input_check" />';
}