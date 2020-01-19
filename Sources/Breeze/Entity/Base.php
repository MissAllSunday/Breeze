<?php

declare(strict_types=1);


namespace Breeze\Entity;


abstract class Base
{
	abstract function getName(): string;
	abstract function getColumns(): array;
}