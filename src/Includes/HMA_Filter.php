<?php

namespace HearMe\Includes;

abstract class HMA_Filter extends HMA_Hook
{
    abstract public function getFilterName(): string;
}
