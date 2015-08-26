<?php

namespace Node;

use Foundation\Data;
use Foundation\NodeTrait;

class ParkAdmin extends Data {
    protected $table = 'dudu_park_admin';

    use NodeTrait;

    /**
     * @field
     */
    public $phone;
}
