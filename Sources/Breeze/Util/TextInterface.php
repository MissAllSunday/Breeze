<?php

declare(strict_types=1);


namespace Breeze\Util;

interface TextInterface
{
	public function getText(string $textKey): string;

	public function getSmfText(string $textKey): string;

	public function parserText(string $text, array $replacements = []): string;

	public function commaSeparated(string $dirtyString, string $type = 'alphanumeric'): string;

	public function normalizeString(string $string = ''): string;

	public function formatBytes(int $bytes, bool $showUnits = false): string;

	public function truncateText(string $string, int $limit = 30, string $break = ' ', string $pad = '...'): string;

	public function timeElapsed(int $timeInSeconds): string;

	public function setLanguage(string $languageName): void;
}
