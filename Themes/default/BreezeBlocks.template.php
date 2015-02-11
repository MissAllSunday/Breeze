<?php

/**
 * Breeze.template.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

function template_breeze_buddies()
{
	global $context, $txt;

	// Buddies
	if (!empty($context['Breeze']['settings']['owner']['buddies']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				'. $txt['Breeze_tabs_buddies'] .'
			</h3>
		</div>';

		echo '
		<div class="information">
			<div class="BreezeList">';

		if (!empty($context['member']['buddies']))
			breeze_user_list($context['member']['buddies'], 'buddy');

		// No buddies :(
		else
			echo $txt['Breeze_user_modules_buddies_none'];

			echo '
			</div>
		</div>';
	}
}

function template_breeze_visitors()
{
	global $context, $txt;

	if (!empty($context['Breeze']['settings']['owner']['visitors']))
	{

		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				'. $txt['Breeze_tabs_views'] .'
			</h3>
		</div>';

		echo '
		<div class="information">
			<div class="BreezeList">';

		if (!empty($context['Breeze']['views']))
			breeze_user_list($context['Breeze']['views'], 'visitors');

		// No visitors :(
		else
			echo $txt['Breeze_user_modules_visitors_none'];

		echo '
			</div>
		</div>';
	}
}
