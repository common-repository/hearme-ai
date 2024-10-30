<?php

namespace HearMe\Includes;

abstract class HMA_Hook
{
    /**
     * @var HMA_Twig
     */
    protected $twig;

    abstract public function handle();

    public function __construct(HMA_Twig $twig)
    {
        $this->twig = $twig;
    }
}
