<?php

namespace HearMe\Hooks\Actions;

use HearMe\Includes\HMA_Action;
use HearMe\Includes\HMA_Options;

final class HMA_LoadPlugin extends HMA_Action
{
    public function getActionName(): string
    {
        return 'admin_init';
    }

    public function handle()
    {
        if (get_option(HMA_Options::ACTIVATION_REDIRECT, false)) {
            delete_option(HMA_Options::ACTIVATION_REDIRECT);
            wp_safe_redirect(admin_url('admin.php?page=hear-me-wizard'));
        }
    }
}
