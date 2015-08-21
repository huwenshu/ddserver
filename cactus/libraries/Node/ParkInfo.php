<?php

namespace Node;

use Foundation\Data;
use Foundation\EqualQuery;
use Foundation\NodeTrait;

class ParkInfo extends Data {
    protected $table = 'dudu_park_info';

    const CORP_TYPE_正常预定 = 1;
    const CORP_TYPE_包月分销 = 2;

    const STATUS_测试中 = 3;
    const STATUS_已合作 = 4;

    use NodeTrait;

    /**
     * @field
     */
    public $name;

    /**
     * 合作方式：1-正常预定，2-包月分销
     *
     * @field
     */
    public $corp_type;

    /**
     * 合作状态：4-已合作，2-找到决策人，0-未接触，3-测试中, 1-在接触+信息化10
     * @field
     */
    public $status;

    /**
     * @return array
     */
    public function getParkAdmin() {
        return (new ParkAdmin())->find(new EqualQuery('parkid', $this->id));
    }
}
