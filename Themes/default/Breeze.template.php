<?php

declare(strict_types=1);

use Breeze\Breeze;

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */
function template_profile(): void
{
	echo '
	<div id="root" class="breeze_main_section" wallType="profile">
	</div>';
}

function template_wall(): void
{
	echo '
	<div id="root" class="breeze_main_section" wallType="general">
	</div>';
}

function template_showEditor(): void
{
	global $context;

	$template = function ($name, $type) use ($context) {
		$fileKey = 'smf_' . $name . '_' . $type;
		$type = $type === 'js' ? 'javascript' : $type;
		$fileUrl = $context[$type . '_files'][$fileKey]['fileUrl'];
		$seed =  $context[$type . '_files'][$fileKey]['options']['seed'] ?? '';
		$format = $type === 'css' ?
			"<link rel='stylesheet' href='%s'>" :
			"<script src='%s'></script>";

		return sprintf($format, $fileUrl . $seed);
	};

	$jsFiles = implode(\PHP_EOL, array_map(function ($name) use ($template) {
		return $template($name, 'js');
	}, [
		'jquery',
		'script',
		'theme',
		'editor',
		'sceditor_bbcode',
		'sceditor_smf',
	]));

	$cssFiles = implode(\PHP_EOL, array_map(function ($name) use ($template) {
		return $template($name, 'css');
	}, [
		'index',
		'responsive',
		'jquery_sceditor',
	]));

	echo '
<html>
	<head>
			' . $jsFiles . '
			' . $cssFiles . '
	</head>
	<body>
	';

	echo  template_control_richedit(Breeze::NAME, 'smileyBox_message', 'bbcBox_message');
	echo template_javascript(true);

	echo '
	</body>
</html>';
}
