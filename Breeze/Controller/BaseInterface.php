<?php

declare(strict_types=1);


namespace Breeze\Controller;

interface BaseInterface
{
	public function do();

	public function create();

	public function update();

	public function delete();
}
