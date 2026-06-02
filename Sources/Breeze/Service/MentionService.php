<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Traits\SettingsTrait;

class MentionService implements MentionServiceInterface
{
	use SettingsTrait;

	public function __construct(
		private BaseRepositoryInterface $userRepository,
		private ?AlertServiceInterface $alertService = null
	) {
		$this->requireOnce('Mentions');
	}

	public function isEnabled(): bool
	{
		return !empty($this->modSetting('enable_mentions')) && allowedTo('mention');
	}

	/**
	 * @return array{body: string, members: array}
	 */
	public function processBody(string $body, array $mentionIds = []): array
	{
		if (empty($mentionIds)) {
			return ['body' => $body, 'members' => []];
		}

		return $this->processBodyWithIds($body, $mentionIds);
	}

	/**
	 * Validates member IDs against the database, cross-verifies that @Name
	 * appears in the body (second-factor check), then rewrites to BBC.
	 *
	 * @return array{body: string, members: array}
	 */
	private function processBodyWithIds(string $body, array $mentionIds): array
	{
		$mentionIds = array_values(array_filter(array_map('intval', $mentionIds), static fn (int $id): bool => $id > 0));

		if (empty($mentionIds)) {
			return ['body' => $body, 'members' => []];
		}

		$userInfo = $this->userRepository->loadUsersInfo($mentionIds);

		$members = [];

		foreach ($userInfo as $id => $info) {
			$name = $info['name'] ?? '';

			if ($name === '') {
				continue;
			}

			if (stripos($body, '@' . $name) === false) {
				continue;
			}

			$body = str_ireplace(
				'@' . $name,
				'[member=' . $id . ']' . $name . '[/member]',
				$body
			);

			$members[$id] = ['id' => $id, 'real_name' => $name];
		}

		return ['body' => $body, 'members' => $members];
	}

	public function save(string $contentType, int $contentId, array $members, int $posterId, int $wallId = 0): void
	{
		if (empty($members)) {
			return;
		}

		\Mentions::insertMentions($contentType, $contentId, $members, $posterId);

		foreach ($members as $member) {
			if (($member['id'] ?? 0) === $posterId) {
				continue;
			}

			$this->alertService->send(AlertEntity::from([
				AlertEntity::ID_MEMBER         => $member['id'],
				AlertEntity::ID_MEMBER_STARTED => $posterId,
				AlertEntity::CONTENT_TYPE      => Breeze::PATTERN . 'mention',
				AlertEntity::CONTENT_ID        => $contentId,
				AlertEntity::CONTENT_ACTION    => Breeze::PATTERN . 'created',
				AlertEntity::IS_READ           => 0,
				AlertEntity::EXTRA             => [
					'content_type' => $contentType,
					'content_id'   => $contentId,
					'wall_id'      => $wallId,
				],
			]));
		}
	}

	public function update(string $contentType, int $contentId, array $members, int $posterId): void
	{
		\Mentions::modifyMentions($contentType, $contentId, $members, $posterId);
	}
}
