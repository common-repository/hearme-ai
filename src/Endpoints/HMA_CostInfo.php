<?php

namespace HearMe\Endpoints;

use HearMe\Exceptions\HMA_NotFoundEpisodeException;
use HearMe\Exceptions\HMA_InternalServerErrorException;
use HearMe\Includes\HMA_Endpoint;
use HearMe\Includes\HMA_Translations;

class HMA_CostInfo extends HMA_Endpoint
{
    protected $uri = 'episode/cost';

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

        try {
            $response = $this->connector->getEpisodeCost($post->ID);
        } catch (HMA_NotFoundEpisodeException $exception) {
            return new \WP_REST_Response([], 404);
        } catch (HMA_InternalServerErrorException $exception) {
            return new \WP_REST_Response([], 500);
        }

        return new \WP_REST_Response($response);
    }

    public function getMethod(): string
    {
        return \WP_REST_Server::READABLE;
    }
}
