<?php

namespace HearMe\Includes;

use HearMe\Exceptions\HMA_NotFoundEpisodeException;

final class HMA_PluginNotify
{

    /**
     * @var string[]
     */ 
    private $options;

    /**
     * @var HMA_Twig
     */
    private $twig;

    public function __construct(HMA_Twig $twig)
    {
        $this->twig = $twig;
    }

    public function notifyMissingKey()
    {
        $this->options = get_option('hear_me_options');

        if (is_array($this->options) && !$this->options['api_key']) {
            $this->twig->render('notifications/notifyMissingApi', [
                'message' => HMA_Translations::get('notifiy_missing_api_key'),
                'settings_link' => menu_page_url('hear-me-settings', false),
                'settings_text' => HMA_Translations::get('notifiy_settings_button'),
                'wizard_link' => menu_page_url('hear-me-wizard', false),
                'wizard_text' => HMA_Translations::get('notifiy_wizard_button'),
            ]);
        }
    }
}
