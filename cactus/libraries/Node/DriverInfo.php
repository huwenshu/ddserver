<?php

namespace Node;

use Foundation\Data;
use Foundation\EqualQuery;
use Foundation\NodeTrait;

class DriverInfo extends Data {
    protected $table = 'dudu_driver_info';

    use NodeTrait;

    /**
     * @field
     */
    public $name;

    /**
     * @field
     */
    public $pushid;
}
