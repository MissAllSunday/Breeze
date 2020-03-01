<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Repository\RepositoryInterface;

abstract class BaseService
{

	/**
	 * @var Settings
	 */
	protected $settings;

	/**
	 * @var Text
	 */
	protected $text;

	/**
	 * @var array
	 */
	protected $configVars = [];

	/**
	 * @var RepositoryInterface
	 */
	protected $repository;

	public function __construct(Settings $settings, Text $text, RepositoryInterface $repository)
	{
		$this->settings = $settings;
		$this->text = $text;
		$this->repository = $repository;
	}

	public function global(string $variableName)
	{
		return $GLOBALS[$variableName] ?? false;
	}

	public function setGlobal($globalName, $globalValue): void
	{
		$GLOBALS[$globalName] = $globalValue;
	}

	public function requireOnce(string $fileName, string $dir = ''): void
	{
		if (empty($fileName))
			return;

		$sourceDir = !empty($dir) ? $dir : $this->global('sourcedir');

		require_once($sourceDir . '/' . $fileName . '.php');
	}

	public function setTemplate(string $templateName): void
	{
		loadtemplate($templateName);
	}

	public function redirect(string $urlName): void
	{
		if(!empty($urlName))
			redirectexit($urlName);
	}
}
