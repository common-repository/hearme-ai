<?php

namespace HearMe\Endpoints;

use HearMe\Includes\HMA_Endpoint;
use HearMe\Includes\HMA_Translations;

class HMA_GenerateAllDrafts extends HMA_Endpoint
{
    protected $uri = 'drafts/generate';

    protected function handle(\WP_REST_Request $request): \WP_REST_Response
    {
        $generateEpisodeClass = new HMA_GenerateEpisode($this->connector, $this->twig);

        $number_of_posts_per_page = 10;
        $initial_offset = 0;
        $paged = $request->get_param('page') ? $request->get_param('page') : 1;
        $number_of_posts_past = $number_of_posts_per_page * ($paged - 1);
        $off = $initial_offset + (($paged > 1) ? $number_of_posts_past : 0);

        $customPostTypes = empty(get_option('hear_me_options')['supported_posts'])? array() : get_option('hear_me_options')['supported_posts'];

        $args = array(
            'post_type' => array_merge(array('post'), $customPostTypes),
            'post_status' => array('publish'),
            'posts_per_page' => $number_of_posts_per_page,
            'paged' => $paged,
            'orderby' => 'date',
            'offset' => $off
        );

        $argsAll = array(
            'post_type' => array_merge(array('post'), $customPostTypes),
            'post_status' => array('publish'),
            #'posts_per_page' => $number_of_posts_per_page,
            'posts_per_page' => -1,
            #'paged' => $paged,
            'orderby' => 'date',
            #'offset' => $off
        );

        $posts = get_posts( $args );
        $postsAll = get_posts( $argsAll );

        $logs = [];
        $noChanges = [];
        foreach ($posts as $post) {
            $response = $generateEpisodeClass->generateEpisode($post->ID);
            $logs[$post->ID] = $response;
            if (is_array($response) && isset($response['status']) && $response['status'] === 'not_found_changes') array_push($noChanges, 1);
        }

        $allPosts = wp_count_posts();
        return new \WP_REST_Response([
            'logs' => $logs,
            'finish' => (count($posts) < 10) ? true : false,
            'all_posts' => (int) count($postsAll),
            'processes_posts' => count($posts),
            'no_change' => count($noChanges),
        ]);
    }

    public function getMethod(): string
    {
        return \WP_REST_Server::CREATABLE;
    }
}
