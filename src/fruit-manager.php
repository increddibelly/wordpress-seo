<?php

namespace Yoast\WP\SEO;

class Fruit_Manager {
    public $fruit;

    public function __construct( Fruit_Interface ...$fruit ) {
        $this->fruit = $fruit;
    }
}
