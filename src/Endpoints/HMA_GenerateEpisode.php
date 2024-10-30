<?php

namespace HearMe\Endpoints;

use HearMe\Includes\HMA_Endpoint;
use HearMe\Includes\HMA_Translations;

class HMA_GenerateEpisode extends HMA_Endpoint
{
    private $regex = [
        'heading' => '/<h\d(.*)>(.*)<\/h\d>/mU',
        'quote' => '/<blockquote(.*)>(.*)<\/blockquote>/mU',
        'preformatted' => '/<pre(.*)>(.*\s*)<\/pre>/mU',
        'text' => '/^([a-zA-z0-9\ ])*$/mU',
        'list' => '/<ul>(.*)<\/ul>/mU',
        'paragraph' => '/^<p>(.*)<\/p>$/mU',
    ];

    protected $uri = 'episode/generate';

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

        $response = $this->generateEpisode($postId);

        return new \WP_REST_Response($response);
    }

    public function generateEpisode($postId)
    {
        $post = get_post($postId);

        $content = apply_filters( 'the_content', $post->post_content );

        $postType = $post->post_type;

        if ($postType === 'info-centre') {
            $content = get_field('page_info_centre_main_content', $postId) ? get_field('page_info_centre_main_content', $postId) : $content;
        }

        $matches = [];

        $br_regex = '/<br(.*)>/U';
        $content = preg_replace($br_regex, ' ', $content);

        $checkSum = md5(json_encode($content));
        $checkSumMeta = get_post_meta($postId, 'hear_me_checksum', true);
        $title = get_post_meta($postId, 'hear_me_title', true);
        $author = get_post_meta($postId, 'hear_me_author', true);

        $user = get_user_by('id', $post->post_author);

        if ($checkSumMeta !== $checkSum || $title !== $post->post_title || $author !== $user->display_name) {
            update_post_meta($postId, 'hear_me_checksum', $checkSum);
            update_post_meta($postId, 'hear_me_title', $post->post_title);
            update_post_meta($postId, 'hear_me_author', $user->display_name);

            $response = $this->connector->sendEpisodeDraft(
                $post->ID,
                (new \DateTime($post->post_date))->format(\DateTime::ATOM),
                $content,
                $post->post_title,
                $user->display_name
            );
        } else {
            $response = ['status' => 'not_found_changes', 'ok' => false];
        }

        return $response;
    }

    private function parseContent($type, $matches): array
    {
        $parsedData = [];
        switch ($type) {
            case 'heading':
                $parsedData = $this->parseHeading($matches[0]);
                break;
            case 'list':
                $parsedData = $this->parseList($matches[1]);
                break;
            case 'paragraph':
                $parsedData = $this->parseParagraph($matches[1]);
                break;
            case 'quote':
                $parsedData = $this->parseQuote($matches[0]);
                break;
        }

        return $parsedData;
    }

    private function parseHeading($matches): array
    {
        $localMatches = $parsedMatches = [];
        foreach ($matches as $number => $match) {
            preg_match_all('/<h(\d.*)>(.*)<\/h\d>/mU', $match[0], $localMatches);

            if (!$localMatches[2][0]) {
                continue;
            }

            $parsedMatches[] = [
                'offset' => $match[1],
                'data' => [
                    'id' => 'h' . (++$number),
                    'type' => 'header',
                    'level' => $localMatches[1][0],
                    'text' => trim(strip_tags($localMatches[2][0]), '>')
                ]
            ];
        }

        return $parsedMatches;
    }

    private function parseList($matches): array
    {
        $localMatches = $parsedMatches = [];
        foreach ($matches as $number => $match) {
            if (!$match[0]) {
                continue;
            }

            $id = 'l' . (++$number);
            $parsedMatches[] = [
                'offset' => $match[1],
                'data' => [
                    'id' => $id,
                    'type' => 'list',
                ]
            ];

            preg_match_all('/<li>(.[^<]*)<\/li>/m', $match[0], $localMatches);

            foreach ($localMatches[1] as $localNumber => $localMatch) {
                if (!$localMatch) {
                    continue;
                }

                ++$localNumber;
                $parsedMatches[] = [
                    'offset' => $match[1] + $localNumber,
                    'data' => [
                        'id' => $id . '.' . $localNumber,
                        'parent' => $id,
                        'type' => 'list-item',
                        'text' => $localMatch
                    ]
                ];
            }
        }

        return $parsedMatches;
    }

    private function parseParagraph($matches): array
    {
        $parsedMatches = [];
        foreach ($matches as $number => $match) {
            if (!$match[0]) {
                continue;
            }

            $parsedMatches[] = [
                'offset' => $match[1],
                'data' => [
                    'id' => 'p' . (++$number),
                    'type' => 'paragraph',
                    'text' => trim(strip_tags($match[0]))
                ]
            ];
        }

        return $parsedMatches;
    }

    private function parseQuote($matches): array
    {
        $parsedMatches = [];
        foreach ($matches as $number => $match) {
            preg_match('/<p>(.*)<\/p>/m', $match[0], $localP);
            preg_match('/<cite>(.*)<\/cite/m', $match[0], $localCite);

            $text = isset($localP[1]) && isset($localCite[1]) ? ($localP[1] . ' - ' . $localCite[1]) : strip_tags($match[0]);

            if (!$text) {
                continue;
            }

            $parsedMatches[] = [
                'offset' => $match[1],
                'data' => [
                    'id' => 'q' . (++$number),
                    'type' => 'quote',
                    'text' => $text
                ]
            ];
        }

        return $parsedMatches;
    }

    public function getMethod(): string
    {
        return \WP_REST_Server::CREATABLE;
    }
}
