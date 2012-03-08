<?php

/**
 * Breeze
 *
 * The purpose of this file is to create a pagination from an array of items
 * @package Breeze mod
 * @version 1.0 Beta 1
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

class Breeze_Pagination
{
	public function __construct($array, $page = 1, $link_prefix = false, $link_suffix = false, $limit_page = 20, $limit_number = 10)
	{
		if (empty($array))
			$this->UsedArray = array();

		else
		{
			$this->UsedArray = $array;
			$this->Page = !empty($page) || !$limit_page ? $page : 1;
			$this->LinkPrefix = !empty($link_prefix) ? $link_prefix : '';
			$this->LinkSufflix = !empty($link_sufflix) ? $link_sufflix : '';
			$this->LimitPage = !empty($limit_page) ? $limit_page : 20;
			$this->LimitNumber = !empty($limit_number) ? $limit_number : 20;
			$this->panel = '';
			$this->output = array();
			$this->page_cur = '';
		}
	}

	public function PaginationArray()
	{
		$this->num_rows = count($this->UsedArray);

		if (!$this->num_rows or $this->LimitPage >= $this->num_rows)
		{
			$this->pagtrue = false;
			return;
		}

		$this->num_pages = ceil($this->num_rows / $this->LimitPage);
		$this->page_offset = ($this->Page - 1) * $this->LimitPage;

		/* Calculating the first number to show */
		if ($this->LimitNumber)
		{
			$this->limit_number_start = $this->Page - ceil($this->LimitNumber / 2);
			$this->limit_number_end = ceil($this->Page + $this->LimitNumber / 2) - 1;

			if ($this->limit_number_start < 1)
				$this->limit_number_start = 1;

			//In case if the current page is at the beginning.
			$this->dif = ($this->limit_number_end - $this->limit_number_start);

			if ($this->dif < $this->LimitNumber)
				$this->limit_number_end = $this->limit_number_end + ($this->LimitNumber - ($this->dif + 1));

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
		$this->output['limit'] = $this->LimitPage; //Number of resuts per page.
		$this->output['array'] = array_slice($this->UsedArray, $this->page_offset, $this->LimitPage, true); //Array of current page results.

		$this->pagtrue = true;
	}

	/* Generating page links. */
	private function GeneratePageLinks()
	{
		global $scripturl;

		for ($i = $this->limit_number_start; $i <= $this->limit_number_end; $i++)
		{
			$this->page_cur = '<a href='. $this->LinkPrefix . $i . $this->LinkSufflix. '>'. $i .'</a>';

			if ($this->Page == $i)
				$this->page_cur = '<strong>'. $i .'</strong>';

			else
				$this->page_cur = '<a href='. $scripturl .''. $this->LinkPrefix . $i . $this->LinkSufflix .'>'. $i .'</a>';

			$this->panel .= ' <span>'. $this->page_cur .'</span>';
		}
	}

	/* Navigation arrows. */
	private  function NavigationArrows()
	{
		global $scripturl;

		if ($this->limit_number_start > 1)
			$this->panel = '<strong><a href="'. $scripturl .''. $this->LinkPrefix . (1) . $this->LinkSufflix .'">&lt;&lt;</a>  <a href="'. $this->LinkPrefix . ($this->Page - 1) . $this->LinkSufflix .'">&lt;</a></strong>'. $this->panel;

		if ($this->limit_number_end < $this->num_pages)
			$this->panel = $this->panel .' <strong><a href="'. $scripturl .''. $this->LinkPrefix . ($this->Page + 1) . $this->LinkSufflix .'">&gt;</a> <a href="'. $this->LinkPrefix . $this->num_pages . $this->LinkSufflix .'">&gt;&gt;</a></strong>';
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
