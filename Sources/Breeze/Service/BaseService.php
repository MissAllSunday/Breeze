<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Repository\RepositoryInterface;
use Breeze\Traits\Text;

abstract class BaseService
{
	use Text;

	/**
	 * @var RepositoryInterface
	 */
	protected $repository;

	public function __construct(RepositoryInterface $repository)
	{
		$this->repository = $repository;
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
