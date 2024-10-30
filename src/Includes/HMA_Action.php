<?php

namespace HearMe\Includes;

abstract class HMA_Action extends HMA_Hook
{
    abstract public function getActionName(): string;
}
