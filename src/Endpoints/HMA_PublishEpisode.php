<?php

namespace HearMe\Endpoints;

use HearMe\Includes\HMA_Endpoint;
use HearMe\Includes\HMA_Translations;

class HMA_PublishEpisode extends HMA_Endpoint
{
    protected $uri = 'episode/publish';

    protected function handle(\WP_REST_Request $request): \WP_REST_Response
    {
        $postId = $request->get_param('id');

        if (!$postId) {
            return new \WP_REST_Response([
                "code" => "post_not_found",
                "message" => HMA_Translations::get('post_not_found'),
                "data" => [
                    "status" => 404
                ]
            ], 404);
        }

        $post = get_post($postId);
        $response = $this->connector->publishEpisode($post->ID);

        return new \WP_REST_Response($response);
    }

    public function getMethod(): string
    {
        return \WP_REST_Server::CREATABLE;
    }
}
