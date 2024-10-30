<?php

namespace HearMe;

use HaydenPierce\ClassFinder\ClassFinder;
use HearMe\Includes\HMA_Action;
use HearMe\Includes\HMA_Connector;
use HearMe\Includes\HMA_Endpoint;
use HearMe\Includes\HMA_Filter;
use HearMe\Includes\HMA_Metabox;
use HearMe\Includes\HMA_PluginSettings;
use HearMe\Includes\HMA_PluginNotify;
use HearMe\Includes\HMA_Post;
use HearMe\Includes\HMA_Shortcode;
use HearMe\Includes\HMA_Twig;
use HearMe\Shortcodes\HMA_Player;


class HMA_Core
{
    /**
     * @var string[]
     */
    private $filters;

    /**
     * @var string[]
     */
    private $actions;

    /**
     * @var string[]
     */
    private $posts;

    /**
     * @var string[]
     */
    private $endpoints;

    /**
     * @var string[]
     */
    private $shortcodes;

    /**
     * @var HMA_Twig
     */
    private $twig;

    /**
     * @var HMA_Connector
     */
    private $connector;

    /**
     * @var mixed[]
     */
    private $options;

    public function __construct()
    {
        $this->twig = new HMA_Twig();
        $this->connector = new HMA_Connector();
        $this->filters = ClassFinder::getClassesInNamespace('HearMe\Hooks\Filters');
        $this->actions = ClassFinder::getClassesInNamespace('HearMe\Hooks\Actions');
        $this->posts = ClassFinder::getClassesInNamespace('HearMe\Posts');
        $this->endpoints = ClassFinder::getClassesInNamespace('HearMe\Endpoints');
        $this->shortcodes = ClassFinder::getClassesInNamespace('HearMe\Shortcodes');
    }

    public function initialize()
    {
        $this->initializeActions();
        $this->initializeFilters();

        $settings = new HMA_PluginSettings($this->twig, $this->connector);
        $notifiy  = new HMA_PluginNotify($this->twig);

        add_action('admin_menu', array($settings, 'menu'));
        add_action('admin_menu', array($settings, 'wizardPage'));
        add_action('admin_init', array($settings, 'initialize'));
        add_action('admin_notices', array($notifiy, 'notifyMissingKey'));

        add_action('admin_enqueue_scripts', array($this, 'initializeWizardScripts'));

        $this->options = get_option('hear_me_options');
        if (isset($this->options['api_key']) && !empty($this->options['api_key'])) {
            add_action('admin_enqueue_scripts', array($this, 'initializeScripts'));
            add_action('wp_enqueue_scripts', array($this, 'initializeFrontScripts'));
            add_action('rest_api_init', array($this, 'initializeEndpoints'));
            add_action('add_meta_boxes', array($this, 'initializeMetaboxes'));
            add_action('init', array($this, 'initializeShortcodes'));
            add_action('init', array($this, 'registerCustomBlock'));

            if (in_array($this->options['insert_type'], ['before_p', 'after_p', 'before_h', 'after_h'])) {
                add_filter('the_content', array($this, 'renderPlugin'));
            }

            if (in_array($this->options['insert_type'], ['before_t', 'after_t'])) {
                add_filter('the_title', array($this, 'renderPluginInTitle'), 10, 2);
            }
        }


        //Plugin Settings Page
        add_filter('plugin_action_links_' . HEAR_ME_PLUGIN, array($this, 'addSettingsLink'));
    }

    public function addSettingsLink($links)
    {
        $settings_link = '<a href="admin.php?page=hear-me-settings">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function renderPluginInTitle($title, $id)
    {
        if (!is_admin() && !is_null($id)) {
            $post = get_post($id);
            if ($post instanceof \WP_Post && ($post->post_type == 'post' || $post->post_type == 'page')) {
                $player = new HMA_Player($this->twig, $this->connector);
                if (method_exists($player, $this->options['insert_type'])) {
                    $hidePlayer = (isset($this->options['hide_player'])) ? true : false;
                    return $player->{$this->options['insert_type']}($title, (int) $this->options['insert_number'], $hidePlayer);
                }
            }
        }


        return $title . 'bez';
    }

    public function renderPlugin($content)
    {   
        $player = new HMA_Player($this->twig, $this->connector);
        if (method_exists($player, $this->options['insert_type'])) {
            $hidePlayer = (isset($this->options['hide_player'])) ? true : false;
            return $player->{$this->options['insert_type']}($content, (int) $this->options['insert_number'], $hidePlayer);
        }
    }

    public function initializeScripts($hook)
    {
        wp_enqueue_script(
            'hear_me_admin_edit',
            HEAR_ME_ABS_URL . 'assets/js/admin.js',
            array('jquery'),
            '1.1'
        );

        wp_enqueue_script(
            'hear_me_admin_js',
            HEAR_ME_ABS_URL . 'assets/js/build/index.js',
            array('jquery'),
            '1.1'
        );

        wp_enqueue_script(
            'hear_me_script',
            HEAR_ME_SCRIPT_URL . 'player.js',
            array(),
            '1.1'
        );

        wp_localize_script('hear_me_admin_edit', 'hear_me_settings', [
            'nonce' => wp_create_nonce('wp_rest'),
            'api' => [
                'url' => HEAR_ME_API_URL,
                'key' => $this->options['api_key']
            ],
            'rest' => [
                'generate_episode' => get_rest_url(null, 'hear-me/episode/generate'),
                'cost_info' => get_rest_url(null, 'hear-me/episode/cost'),
                'get_player' => get_rest_url(null, 'hear-me/episode/player'),
                'episode_info' => get_rest_url(null, 'hear-me/episode/info'),
                'publish_episode' => get_rest_url(null, 'hear-me/episode/publish'),
                'send_all_drafts' => get_rest_url(null, 'hear-me/drafts/generate'),
            ]
        ]);
    }

    public function initializeFrontScripts()
    {
        wp_enqueue_script(
            'hear_me_script',
            HEAR_ME_SCRIPT_URL . 'player.js',
            array(),
            '1.0'
        );
    }

    public function initializeWizardScripts()
    {
        wp_enqueue_style(
            'hear_me_base_styles',
            HEAR_ME_ABS_URL . 'assets/js/build/index.css',
            array(),
            '1.0'
        );
        wp_enqueue_script(
            'hear_me_wizard_script',
            HEAR_ME_ABS_URL . 'assets/js/wizard.js',
            array(),
            '1.0'
        );

        wp_add_inline_script('hear_me_wizard_script', 'const api_url =" '. HEAR_ME_API_URL . '";', 'before');
    }

    function registerCustomBlock()
    {
        wp_register_script(
            'hear-me-player-block',
            HEAR_ME_ABS_URL . 'assets/js/block.js',
            array('wp-blocks', 'wp-block-library', 'wp-element'),
            '1.0'
        );

        register_block_type('hear-me/player', array(
            'api_version' => 2,
            'editor_script' => 'hear-me-player-block',
        ));
    }

    public function initializeEndpoints()
    {
        foreach ($this->endpoints as $endpoint) {
            /** @var HMA_Endpoint $instance */
            $instance = new $endpoint($this->connector, $this->twig);

            register_rest_route('hear-me', $instance->getUri(), [
                'methods' => $instance->getMethod(),
                'callback' => [$instance, 'callback'],
                'permission_callback' => function (\WP_REST_Request $request) {
                    return true;
                },
            ]);
        }
    }

    public function initializeMetaboxes()
    {
        foreach ($this->posts as $post) {
            /** @var HMA_Post $postObject */
            $postObject = new $post();

            foreach ($postObject->metaboxes() as $metabox) {
                /** @var HMA_Metabox $metaboxObject */
                $metaboxObject = new $metabox($this->twig, $this->connector);
                add_meta_box(
                    $metaboxObject->getId(),
                    $metaboxObject->getTitle(),
                    [$metaboxObject, 'handle'],
                    $postObject->getType(),
                    $metaboxObject->getContext(),
                    $metaboxObject->getPriority()
                );
            }
        }
    }

    public function initializeShortcodes()
    {
        foreach ($this->shortcodes as $shortcode) {
            /** @var HMA_Shortcode $instance */
            $instance = new $shortcode($this->twig, $this->connector);

            add_shortcode($instance->getTag(), [$instance, 'handle']);
        }
    }

    private function initializeFilters()
    {
        foreach ($this->filters as $filter) {
            /** @var HMA_Filter $filterObject */
            $filterObject = new $filter($this->twig);
            add_filter($filterObject->getFilterName(), [$filterObject, 'handle']);
        }
    }

    private function initializeActions()
    {
        foreach ($this->actions as $action) {
            /** @var HMA_Action $actionObject */
            $actionObject = new $action($this->twig);
            add_action($actionObject->getActionName(), [$actionObject, 'handle']);
        }
    }
}
