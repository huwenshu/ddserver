<?php

namespace Node;

use DateTime;
use Foundation\Data;
use Foundation\NodeTrait;

class ParkOrder extends Data {
    protected $table = 'dudu_park_order';

    use NodeTrait;

    /**
     * @field
     */
    public $name;

    /**
     * ParkInfo ID
     * @field
     */
    public $pid;
    /**
     * @field
     */
    public $carid;

    /**
     * @type DateTime
     * @field
     */
    public $endtime;

    /**
     * 订单状态：0-已预付，未进场 1-已预付，在场 2-付清，准备离场 3-离场
     *
     * @type int
     * @field
     */
    public $state;

    /**
     * @return ParkInfo
     */
    public function getParkInfo() {
        return (new ParkInfo())->load($this->pid);
    }
}
