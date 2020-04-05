<?php

declare(strict_types=1);

namespace Breeze\Model;

interface MentionModelInterface
{
	public function userMention(string $match): array;
}
