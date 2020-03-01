<?php

declare(strict_types=1);

namespace Breeze\Controller\Admin;

use Breeze\Breeze as Breeze;
use Breeze\Controller\BaseController as BaseController;
use Breeze\Repository\MoodRepository as MoodRepository;
use Breeze\Service\Settings as SettingsService;

class Feed extends BaseController
{
	protected const STATUS_OK = 200;

	/**
	 * @var MoodRepository
	 */
	protected $moodRepository;

	/**
	 * @var SettingsService
	 */
	protected $settingsService;

	public function __construct(MoodRepository $moodRepository, SettingsService $settingsService)
	{
		$this->moodRepository = $moodRepository;
		$this->settingsService = $settingsService;
	}

	public function do(): void
	{
		// TODO: Implement do() method.
	}

	public function showFeed(): void
	{
		$this->settingsService->requireOnce('Class-CurlFetchWeb');
		$feedData = '';

		$fetch = new \curl_fetch_web_data();
		$fetch->get_url_data(Breeze::FEED);

		if (self::STATUS_OK === $fetch->result('code') && !$fetch->result('error'))
			$feedData = $fetch->result('body');

		smf_serverResponse($feedData, 'Content-type: text/xml');
	}
}
