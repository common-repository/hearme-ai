<?php

namespace HearMe\Includes;

abstract class HMA_Endpoint
{
    protected $uri;

    /**
     * @var HMA_Connector
     */
    protected $connector;

    /**
     * @var HMA_Twig
     */
    protected $twig;

    public function __construct(HMA_Connector $connector, HMA_Twig $twig)
    {
        $this->connector = $connector;
        $this->twig = $twig;
    }

    public function getUri(): string
    {
        if (!$this->uri) {
            throw new \Exception('Endpoint \'uri\' property is not set!');
        }

        return $this->uri;
    }

    public function callback(\WP_REST_Request $request): \WP_REST_Response
    {
        if (!wp_verify_nonce( $request->get_header('X-WP-Nonce'), 'wp_rest' )) {
            return new \WP_REST_Response([
                [
                    "code" => "rest_cookie_invalid_nonce",
                    "message" => HMA_Translations::get('rest_cookie_invalid_nonce'),
                    "data" => [
                        "status" => 403
                    ]
                ]
            ], 403);
        }

        return $this->handle($request);
    }

    abstract protected function handle(\WP_REST_Request $request): \WP_REST_Response;

    abstract public function getMethod(): string;
}
