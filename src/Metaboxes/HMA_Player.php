<?php

namespace HearMe\Metaboxes;

use HearMe\Exceptions\HMA_NotFoundEpisodeException;
use HearMe\Exceptions\HMA_InternalServerErrorException;
use HearMe\Includes\HMA_Metabox;

class HMA_Player extends HMA_Metabox
{
    protected $id = 'hear_me_player';

    protected $title = '<img id="hear_me_box_logo" src="' . HEAR_ME_ABS_URL . '/assets/img/logo.svg" width="35" alt="HearMe Logo"> HearMe Player';

    protected $context = 'advanced';

    private $playerData;

    public function handle(): void
    {
        try {
            $episodeInfo = $this->connector->getEpisodeInfo(get_the_ID());
        } catch (HMA_NotFoundEpisodeException $exception) {
            $episodeInfo = null;
        } catch (HMA_InternalServerErrorException $exception) {
            $episodeInfo = null;
        }

        $player = [];
        if ($episodeInfo && $episodeInfo->isPublished === true) {
            $player = $this->connector->getEpisodePlayer(get_the_ID());
        }

        $this->playerData = $player ? $player->playerData : null;

        add_action('admin_footer', [$this, 'renderScript']);

        $this->twig->render('metaboxes/player', [
            'episode' => $episodeInfo,
            'post_id' => get_the_ID()
        ]);
    }

    public function renderScript()
    {
        if ($this->playerData) {
            $response = json_decode(json_encode($this->playerData), true);
            $response['playerStyle'] = json_encode($response['playerStyle']);

            $this->twig->render('player', $response);
        }
    }
}
