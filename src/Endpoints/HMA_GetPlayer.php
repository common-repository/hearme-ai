<?php

namespace HearMe\Endpoints;

use HearMe\Exceptions\HMA_NotFoundEpisodeException;
use HearMe\Includes\HMA_Endpoint;
use HearMe\Includes\HMA_Translations;

class HMA_GetPlayer extends HMA_Endpoint
{
    protected $uri = 'episode/player';

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
            $response = $this->connector->getEpisodePlayer($post->ID);
        } catch (HMA_NotFoundEpisodeException $exception) {
            return new \WP_REST_Response([], 404);
        }

        $response = json_decode(json_encode($response->playerData), true);
        $response['playerStyle'] = json_encode($response['playerStyle']);

        return new \WP_REST_Response([
            'script' => $this->twig->load('playerJS', $response),
            'scriptFile' => HEAR_ME_SCRIPT_URL . 'player.js'
        ]);
    }

    public function getMethod(): string
    {
        return \WP_REST_Server::READABLE;
    }
}
