<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

namespace Breeze\Service;

use Breeze\Breeze;

if (!defined('SMF'))
	die('No direct access...');

class FormService extends BaseService implements ServiceInterface
{
	private $elements = [];

	private $buffer = '';

	private $textPrefix = 'user_settings_';

	/**
	 * @var array
	 */
	private $options = ['name' => '', 'url' => '', 'title' => '', 'desc' => '',];

	public function setOptions(array $options): void
	{
		if (empty($options))
			return;

		$this->options = array_merge($this->options, $options);
	}

	public function setTextPrefix(string $prefix): void
	{
		$this->textPrefix = $prefix;
	}

	private function setText(string $text)
	{
		return $this->getText($this->textPrefix . $text);
	}

	private function addElement(array $element): void
	{
		$this->elements[] = $element;
	}

	private function getElement(string $id)
	{
		return $this->elements[$id];
	}

	protected function setParamValues(array &$param): void
	{
		if (empty($param['text']))
			$param['text'] = $param['name'];

		$param['text']  = !empty($param['fullText']) ?
			$param['fullText'] : $this->setText($param['text']);

		$param['desc']  = !empty($param['fullDesc']) ?
			$param['fullDesc'] : $this->setText($param['name'] . '_sub');
	}

	public function addSelect(array $param = []): void
	{
		if (!$this->isEmptyParam($param))
			return;

		$this->setParamValues($param);
		
		$param['type'] = 'select';
		$param['html_start'] = '<' . $param['type'] . ' name="' . 
			(!empty($this->options['name']) ? $this->options['name'] .
				'[' . $param['name'] . ']' : $param['name']) . '">';
		$param['html_end'] = '</' . $param['type'] . '>';

		foreach($values as $key => $value)
			$param['values'][$key] = '<option value="' . $key . '" ' . (isset($value[1]) && 'selected' == $value[1] ?
					'selected="selected"' : '') . '>' . $this->getText($value[0]) . '</option>';

		$this->addElement($param);
	}

	public function addCheckBox(array $param = []): self
	{
		if (!$this->isEmptyParam($param))
			return $this;

		$this->setParamValues($param);
		$param['type'] = 'checkbox';
		$param['value'] = 1;
		$param['checked'] = empty($param['checked']) ? '' : 'checked="checked"';
		$param['html'] = '<input type="' . $param['type'] . '" name="' . (!empty($this->options['name']) ?
				$this->options['name'] . '[' . $param['name'] . ']' : $param['name']) . '" id="' . $param['name'] .
			'" value="' . $param['value'] . '" ' . $param['checked'] . ' class="input_check" />';

		$this->addElement($param);

		return $this;
	}

	public function addText(array $param = []): self
	{
		if (!$this->isEmptyParam($param))
			return $this;

		$this->setParamValues($param);
		$param['type'] = 'text';
		$param['size'] = empty($param['size'] ) ? 'size="20"' : 'size="' . $param['size'] . '"';
		$param['maxlength'] = empty($param['maxlength']) ?
			'maxlength="20"' : 'maxlength="' . $param['maxlength'] . '"';
		$param['html'] = '<input type="' . $param['type'] . '" name="' . (!empty($this->options['name']) ?
				$this->options['name'] . '[' . $param['name'] . ']' : $param['name']) . '" id="' .
			$param['name'] . '" value="' . $param['value'] . '" ' . $param['size'] . ' ' . $param['maxlength'] .
			' class="input_text" />';

		$this->addElement($param);

		return $this;
	}

	public function addTextArea(array $param = []): self
	{
		if (!$this->isEmptyParam($param))
			return $this;

		$this->setParamValues($param);
		$param['type'] = 'textarea';
		$param['value'] = empty($param['value']) ? '' : $param['value'];

		$rows = 'rows="' . (!empty($param['size'] ) && !empty($param['size']['rows']) ?
				$param['size']['rows'] : 10) . '"';
		$cols = 'cols="' . (!empty($param['size'] ) && !empty($param['size']['cols']) ?
				$param['size']['cols'] : 40) . '"';

		$param['maxlength'] = 'maxlength="' . (!empty($param['size'] ) && !empty($param['size']['maxlength']) ?
				$param['size']['maxlength'] : 1024) . '"';

		$param['html'] = '<' . $param['type'] . ' name="' . (!empty($this->options['name']) ?
				$this->options['name'] . '[' . $param['name'] . ']' : $param['name']) . '" id="' . $param['name'] .
			'" ' . $rows . ' ' . $cols . ' ' . $param['maxlength'] . '>' .
			$param['value'] . '</' . $param['type'] . '>';

		$this->addElement($param);

		return $this;
	}

	public function addHiddenField(string $name, string $value): self
	{
		$param['type'] = 'hidden';
		$param['html'] = '
		<input type="' . $param['type'] . '" name="' . $name . '" id="' . $name . '" value="' . $value . '" />';

		$this->addElement($param);

		return $this;
	}

	public function addSessionField(): self
	{
		$context = $this->global('context');

		return $this->addHiddenField($context['session_var'], $context['session_id']);
	}

	public function addHr(): void
	{
		$param['type'] = 'hr';
		$param['html'] = '<br /><hr /><br />';

		$this->addElement($param);
	}

	public function addHTML(array $param = []): void
	{
		if (!$this->isEmptyParam($param))
			return;

		$this->setParamValues($param);
		$param['type'] = 'html';

		$this->addElement($param);
	}

	public function addButton(array $param = []): void
	{
		if (!$this->isEmptyParam($param))
			return;

		$this->setParamValues($param);
		$param['type'] = 'button';

		$this->addElement($param);
	}

	public function addSection(array $param = []): void
	{
		if (!$this->isEmptyParam($param))
			return;

		$this->setParamValues($param);
		$param['type'] = 'section';

		$this->addElement($param);
	}

	public function display()
	{
		global $context;

		$this->setTemplate(Breeze::NAME . 'Form');

		$context['form'] = [
			'options' => $this->options,
			'elements' => $this->elements,
		];

		return template_breeze_form();
	}

	protected function isEmptyParam($param): bool
	{
		return empty($param) || empty($param['name']);
	}
}
