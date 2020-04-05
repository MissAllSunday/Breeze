<?php

declare(strict_types=1);

namespace Breeze\Model;

interface StatusModelInterface extends BaseModelInterface
{
	public function getColumnPosterId(): string;
}
