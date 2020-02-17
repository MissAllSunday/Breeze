<?php

declare(strict_types=1);

namespace Breeze\Controller;

class Admin extends Base implements ControllerInterface
{
    public function doAction()
    {
        echo 'lol';
    }

    public function getSubActions(): array
    {
        return [
            'general' => [$this->text->get('page_main')],
            'settings' => [$this->text->get('page_settings')],
            'permissions' => [$this->text->get('page_permissions')],
            'cover' => [$this->text->get('page_cover')],
            'donate' => [$this->text->get('page_donate')],
        ];
    }
}
