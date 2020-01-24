<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

class User
{
	public function stalkingCheck($fetchedUser = 0)
	{
		global $user_info;

		// But of course you can stalk non-existent users!
		if (empty($fetchedUser))
			return true;

		// Get the "stalkee" user settings.
		$stalkedSettings = $this->_app['query']->getUserSettings($fetchedUser);

		// Check if the stalker has been added in stalkee's ignore list.
		if (!empty($stalkedSettings['kick_ignored']) && !empty($stalkedSettings['ignoredList']))
		{
			$ignored = explode(',', $stalkedSettings['ignoredList']);

			return in_array($user_info['id'], $ignored);
		}

		// Lucky you!

		return false;
	}

	public function floodControl($user = 0)
	{
		global $user_info;

		// No param? use the current user then.
		$user = !empty($user) ? $user : $user_info['id'];

		// Set some needed stuff.
		$seconds = 60 * ($this->setting('flood_minutes') ? $this->setting('flood_minutes') : 5);
		$messages = $this->setting('flood_messages') ? $this->setting('flood_messages') : 10;

		// Has it been defined yet?
		if (!isset($_SESSION['Breeze_floodControl' . $user]))
			$_SESSION['Breeze_floodControl' . $user] = [
			    'time' => time() + $seconds,
			    'msg' => 0,
			];

		// Keep track of it.
		$_SESSION['Breeze_floodControl' . $user]['msg']++;

		// Short name.
		$flood = $_SESSION['Breeze_floodControl' . $user];

		// Chatty one huh?
		if ($flood['msg'] >= $messages && time() <= $flood['time'])
			return false;

		// Enough time has passed, give the user some rest.
		if (time() >= $flood['time'])
			unset($_SESSION['Breeze_floodControl' . $user]);

		return true;
	}

	public function loadUserInfo($id, $returnID = false)
	{
		global $context, $memberContext, $txt;

		// If this isn't an array, lets change it to one.
		$id = (array) $id;
		$id = array_unique($id);

		// Only load those that haven't been loaded yet.
		if (!empty(static::$_users))
			foreach ($id as $k => $v)
				if (!empty(static::$_users[$v]))
					unset($id[$k]);

		// Got nothing to load.
		if (empty($id))
		{
			$context['Breeze']['user_info'] = static::$_users;

			return $returnID ? array_keys(static::$_users) : false;
		}

		// $memberContext gets set and globalized, we're gonna take advantage of it
		$loadedIDs = loadMemberData($id, false, 'profile');

		// Set the context var.
		foreach ($id as $k => $u)
		{
			// Set an empty array.
			static::$_users[$u] = [
			    'breezeFacebox' => '',
			    'link' => '',
			    'name' => '',
			    'linkFacebox' => '',
			];

			// Gotta make sure you're only loading info from real existing members...
			if (is_array($loadedIDs) && in_array($u, $loadedIDs))
			{
				loadMemberContext($u, true);

				$user = $memberContext[$u];

				// Pass the entire data array.
				static::$_users[$user['id']] = $user;

				// Build the "breezeFacebox" link. Rename "facebox" to "breezeFacebox" in case there are other mods out there using facebox, specially its a[rel*=facebox] stuff.
				static::$_users[$user['id']]['breezeFacebox'] = '<a href="' . $this->scriptUrl . '?action=wall;sa=userdiv;u=' . $u . '" class="avatar" rel="breezeFacebox" data-name="' . (!empty($user['name']) ? $user['name'] : '') . '">' . $user['avatar']['image'] . '</a>';

				// Also provide a no avatar facebox link.
				static::$_users[$user['id']]['linkFacebox'] = '<a href="' . $this->scriptUrl . '?action=wall;sa=userdiv;u=' . $u . '" class="avatar" rel="breezeFacebox" data-name="' . (!empty($user['name']) ? $user['name'] : '') . '">' . $user['name'] . '</a>';
			}

			// Not a real member, fill out some guest generic vars and be done with it..
			else
				static::$_users[$u] = [
				    'breezeFacebox' => $txt['guest_title'],
				    'link' => $txt['guest_title'],
				    'name' => $txt['guest_title'],
				    'linkFacebox' => $txt['guest_title'],
				];
		}

		$context['Breeze']['user_info'] = static::$_users;

		// Lastly, if the ID was requested, sent it back!
		if ($returnID)
			return $loadedIDs;
	}
}
