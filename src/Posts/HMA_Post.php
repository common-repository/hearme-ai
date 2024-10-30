<?php

namespace HearMe\Posts;

use HearMe\Includes\HMA_Post as BasePostClass;
use HearMe\Metaboxes\HMA_Player;

final class HMA_Post extends BasePostClass
{
    public function metaboxes(): array
    {
        return [
            HMA_Player::class
        ];
    }
}
