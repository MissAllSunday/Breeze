<?php

namespace Breeze\Util\Form;

interface ValueFormatterInterface
{
	public function getConfigVar(array $setting): array;
}