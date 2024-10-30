<?php

namespace HearMe\Includes;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

final class HMA_Twig
{
    public function render($templateName, $data = [])
    {
        $twig = $this->prepareTwig();
        $data['uniqueIdentifier'] = round(microtime(true) * 1000);

        // Twig doing autoescape in default (in template every variable is escaping)
        echo $twig->load($templateName . '.html.twig', [])->render($data);
    }

    public function load($templateName, $data = [])
    {
        $twig = $this->prepareTwig();
        $data['uniqueIdentifier'] = round(microtime(true) * 1000);

        // Twig doing autoescape in default (in template every variable is escaping)
        return $twig->load($templateName . '.html.twig', [])->render($data);
    }

    private function prepareTwig(): Environment
    {
        $loader = new FilesystemLoader(HEAR_ME_ABS_PATH . DIRECTORY_SEPARATOR . 'templates');
        $twig = new Environment($loader, [
            'debug' => true
        ]);

        $filter = new TwigFilter('translate', function ($key) {
            return HMA_Translations::get($key);
        });

        $twig->addFilter($filter);
        $twig->addExtension(new \Twig\Extension\DebugExtension());

        return $twig;
    }
}
