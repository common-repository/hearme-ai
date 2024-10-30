<?php

namespace HearMe\Includes;

abstract class HMA_Post
{

    /**
     * @var mixed[]
     */
    private $options;

    protected $type = ['post'];

    public function getType(): array
    {
        $post_types = $this->type;
        $this->options = get_option('hear_me_options');
        if(isset($this->options['supported_posts']))
            $post_types = array_merge($this->type, $this->options['supported_posts']);
        return $post_types;
    }

    abstract public function metaboxes(): array;
}
