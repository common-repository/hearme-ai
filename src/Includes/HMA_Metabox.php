<?php

namespace HearMe\Includes;

abstract class HMA_Metabox
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var string
     */
    protected $priority = 'default';

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

    public function getId(): string
    {
        if (!$this->id) {
            throw new \Exception('Metabox \'id\' property is not set!');
        }

        return $this->id;
    }

    public function getTitle(): string
    {
        if (!$this->title) {
            throw new \Exception('Metabox \'title\' property is not set!');
        }

        return $this->title;
    }

    public function getContext(): string
    {
        if (!$this->context) {
            throw new \Exception('Metabox \'context\' property is not set!');
        }

        return $this->context;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    abstract public function handle(): void;
}
