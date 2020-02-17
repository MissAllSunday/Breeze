<?php

declare(strict_types=1);


trait Persistance
{
	public function setResponse($message, $type): void
	{
		if (empty($message) || empty($type))
			return;

		// Yeah, a nice session var...
		$_SESSION['Breeze']['response'] = [
		    'message' => $message,
		    'type' => $type,
		];
	}

	public function getResponse()
	{
		if (empty($_SESSION['Breeze']['response']))

			return false;

		$response = $_SESSION['Breeze']['response'];
		unset($_SESSION['Breeze']['response']);

		return $response;
	}
}
