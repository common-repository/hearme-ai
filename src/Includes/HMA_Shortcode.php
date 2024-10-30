<?php

namespace HearMe\Includes;

abstract class HMA_Shortcode
{
    /**
     * @var string
     */
    protected $tag;

    /**
     * @var HMA_Twig
     */
    protected $twig;

    /**
     * @var HMA_Connector
     */
    protected $connector;

    public function __construct(HMA_Twig $twig, HMA_Connector $connector)
    {
        $this->twig = $twig;
        $this->connector = $connector;
    }

    public function getTag(): string
    {
        if (!$this->tag) {
            throw new \Exception('Shortcode \'tag\' property is not set!');
        }

        return $this->tag;
    }

    abstract public function handle($atts): string;
}
