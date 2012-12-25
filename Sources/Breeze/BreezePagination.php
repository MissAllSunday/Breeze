<?php

/**
 * BreezePagination
 *
 * The purpose of this file is to create a pagination from an array of items
 * @package Breeze mod
 * @version 1.0 Beta 3
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
 * The Original Code is http://www.codehive.net/PHP-Array-Pagination-10.html.
 *
 * The Initial Developer of the Original Code is
 * Sergey Gurevich.
 * Portions created by the Initial Developer are Copyright (C) 2009
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 * Jessica González
 */

class BreezePagination
{
	public function __construct($array, $page = 1, $link_prefix = false, $link_suffix = false, $limit_page = 20, $limit_number = 10)
	{
		if (empty($array))
			$this->usedArray = array();

		else
		{
			$this->usedArray = $array;
			$this->page = !empty($page) || !$limit_page ? $page : 1;
			$this->linkPrefix = !empty($link_prefix) ? $link_prefix : '';
			$this->linkSufflix = !empty($link_sufflix) ? $link_sufflix : '';
			$this->limitPage = !empty($limit_page) ? $limit_page : 20;
			$this->limitNumber = !empty($limit_number) ? $limit_number : 20;
			$this->panel = '';
			$this->output = array();
			$this->page_cur = '';
		}
	}

	public function PaginationArray()
	{
		$this->num_rows = count($this->usedArray);

		if (!$this->num_rows or $this->limitPage >= $this->num_rows)
		{
			$this->pagtrue = false;
			return;
		}

		$this->num_pages = ceil($this->num_rows / $this->limitPage);
		$this->page_offset = ($this->page - 1) * $this->limitPage;

		/* Calculating the first number to show */
		if ($this->limitNumber)
		{
			$this->limit_number_start = $this->page - ceil($this->limitNumber / 2);
			$this->limit_number_end = ceil($this->page + $this->limitNumber / 2) - 1;

			if ($this->limit_number_start < 1)
				$this->limit_number_start = 1;

			//In case if the current page is at the beginning.
			$this->dif = ($this->limit_number_end - $this->limit_number_start);

			if ($this->dif < $this->limitNumber)
				$this->limit_number_end = $this->limit_number_end + ($this->limitNumber - ($this->dif + 1));

			if ($this->limit_number_end > $this->num_pages)
				$this->limit_number_end = $this->num_pages;

			//In case if the current page is at the ending.
			$this->dif = ($this->limit_number_end - $this->limit_number_start);

			if ($this->limit_number_start < 1)
				$this->limit_number_start = 1;
		}

		else
		{
			$this->limit_number_start = 1;
			$this->limit_number_end = $this->num_pages;
		}

		/* Let's generate the panel */
		$this->GeneratePageLinks();
		$this->NavigationArrows();
		$this->panel = trim($this->panel);

		$this->output['panel'] = $this->panel; //Panel HTML source.
		$this->output['offset'] = $this->page_offset; //Current page number.
		$this->output['limit'] = $this->limitPage; //Number of resuts per page.
		$this->output['array'] = array_slice($this->usedArray, $this->page_offset, $this->limitPage, true); //Array of current page results.

		$this->pagtrue = true;
	}

	/* Generating page links. */
	private function GeneratePageLinks()
	{
		global $scripturl;

		for ($i = $this->limit_number_start; $i <= $this->limit_number_end; $i++)
		{
			$this->page_cur = '<a href='. $this->linkPrefix . $i . $this->linkSufflix. '>'. $i .'</a>';

			if ($this->page == $i)
				$this->page_cur = '<strong>'. $i .'</strong>';

			else
				$this->page_cur = '<a href='. $scripturl .''. $this->linkPrefix . $i . $this->linkSufflix .'>'. $i .'</a>';

			$this->panel .= ' <span>'. $this->page_cur .'</span>';
		}
	}

	/* Navigation arrows. */
	private  function NavigationArrows()
	{
		global $scripturl;

		if ($this->limit_number_start > 1)
			$this->panel = '<strong><a href="'. $scripturl .''. $this->linkPrefix . (1) . $this->linkSufflix .'">&lt;&lt;</a>  <a href="'. $this->linkPrefix . ($this->page - 1) . $this->linkSufflix .'">&lt;</a></strong>'. $this->panel;

		if ($this->limit_number_end < $this->num_pages)
			$this->panel = $this->panel .' <strong><a href="'. $scripturl .''. $this->linkPrefix . ($this->page + 1) . $this->linkSufflix .'">&gt;</a> <a href="'. $this->linkPrefix . $this->num_pages . $this->linkSufflix .'">&gt;&gt;</a></strong>';
	}

	public function OutputArray()
	{
		if(!empty($this->output['array']))
			return $this->output['array'];

		else
			return false;
	}

	public function OutputPanel()
	{
		if(!empty($this->output['panel']))
			return $this->output['panel'];

		else
			return false;
	}

	public function OutputPage()
	{
		if(!empty($this->page))
			return $this->page;

		else
			return false;
	}

	public function OutputOffSet()
	{
		if(!empty($this->output['offset']))
			return $this->output['offset'];

		else
			return false;
	}

	public function OutputLimit()
	{
		if(!empty($this->output['limit']))
			return $this->output['limit'];

		else
			return false;
	}

	public function PagTrue()
	{
		return $this->pagtrue;
	}
}
