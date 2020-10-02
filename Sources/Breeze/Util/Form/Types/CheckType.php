<?php

declare(strict_types=1);

namespace Breeze\Util\Form\Types;

use Breeze\Breeze;

class CheckType extends ValueFormatter implements ValueFormatterInterface
{
	public function getConfigVar(string $settingName, string $settingType): array
	{
		return [
			$settingType,
			Breeze::PATTERN . $settingName,
			'subtext' => $this->getText($settingName . '_sub')
		];
	}

	function getCheckBox($params = []): string
	{
		// Kinda needs this...
		if (empty($params) || empty($params['name']))
			return '';

		$this->setParamValues($param);
		$param['type'] = 'checkbox';
		$param['value'] = 1;
		$param['checked'] = empty($param['checked']) ? '' : 'checked="checked"';
		$param['html'] = '<input type="' . $param['type'] . '" name="' . (!empty($this->_options['name']) ? $this->_options['name'] . '[' . $param['name'] . ']' : $param['name']) . '" id="' . $param['name'] . '" value="' . $param['value'] . '" ' . $param['checked'] . ' class="input_check" />';

		return template_breezeForm_Check($params);
	}
}
