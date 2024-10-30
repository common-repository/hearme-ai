<?php

/**
 * Plugin Name: HearMe.ai
 * Description: Reach audiences who prefer to listen! Automatically convert your blog into a podcast and add audio players directly to your website.
 * Text Domain: hearme
 * Version: 1.15.6
 * Author: hearmeai
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 **/

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

defined('HEAR_ME_ABS_PATH') || define('HEAR_ME_ABS_PATH', plugin_dir_path(__FILE__));
defined('HEAR_ME_ABS_URL') || define('HEAR_ME_ABS_URL', plugin_dir_url(__FILE__));
defined('HEAR_ME_PLUGIN') || define('HEAR_ME_PLUGIN', plugin_basename(__FILE__));
defined('HEAR_ME_API_URL') || define('HEAR_ME_API_URL', $_ENV['API_URL']);
defined('HEAR_ME_SCRIPT_URL') || define('HEAR_ME_SCRIPT_URL', $_ENV['HEARME_URL']);

register_activation_hook(__FILE__, 'HMA_activate');

function HMA_activate()
{
    add_option(\HearMe\Includes\HMA_Options::ACTIVATION_REDIRECT, true);
}

(new \HearMe\HMA_Core())->initialize();
