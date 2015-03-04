<?php

/**
 * BreezeTrackActions
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeTrackActions extends Breeze
{
	function __construct()
	{
		parent::__construct();
	}

	public function createTopic(&$msgOptions, &$topicOptions, &$posterOptions)
	{
		// Get the poster's options.
		$options = $this['query']->getUserSettings($posterOptions['id']);

		// Does the user wants to log this? does the new topic has been approved?
		if (!empty($options['alert_topic']) && !empty($topicOptions['is_approved']))
			$this['query']->createLog(array(
				'member' => $posterOptions['id'],
				'content_type' => 'topic',
				'content_id' => $topicOptions['id'],
				'time' => time(),
				'extra' => array(
					'subject' => $msgOptions['subject'],
					'toLoad' => array($posterOptions['id']),),
			));
	}
	
	public function editAvatar(&$profile_vars, &$post_errors, $memID, $cur_profile, $current_area)
	{global $user_info;
		echo '<pre>';
		print_r($profile_vars);echo '</pre><hr><pre>';
		print_r($user_info);
		die;
	}
}
