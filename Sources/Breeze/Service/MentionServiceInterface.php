<?php

declare(strict_types=1);

namespace Breeze\Service;

interface MentionServiceInterface
{
	/**
	 * Returns true when SMF's enable_mentions setting is on and the current
	 * user has the 'mention' permission.
	 */
	public function isEnabled(): bool;

	/**
	 * Rewrites @Name occurrences to [member=id]Name[/member] BBC in the body
	 * and returns the verified list of mentioned members alongside the
	 * transformed body.
	 *
	 * @return array{body: string, members: array}
	 */
	public function processBody(string $body): array;

	/**
	 * Persists mention rows for a newly created piece of content.
	 *
	 * @param array $members  Verified member array from processBody()
	 */
	public function save(string $contentType, int $contentId, array $members, int $posterId): void;

	/**
	 * Diffs existing mention rows against the new member list and
	 * inserts/removes accordingly. Use when content is edited.
	 *
	 * @param array $members  Verified member array from processBody()
	 */
	public function update(string $contentType, int $contentId, array $members, int $posterId): void;
}
