<?php

namespace HearMe\Includes;

use HearMe\Exceptions\HMA_NotFoundTranslationException;

abstract class HMA_Translations
{
    static public function get($key)
    {
        $translations = self::translations();

        if (!isset($translations[$key])) {
            throw new HMA_NotFoundTranslationException("Missing key: " . $key);
        }

        return $translations[$key];
    }

    static protected function translations()
    {
        return [
            'rest_cookie_invalid_nonce' => __('Rest cookie is invalid', 'hearme'),
            'post_not_found' => __('Post not found. Please try again', 'hearme'),
            'episode_not_generated' => __('The audio for this post has not been created yet', 'hearme'),
            'generate_episode_button' => __('Generate audio', 'hearme'),
            'edit_episode_button' => __('Edit audio in HearMe.ai', 'hearme'),
            'draft_generated' => __('Draft for this post has been created', 'hearme'),
            'generating_episode' => __('The audio is being generated at the moment', 'hearme'),
            'generating_episode_draft' => __('The draft is being created at the moment', 'hearme'),
            'hear_episode' => __('Listen to the generated audio', 'hearme'),
            'episode_generated_and_published' => __('The audio has been generated and published', 'hearme'),
            'hear_me_settings' => __('Hearme.Ai Plugin Settings', 'hearme'),
            'choose_option' => __('-- Choose an option --', 'hearme'),
            'enter_settings' => __('Configure your HearMe.ai plugin with the settings below', 'hearme'),
            'connection_settings_title' => __('Settings', 'hearme'),
            'api_key' => __('Api Key', 'hearme'),
            'hide_player' => __('Hide player', 'hearme'),
            'api_key_description' => __('You can obtain the API Key when you add a new website to HearMe.ai and choose the Wordpress integration mode. You can find your API key in the Settings section of HearMe.ai', 'hearme'),
            'insert_number_description' => __('Please select after or before which paragraph the player will be added (1st, 2nd, 3rd etc.). Please enter the number only. ', 'hearme'),
            'insert_type_description' => __('This setting allows you to automatically add HearMe.ai player for articles that have audio generated before or after n-th paragrapth. Please make sure you select the "n" number in the setting below.', 'hearme'),
            'generate_drafts_description' => __('Send posts to HearMe.ai', 'hearme'),
            'insert_type' => __('Auto add Hearme.Ai Player', 'hearme'),
            'hear_me_settings_title' => __('HearMe.ai', 'hearme'),
            'api_key_error' => __('API Key is invalid', 'hearme'),
            'api_internal_error' => __('There is some internal error', 'hearme'),
            'before_p' => __('Before {n} paragraph', 'hearme'),
            'after_p' => __('After {n} paragraph', 'hearme'),
            'before_h' => __('Before {n} heading in text', 'hearme'),
            'after_h' => __('After {n} heading in text', 'hearme'),
            'before_t' => __('Before title', 'hearme'),
            'after_t' => __('After title', 'hearme'),
            'notifiy_missing_api_key' => __('API Key is missing, you can setup that through out wizard or by settings page.', 'hearme'),
            'notifiy_settings_button' => __('Settings', 'hearme'),
            'notifiy_wizard_button' => __('Setup Wizard', 'hearme'),
        ];
    }
}
