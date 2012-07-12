<?php

/**
 * BreezeDB
 *
 * The purpose of this file is to perform the queries made by breeze, it only executes, no logic here.
 * @package Breeze mod
 * @version 1.0 Beta 2
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

class BreezeDB
{
	protected $_dataResult = array();
	protected $_params = array();
	protected $_table;
	protected $_data;

	public function __construct($table)
	{
		$this->_table = isset($table) ? '{db_prefix}'.$table : null;
	}

	public function params($params, $data = null)
	{
		if(!is_array($params))
			return false;

		$this->_params['rows'] = isset($params['rows']) ? trim($params['rows']) : '';
		$this->_params['where'] = isset($params['where']) ? 'WHERE '. trim($params['where']) : '';
		$this->_params['whereAnd'] = isset($params['and']) ? 'AND '. trim($params['and']) : '';
		$this->_params['limit'] = isset($params['limit']) ? 'LIMIT '. trim($params['limit']) : '';
		$this->_params['left'] = isset($params['left_join']) ? 'LEFT JOIN '. trim($params['left_join']) : '';
		$this->_params['order'] = isset($params['order']) ? 'ORDER BY '. trim($params['order']) : '';
		$this->_params['set'] = isset($params['set']) ? 'SET '. trim($params['set']) : '';
		$this->_data = !is_array($data) ? array($data) : $data;
	}

	public function getData($key = null, $single = false)
	{
		global $smcFunc;

		if ($key)
			$this->key = $key;

		$params = $this->_params;

		$query = $smcFunc['db_query']('', '
			SELECT '. $this->_params['rows'] .'
			FROM '. $this->_table .'
			'. $this->_params['left'] .'
			'. $this->_params['where'] .'
				'. $this->_params['whereAnd'] .'
			'. $this->_params['order'] .'
			'. $this->_params['limit'] .'
			',
			$this->_data
		);

		if (!$query)
			$this->_dataResult = array();

		if($single)
			while ($row = $smcFunc['db_fetch_assoc']($query))
				$this->_dataResult = $row;

		if ($key)
			while($row = $smcFunc['db_fetch_assoc']($query))
				$this->_dataResult[$row[$this->key]] = $row;

		else
			while($row = $smcFunc['db_fetch_assoc']($query))
				$this->_dataResult[] = $row;

		$smcFunc['db_free_result']($query);
	}

	public function dataResult()
	{
		return $this->_dataResult;
	}

	public function updateData()
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			UPDATE '.$this->_table .'
			'.$this->set .'
			'.$this->_params['where'] .'
			'.$this->_params['order'] .'
			'.$this->_params['limit'] .'
			',
			$this->_data
		);
	}

	public function deleteData()
	{
		global $smcFunc;

		$smcFunc['db_query']('', '
			DELETE FROM '.$this->_table .'
			'.$this->_params['where'] .'
			'.$this->_params['order'] .'
			'.$this->_params['limit'] .'
			',
			$this->_data
		);
	}

	public function insertData($data, $values, $indexes)
	{
		if(is_null($values) || is_null($indexes) || is_null($data))
			return false;

		global $smcFunc;

		$this->indexes = isset($params['indexes']) ? array($params['indexes']) : null;
		$this->values = !is_array($values) ? array($values) : $values;
		$this->_data = !is_array($data) ? array($data) : $data;

		$smcFunc['db_insert']('replace',
			''.$this->_table .'',
			$this->_data ,
			$this->values ,
			$this->indexes
		);
	}

	public function count($params = null, $data = null)
	{
		global $smcFunc;

		if(is_null($params))
			$params = array();

		if(is_null($data))
			$data = array();

		$this->_data = !is_array($data) ? array($data) : $data;
		$this->_params['where'] = isset($params['where']) ? 'WHERE '.trim($params['where']) : null;
		$this->_params['left'] = isset($params['left_join']) ? 'LEFT JOIN '.trim($params['left_join']) : null;

		$request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM '.$this->_table .'
			' . $this->_params['where'] . '
			' . $this->_params['left'] . '
			',
			$this->_data
		);

		list ($count) = $smcFunc['db_fetch_row']($request);
		$smcFunc['db_free_result']($request);

		return $count;
	}
}