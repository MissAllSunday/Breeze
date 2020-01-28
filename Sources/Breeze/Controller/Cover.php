<?php

declare(strict_types=1);


namespace Breeze\Controller;

use Breeze\Repository\User\Cover as CoverRepository;
use Breeze\Service\Data;

class Cover extends Base implements BaseInterface
{
	/**
	 * @var CoverRepository
	 */
	private $coverRepository;

	/**
	 * @var Data
	 */
	private $data;

	public function __construct(Data $data, CoverRepository $coverRepository)
	{
		$this->data = $data;
		$this->coverRepository = $coverRepository;
	}

	public function do(): void
	{
		// TODO: Implement do() method.
	}

	public function create(): void
	{
		// TODO: Implement create() method.
	}

	public function update(): void
	{
		// TODO: Implement update() method.
	}

	public function delete(): void
	{
		// TODO: Implement delete() method.
	}

	public function display(): void
	{
		global $smcFunc, $modSettings, $maintenance;

		// Get the user ID.
		$useriD = $this->data('get')->get('u');

		// Thumbnail?
		$thumb = $this->data('get')->validate('thumb');

		// Kinda need this!
		if (!$this['tools']->enable('cover') || empty($useriD))
		{
			header('HTTP/1.0 404 File Not Found');
			die('404 File Not Found');
		}

		// Get the user's settings.
		$userSettings = $this['query']->getUserSettings($useriD);

		// Gotta work with paths.
		$folder = $this['tools']->boardDir . \Breeze\Breeze::$coversFolder . $useriD . '/' . ($thumb ? 'thumbnail/' : '');

		// False if there is no image.
		$file = empty($userSettings['cover']) ? false : $folder . $userSettings['cover']['basename'];

		// Lots and lots of checks!
		if ((!empty($maintenance) && 2 == $maintenance) || empty($file) || !file_exists($file))
		{
			header('HTTP/1.0 404 File Not Found');
			die('404 File Not Found');
		}

		// Kill anything else
		ob_end_clean();

		// This is done to clear any output that was made before now.
		if(!empty($modSettings['enableCompressedOutput']) && !headers_sent() && 0 == ob_get_length())
		{
			if('1' == @ini_get('zlib.output_compression') || 'ob_gzhandler' == @ini_get('output_handler'))
				$modSettings['enableCompressedOutput'] = 0;
			else
				ob_start('ob_gzhandler');
		}

		if(empty($modSettings['enableCompressedOutput']))
		{
			ob_start();
			header('Content-Encoding: none');
		}

		// Get some info.
		$fileTime = filemtime($file);

		// If it hasn't been modified since the last time this attachment was retrieved, there's no need to display it again.
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			[$modified_since] = explode(';', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if (strtotime($modified_since) >= $fileTime)
			{
				ob_end_clean();

				// Answer the question - no, it hasn't been modified ;).
				header('HTTP/1.1 304 Not Modified');
				exit;
			}
		}

		header('Pragma: ');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $fileTime) . ' GMT');
		header('Accept-Ranges: bytes');
		header('Connection: close');
		header('ETag: ' . md5($fileTime));
		header('Content-Type: ' . $userSettings['cover']['mime']);

		// Since we don't do output compression for files this large...
		if (4194304 < filesize($file))
		{
			// Forcibly end any output buffering going on.
			while (0 < @ob_get_level())
				@ob_end_clean();

			$fp = fopen($file, 'rb');
			while (!feof($fp))
			{
				echo fread($fp, 8192);
				flush();
			}
			fclose($fp);
		}

		// On some of the less-bright hosts, readfile() is disabled.  It's just a faster, more byte safe, version of what's in the if.
		elseif (null === @readfile($file))
			echo file_get_contents($file);

		die();
	}
}
