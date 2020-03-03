<?php

declare(strict_types=1);

namespace Breeze\Model;

use \Breeze\Entity\MemberEntity as MemberEntity;
use Breeze\Entity\MentionEntity as MentionEntity;

class MentionModel extends BaseModel
{
	public function userMention(string $match): array
	{
		// @todo figure what to do with requiring SMF files
		require_once($this->_app['tools']->sourceDir . '/Subs-Member.php');

		$mention = [
		    'name' => '',
		    'id' => ''
		];

		if (empty($match))
			return $mention;

		// @todo create sanitize/request service or something like that
		$match = $this->db['htmltrim']($this->db['htmlspecialchars']($match), \ENT_QUOTES);

		if ($this->db['strlen']($match) >= 3)
			$match = $this->db['substr']($match, 0, 3);

		$allowedMembers = array_values(membersAllowedTo('breeze_beMentioned'));

		if (empty($allowedMembers))
			return $mention;

		$result = $this->db['db_query'](
		    '',
		    'SELECT ' . implode(', ', MemberEntity::getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE ' . MemberEntity::COLUMN_ID . ' IN({array_int:allowedMembers})
				AND ' . MemberEntity::COLUMN_MEMBER_NAME . ' LIKE {string:match} 
				OR ' . MemberEntity::COLUMN_REAL_NAME . ' LIKE {string:match}',
		    [
		        'match' => $match . '%',
		        'allowedMembers' => array_map('intval', $allowedMembers)
		    ]
		);

		while ($row = $this->db->fetchAssoc($result))
			$mention[] = [
			    'name' => $row[MemberEntity::COLUMN_MEMBER_NAME],
			    'id' => (int) $row[MemberEntity::COLUMN_ID],
			];

		$this->db->freeResult($result);

		return $mention;
	}

	function insert(array $data, int $id = 0): int
	{
		return 1;
	}

	function update(array $data, int $id = 0): array
	{
		return [];
	}

	function getTableName(): string
	{
		return MentionEntity::TABLE;
	}

	function getColumnId(): string
	{
		return MentionEntity::COLUMN_CONTENT_ID;
	}

	function getColumns(): array
	{
		return MentionEntity::getColumns();
	}
}
