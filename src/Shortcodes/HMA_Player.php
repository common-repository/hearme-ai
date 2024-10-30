<?php

namespace HearMe\Shortcodes;

use HearMe\Exceptions\HMA_NotFoundEpisodeException;
use HearMe\Exceptions\HMA_InternalServerErrorException;
use HearMe\Includes\HMA_Connector;
use HearMe\Includes\HMA_Shortcode;
use HearMe\Includes\HMA_Twig;

class HMA_Player extends HMA_Shortcode
{
    protected $tag = 'hear_me_player';

    private $playerData;

    public function handle($atts): string
    {
        try {
            $player = $this->connector->getEpisodePlayer(get_the_ID());
        } catch (HMA_NotFoundEpisodeException $exception) {
            $player = null;
        } catch (HMA_InternalServerErrorException $exception) {
            $player = null;
        }

        $this->playerData = $player ? $player->playerData : null;

        add_action( 'wp_footer', [ $this, 'renderScript'] );

        return $this->twig->load('shortcodes/player');
    }

    public function renderScript()
    {
        if ($this->playerData) {
            $response = json_decode(json_encode($this->playerData), true);
            $response['playerStyle'] = json_encode($response['playerStyle']);

            $this->twig->render('player', $response);
        }
    }

    private function checkIfInContentOrIsAdmin()
    {
        $id = get_the_ID();
        $post = get_post($id);
        $content = '';
        if($post !== null) {
            $content = $post->post_content;
        }
        if ($post !== null && $post->post_type === 'info-centre') {
            $content = get_field('page_info_centre_main_content', $id) ? get_field('page_info_centre_main_content', $id) : $content;
        }
        return strpos($content, '[hear_me_player]') !== false || is_admin();
    }

    public function before_h(string $content, int $number, bool $hidePlayer)
    {
        if ($this->checkIfInContentOrIsAdmin() || $hidePlayer) {
            return $content;
        }

        $pos = 0;
        for ($i = 0; $i < $number; $i++) {
            $findPos = strpos($content, '<h', $pos ? $pos + 5 : 0);
            $pos = $findPos ?: $pos;
        }

        return substr($content, 0, $pos) . $this->handle([]) . substr($content, $pos);
    }

    public function after_h(string $content, int $number, bool $hidePlayer)
    {
        if ($this->checkIfInContentOrIsAdmin() || $hidePlayer) {
            return $content;
        }

        $pos = 0;
        for ($i = 0; $i < $number; $i++) {
            $findPos = strpos($content, '</h', $pos);
            $pos = $findPos ? $findPos + 5 : $pos;
        }

        return substr($content, 0, $pos) . $this->handle([]) . substr($content, $pos);
    }

    public function before_p(string $content, int $number, bool $hidePlayer)
    {
        if ($this->checkIfInContentOrIsAdmin() || $hidePlayer) {
            return $content;
        }

        $pos = 0;
        for ($i = 0; $i < $number; $i++) {
            $findPos = strpos($content, '<p>', $pos ? $pos + 4 : 0);
            $pos = $findPos ?: $pos;
        }

        return substr($content, 0, $pos) . $this->handle([]) . substr($content, $pos);
    }

    public function after_p(string $content, int $number, bool $hidePlayer)
    {
        if ($this->checkIfInContentOrIsAdmin() || $hidePlayer) {
            return $content;
        }

        $pos = 0;
        for ($i = 0; $i < $number; $i++) {
            $findPos = strpos($content, '</p>', $pos);
            $pos = $findPos ? $findPos + 4 : $pos;
        }

        return substr($content, 0, $pos) . $this->handle([]) . substr($content, $pos);
    }

    public function before_t(string $content, int $number)
    {
        if ($this->checkIfInContentOrIsAdmin()) {
            return $content;
        }

        return $this->handle([]) . $content;
    }

    public function after_t(string $content, int $number, bool $hidePlayer)
    {
        if ($this->checkIfInContentOrIsAdmin() || $hidePlayer) {
            return $content;
        }

        return $content . $this->handle([]);

    }
}
