<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Repository\RepositoryInterface;
use Breeze\Traits\SettingsTrait;
use Breeze\Traits\TextTrait;

abstract class BaseService implements ServiceInterface
{
	use SettingsTrait;
	use TextTrait;

	/**
	 * @var RepositoryInterface
	 */
	protected $repository;

	public function __construct(RepositoryInterface $repository)
	{
		$this->repository = $repository;
	}
}
