<?php

/**
* Array Pagination Function.
* By Sergey Gurevich.
*
* Input:
* 1 - Target Array.
* 2 - Page Number.
* 3 - Link prefix (example: "?page=").
* 4 - Link suffix.
* 5 - Results per page.
* 6 - Number of pages displayed in the page link panel.
*
* Output:
* - $output['panel'] - Link Panel HTML source.
* - $output['offset'] - Current page number.
* - $output['limit'] - Number of resuts per page.
* - $output['array'] = - Array of current page results.
*
* Will return FALSE if no pagination was done.
*/
function pagination_array($array, $page = 1, $link_prefix = false, $link_suffix = false, $limit_page = 20, $limit_number = 10)
{
	if (empty($page) or !$limit_page) $page = 1;

	$num_rows = count($array);
	if (!$num_rows or $limit_page >= $num_rows) return false;
	$num_pages = ceil($num_rows / $limit_page);
	$page_offset = ($page - 1) * $limit_page;

	//Calculating the first number to show.
	if ($limit_number)
	{
		$limit_number_start = $page - ceil($limit_number / 2);
		$limit_number_end = ceil($page + $limit_number / 2) - 1;
		if ($limit_number_start < 1) $limit_number_start = 1;
		//In case if the current page is at the beginning.
		$dif = ($limit_number_end - $limit_number_start);
		if ($dif < $limit_number) $limit_number_end = $limit_number_end + ($limit_number - ($dif + 1));
		if ($limit_number_end > $num_pages) $limit_number_end = $num_pages;
		//In case if the current page is at the ending.
		$dif = ($limit_number_end - $limit_number_start);
		if ($limit_number_start < 1) $limit_number_start = 1;
	}
	else
	{
		$limit_number_start = 1;
		$limit_number_end = $num_pages;
	}
	//Generating page links.
	for ($i = $limit_number_start; $i <= $limit_number_end; $i++)
	{
		$page_cur = "<a href='$link_prefix$i$link_suffix'>$i</a>";
		if ($page == $i) $page_cur = "<b>$i</b>";
		else $page_cur = "<a href='$link_prefix$i$link_suffix'>$i</a>";

		$panel .= " <span>$page_cur</span>";
	}

	$panel = trim($panel);
	//Navigation arrows.
	if ($limit_number_start > 1) $panel = "<b><a href='$link_prefix".(1)."$link_suffix'>&lt;&lt;</a>  <a href='$link_prefix".($page - 1)."$link_suffix'>&lt;</a></b> $panel";
	if ($limit_number_end < $num_pages) $panel = "$panel <b><a href='$link_prefix".($page + 1)."$link_suffix'>&gt;</a> <a href='$link_prefix$num_pages$link_suffix'>&gt;&gt;</a></b>";

	$output['panel'] = $panel; //Panel HTML source.
	$output['offset'] = $page_offset; //Current page number.
	$output['limit'] = $limit_page; //Number of resuts per page.
	$output['array'] = array_slice($array, $page_offset, $limit_page, true); //Array of current page results.

	return $output;
}