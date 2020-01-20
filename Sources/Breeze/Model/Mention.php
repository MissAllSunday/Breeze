<?php

declare(strict_types=1);

use \Breeze\Entity\Member as MemberEntity;
use Breeze\Entity\Mention as MentionEntity;

class Mention extends Base
{
	public function userMention(string $match): array
	{
		require_once($this->_app['tools']->sourceDir . '/Subs-Member.php');

		$mention = [
		    'name' => '',
		    'id' => ''
		];

		if (empty($match))
			return $mention;

		$match = $this->db['htmltrim']($this->db['htmlspecialchars']($match), \ENT_QUOTES);

		if ($this->db['strlen']($match) >= 3)
			$match = $this->db['substr']($match, 0, 3);

		$allowedMembers = array_values(membersAllowedTo('breeze_beMentioned'));

		if (empty($allowedMembers))
			return $mention;

		$allowedMembers = (array) $allowedMembers;

		$result = $this->db['db_query'](
		    '',
		    'SELECT ' . implode(', ', MemberEntity::getColumns()) . '
			FROM {db_prefix}' . $this->getTableName() . '
			WHERE ' . MemberEntity::COLUMN_ID . ' IN({array_int:allowedMembers})
				AND ' . MemberEntity::COLUMN_MEMBER_NAME . ' LIKE {string:match} 
				OR ' . MemberEntity::COLUMN_REAL_NAME . ' LIKE {string:match}',
		    [
		        'match' => $match . '%',
		        'allowedMembers' => $allowedMembers
		    ]
		);

		while ($row = $this->db['db_fetch_assoc']($result))
			$mention[] = [
			    'name' => $row[MemberEntity::COLUMN_MEMBER_NAME],
			    'id' => (int) $row[MemberEntity::COLUMN_ID],
			];

		$this->db['db_free_result']($result);

		return $mention;
	}

	function insert(array $data, int $id = 0): int
	{
		// TODO: Implement insert() method.
	}

	function update(array $data, int $id = 0): array
	{
		// TODO: Implement update() method.
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
