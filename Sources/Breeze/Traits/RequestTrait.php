<?php

declare(strict_types=1);


namespace Breeze\Traits;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Util\Json;

trait RequestTrait
{
	private mixed $request;

	public function init(): void
	{
		$this->request = $_REQUEST;
	}

	public function getData(array $data = []): array
	{
		$request = $_SERVER['REQUEST_METHOD'] === 'GET' ?
			$_GET : Json::decode(file_get_contents('php://input'))['data'];

		return array_filter($data === [] ? $this->sanitize($request) :
			$data);
	}

	public function getDataFromGet(): array
	{
		return $this->sanitize($_GET);
	}

	public function getRequest(string $variableName, mixed $defaultValue = false): mixed
	{
		$this->init();

		return empty($this->request[$variableName]) ?
			$defaultValue : $this->sanitize($this->request[$variableName]);
	}

	public function setPost(string $variableName, $variableValue): void
	{
		$_POST[$variableName] = $variableValue;
	}

	public function setGet(string $variableName, mixed $variableValue): void
	{
		$_GET[$variableName] = $variableValue;
	}

	public function isRequestSet(string $variableName): bool
	{
		$this->init();

		return isset($this->request[$variableName]);
	}

	public function sanitize(mixed $variable, string $name = ''): mixed
	{
		$smcFunc = $this->getSmcFunc();

		if (is_array($variable)) {
			foreach ($variable as $key => $variableValue) {
				$variable[$key] = $this->sanitize($variableValue, $key);
			}

			return array_filter($variable);
		}

		$var = $smcFunc['htmlspecialchars'](
			$smcFunc['htmltrim']((string)$variable),
			\ENT_QUOTES
		);

		if (is_numeric($var)) {
			// Should the param is required to be a string?
			$stringRequired = in_array($name, [StatusEntity::BODY, CommentEntity::BODY], true);

			$var = $stringRequired ? (string) $var : (int) $var;
		}

		if (empty($var)) {
			return false;
		}

		return $var;
	}

	private function getSmcFunc(): array
	{
		return $GLOBALS['smcFunc'];
	}
}
