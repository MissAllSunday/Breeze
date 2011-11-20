<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

function template_post_status()
{
	global  $context;
	
	switch ($context['breeze']['ok'])
	{
		case 'error':
			echo 'error_';
			break;
		case '':
			echo 'error_';
			break;
		case 'deleted':
			echo 'deleted_';
			break;
		case 'ok':
			echo $context['breeze']['post']['data'];
			break;
		default:
			echo 'error_';
	}

}
