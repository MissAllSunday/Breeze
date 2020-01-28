<?php

declare(strict_types=1);

namespace Breeze\Repository\User;

use Breeze\Controller\User\Wall;
use Breeze\Model\User as UserModel;
use Breeze\Service\Settings;
use Breeze\Service\Text;

class User
{
	/**
	 * @var UserModel
	 */
	protected $userModel;

	/**
	 * @var Settings
	 */
	protected $settings;

	/**
	 * @var Text
	 */
	protected $text;

	public function __construct(UserModel $userModel, Settings $settings, Text $text)
	{
		$this->userModel = $userModel;
		$this->settings = $settings;
		$this->text = $text;
	}

	public function loadUserInfo(array $userIds, $returnId = false)
	{
		static $loadedUserIds = [];

		$memberContext = $this->settings->global('memberContext');
		$userIds = array_unique($userIds);

		if (!empty($loadedUserIds))
			foreach ($userIds as $userId)
				if (!empty($loadedUserIdss[$userId]))
					unset($userIds[$userId]);

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
