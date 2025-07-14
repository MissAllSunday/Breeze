<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

use Breeze\Entity\MemberEntity;
use Breeze\Entity\OptionsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\BaseRepository;
use Breeze\Util\Json;
use Breeze\Util\Validate\DataNotFoundException;

class SettingsRepository extends BaseRepository implements SettingsRepositoryInterface
{
	public const array JSON_VALUES = ['cover', 'petitionList'];

	public const array ARRAY_VALUES = ['blockListIDs'];

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $id): UserSettingsEntity
	{
		$userSettings = $this->getCache(sprintf(OptionsEntity::CACHE_NAME, $id));

		if ($userSettings === []) {
			$result = $this->dbClient->query(
				'SELECT op.' . (implode(', op.', OptionsEntity::getColumns())) . ',
			mem.' . (implode(', mem.', MemberEntity::getColumns())) . '
			FROM {db_prefix}' . OptionsEntity::TABLE . ' AS op
				LEFT JOIN {db_prefix}' . MemberEntity::TABLE . '
				AS mem ON (mem.' . MemberEntity::ID . ' = {int:userId})
			WHERE ' . MemberEntity::ID . ' = {int:userId}',
				[
					'userId' => $id,
				]
			);

			if ($result === false) {
				throw new DataNotFoundException('error_no_user_settings');
			}

			$userData = [];
			while ($row = $this->dbClient->fetchAssoc($result)) {
				$userData[$row[OptionsEntity::VARIABLE]] = $row[OptionsEntity::VALUE];

				$userData += [
					UserSettingsEntity::BUDDIES => empty($row[MemberEntity::BUDDY_LIST]) ?
						'' : $row[MemberEntity::BUDDY_LIST],
					UserSettingsEntity::BLOCK_LIST => empty($row[MemberEntity::IGNORE_LIST]) ?
						'' : $row[MemberEntity::IGNORE_LIST],
				];
			}

			$this->dbClient->freeResult($result);
			$userSettings = UserSettingsEntity::from($userData);
			$this->setCache(sprintf(OptionsEntity::CACHE_NAME, $id), $userSettings);
		}

		return $userSettings;
	}

	public function insert(array $userSettings, $userId): bool
	{
		$toInsert = [];
		$mergedValues = array_merge(UserSettingsEntity::getDefaultValues(), $userSettings);

		foreach ($mergedValues as $name => $value) {
			if (in_array($name, self::JSON_VALUES)) {
				$value = empty($value) ? '' : Json::encode($value);
			}

			$toInsert[] = [$userId, $name, $value];
		}

		if ($toInsert === []) {
			return false;
		}

		$result = $this->dbClient->replace(
			OptionsEntity::TABLE,
			[
				OptionsEntity::MEMBER_ID => 'int',
				OptionsEntity::VARIABLE => 'string',
				OptionsEntity::VALUE => 'string',
			],
			$toInsert,
			OptionsEntity::MEMBER_ID
		);

		if ($result !== 0) {
			$this->setCache(sprintf(OptionsEntity::CACHE_NAME, $userId), null);
		}

		return $result !== 0;
	}

	public function getTableName(): string
	{
		return MemberEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return MemberEntity::ID;
	}

	public function getColumns(): array
	{
		return MemberEntity::getColumns();
	}

	public function getColumnPosterId(): string
	{
		return UserSettingsEntity::WALL;
	}
}
