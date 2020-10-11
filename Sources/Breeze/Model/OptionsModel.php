<?php

declare(strict_types=1);


namespace Breeze\Model;

class OptionsModel extends BaseModel implements OptionsModelInterface
{
	public const SAVED_AS_JSON = ['moodHistory'];

	public function insert(array $data, int $id = 0): int
	{
		if (empty($data) || empty($userId))
			return 0;

		$inserts = [];

		foreach ($data as $settingVariable => $settingValue)
		{
			// Does the value needs to be encoded?
			if (in_array($var, $this->_needJSON))
				$val = !empty($val) ? json_encode($val) : '';

			$inserts[] = [$userID, $var, $val];
		}

		if (!empty($inserts))
			$smcFunc['db_insert'](
				'replace',
				'{db_prefix}' . ($this->_tables['options']['table']),
				['member_id' => 'int', 'variable' => 'string-255', 'value' => 'string-65534'],
				$inserts,
				['member_id']
			);

		// Force getting the new settings.
		return $this->getUserSettings($userID);
	}

	public function update(array $data, int $id = 0): array
	{
		return [];
	}

	public function getTableName(): string
	{
		return '';
	}

	public function getColumnId(): string
	{
		return '';
	}

	public function getColumns(): array
	{
		return [];
	}
}
