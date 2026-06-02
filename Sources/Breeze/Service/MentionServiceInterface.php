<?php

declare(strict_types=1);

namespace Breeze\Service;

interface MentionServiceInterface
{
	public const string CONTENT_TYPE_STATUS = 'breeze_status';

	public const string CONTENT_TYPE_COMMENT = 'breeze_comment';

	/**
	 * Returns true when SMF's enable_mentions setting is on and the current
	 * user has the 'mention' permission.
	 */
	public function isEnabled(): bool;

	/**
	 * Rewrites @Name occurrences to [member=id]Name[/member] BBC and returns
	 * the verified member list alongside the transformed body.
	 *
	 * When $mentionIds is non-empty (provided by the frontend), members are
	 * looked up by ID and the @Name in the body is used as a second-factor
	 * cross-verification — preventing ID manipulation via network interception.
	 *
	 * When $mentionIds is empty, mention processing is skipped entirely.
	 *
	 * @param  array $mentionIds  Validated member IDs supplied by the client.
	 * @return array{body: string, members: array}
	 */
	public function processBody(string $body, array $mentionIds = []): array;

	/**
	 * Persists mention rows and sends in-app alerts for a newly created piece
	 * of content. $wallId is used to build the alert link back to the wall.
	 *
	 * @param array $members  Verified member array from processBody()
	 */
	public function save(string $contentType, int $contentId, array $members, int $posterId, int $wallId = 0): void;

	/**
	 * Diffs existing mention rows against the new member list and
	 * inserts/removes accordingly. Use when content is edited.
	 *
	 * @param array $members  Verified member array from processBody()
	 */
	public function update(string $contentType, int $contentId, array $members, int $posterId): void;
}
