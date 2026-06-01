<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Traits\SettingsTrait;

class MentionService implements MentionServiceInterface
{
	use SettingsTrait;

	public function isEnabled(): bool
	{
		return !empty($this->modSetting('enable_mentions')) && allowedTo('mention');
	}

	/**
	 * @return array{body: string, members: array}
	 */
	public function processBody(string $body): array
	{
		$this->requireOnce('Mentions');

		$members = \Mentions::getMentionedMembers($body);

		if (!empty($members)) {
			$body    = \Mentions::getBody($body, $members);
			$members = \Mentions::verifyMentionedMembers($body, $members);
		}

		return ['body' => $body, 'members' => $members];
	}

	public function save(string $contentType, int $contentId, array $members, int $posterId): void
	{
		if (empty($members)) {
			return;
		}

		$this->requireOnce('Mentions');
		\Mentions::insertMentions($contentType, $contentId, $members, $posterId);
	}

	public function update(string $contentType, int $contentId, array $members, int $posterId): void
	{
		$this->requireOnce('Mentions');
		\Mentions::modifyMentions($contentType, $contentId, $members, $posterId);
	}
}
