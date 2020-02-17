<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Repository\User\Cover as CoverRepository;
use Breeze\Service\Request;
use Breeze\Service\Settings;
use Breeze\Service\Text;

abstract class Base
{
	const CREATE = 'create';
	const DELETE = 'delete';

    /**
     * @var Text
     */
    protected $text;

    /**
     * @var Settings
     */
    protected $settings;

    public function __construct(Text $text, Settings $settings)
    {
        $this->text = $text;
        $this->settings = $settings;
    }
}
