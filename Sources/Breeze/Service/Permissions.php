<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;

class Permissions extends Base
{
	public const DELETE_COMMENTS = 'deleteComments';
	public const DELETE_OWN_COMMENTS = 'deleteOwnComments';
	public const DELETE_OWN_PROFILE_COMMENTS = 'deleteProfileComments';
	public const DELETE_STATUS = 'deleteStatus';
	public const DELETE_OWN_STATUS = 'deleteOwnStatus';
	public const DELETE_OWN_PROFILE_STATUS = 'deleteProfileStat;us';
	public const POST_STATUS = 'postStatus';
	public const POST_COMMENTS = 'postComments;';
	public const USE_COVER = 'useCover';
	public const USE_MOOD = 'useMood';

	/**
	 * @var Text
	 */
	private $text;

	public function __construct(Text $text)
	{
		$this->text = $text;
	}

	public function hookPermissions(&$permissionGroups, &$permissionList): void
	{
		$this->text->setLanguage(Breeze::NAME);

		$permissionGroups['membergroup']['simple'] = ['breeze_per_simple'];
		$permissionGroups['membergroup']['classic'] = ['breeze_per_classic'];

		foreach (self::getAll() as $permissionName)
			$permissionList['membergroup']['breeze_' . $permissionName] = [
			    false,
			    'breeze_per_classic',
			    'breeze_per_simple'];
	}

	public static function getAll(): array
	{
		return [
		    self::DELETE_COMMENTS,
		    self::DELETE_OWN_COMMENTS,
		    self::DELETE_OWN_PROFILE_COMMENTS,
		    self::DELETE_STATUS,
		    self::DELETE_OWN_STATUS,
		    self::DELETE_OWN_PROFILE_STATUS,
		    self::POST_STATUS,
		    self::POST_COMMENTS,
		    self::USE_COVER,
		    self::USE_MOOD,
		];
	}
}
