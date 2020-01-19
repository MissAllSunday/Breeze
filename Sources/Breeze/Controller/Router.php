<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;

class Router
{
	/**
	 * @var Breeze
	 */
	protected $breeze;

	/**
	 */
	public function call(Breeze $breeze)
	{
		$this->breeze = $breeze;
	}
}