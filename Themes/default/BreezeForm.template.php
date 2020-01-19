<?php

declare(strict_types=1);

/**
 * Breeze.template.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2015, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breeze_form()
{
	global $context;

	$return = '';

	$return .= '
<form action="' . $context['form']['options']['url'] . '" method="post" accept-charset="' . $context['form']['options']['character_set'] . '" name="' . $context['form']['options']['name'] . '" id="' . $context['form']['options']['name'] . '">';

		// Any title and/or description?
		if (!empty($context['form']['options']['title']))
			$return .= '
	<div class="cat_bar">
		<h3 class="catbg profile_hd">
				' . $context['form']['options']['title'] . '
		</h3>
	</div>';

		if (!empty($context['form']['options']['desc']))
			$return .= '
	<p class="information">
		' . $context['form']['options']['desc'] . '
	</p>';

		$return .= '
	<div class="windowbg2">
		<div class="content">
			<dl class="settings">';

		foreach($context['form']['elements'] as $el)
		{
			switch($el['type'])
			{
				case 'textarea':
				case 'checkbox':
				case 'text':
					$return .= '
				<dt>
					<span style="font-weight:bold;">' . $el['text'] . '</span>
					<br /><span class="smalltext">' . $el['desc'] . '</span>
				</dt>
				<dd>
					<input type="hidden" name="' . (!empty($context['form']['options']['name']) ? $context['form']['options']['name'] . '[' . $el['name'] . ']' : $el['name']) . '" value="0" />' . $el['html'] . '
				</dd>';
					break;
				case 'select':
					$return .= '
				<dt>
					<span style="font-weight:bold;">' . $el['text'] . '</span>
					<br /><span class="smalltext">' . $el['desc'] . '</span>
				</dt>
				<dd>
					<input type="hidden" name="' . (!empty($context['form']['options']['name']) ? $context['form']['options']['name'] . '[' . $el['name'] . ']' : $el['name']) . '" value="0" />' . $el['html_start'] . '';

					foreach($el['values'] as $k => $v)
						$return .= $v . '';

					$return .= $el['html_end'] . '
				</dd>';
					break;
				case 'hidden':
				case 'submit':
					$return .= '
				<dt></dt>
				<dd>
					' . $el['html'] . '
				</dd>';
					break;
				case 'hr':
					$return .= '
				</dl>
					' . $el['html'] . '
				<dl class="settings">';
					break;
				case 'html':
					$return .= '
				<dt>
					<span style="font-weight:bold;">' . $el['text'] . '</span>
					<br /><span class="smalltext">' . $el['desc'] . '</span>
				</dt>
				<dd>
					' . $el['html'] . '
				</dd>';
					break;
				case 'section':
				$return .= '
				</dl>
				<div class="cat_bar">
					<h3 class="catbg">' . $el['text'] . '</h3>
				</div>
				<br />
				<dl class="settings">';
					break;
			}
		}

		$return .= '
			</dl>';

		// Any buttons?
		foreach($context['form']['elements'] as $el)
			if ($el['type'] == 'button')
				$return .= '<input type="submit" name="' . $el['name'] . '" value="' . $el['text'] . '" class="button_submit"/>';

		// Close it.
		$return .= '
		</div>
	</div>
	<br />
</form>';

	return $return;
}
