<?php

namespace Node;

use Foundation\AndQuery;
use Foundation\Data;
use Foundation\EqualQuery;
use Foundation\NodeTrait;

class Relationship extends Data {
    protected $table = 'dudu_relationship';

    use NodeTrait;

    const STATUS_NONE = 'none';
    const STATUS_FOLLOWS = 'follows';
    const STATUS_WATCHES = 'watches';

    /**
     * @field
     */
    public $status;
    /**
     * @field
     */
    public $sourceId;
    /**
     * @field
     */
    public $targetId;

    /**
     * @return DriverInfo
     */
    public function getSource() {
        return (new DriverInfo())->load($this->sourceId);
    }

    public static function getInstance($sourceId, $targetId) {
        assert(!empty($sourceId));
        assert(!empty($targetId));

        $instance = current((new Relationship())->find(new AndQuery(
            new EqualQuery('sourceId', $sourceId)
            ,new EqualQuery('targetId', $targetId)
        )));

        if (empty($instance)) {
            $instance = new Relationship();
            $instance->sourceId = $sourceId;
            $instance->targetId = $targetId;
            $instance->status = Relationship::STATUS_NONE;
        }

        return $instance;
    }

    public static function modify($sourceId, $targetId, $action) {
        $outgoing = Relationship::getInstance($sourceId, $targetId);
        $incoming = Relationship::getInstance($targetId, $sourceId);
        switch ($action) {
            case 'watch':
                $outgoing->status = Relationship::STATUS_WATCHES;
                $outgoing->save();
                break;
            case 'follow':
                $outgoing->status = Relationship::STATUS_FOLLOWS;
                $outgoing->save();
                break;
            case 'none':
            case 'unwatch':
            case 'unfollow':
                $outgoing->status = Relationship::STATUS_NONE;
                $outgoing->save();
                break;
        }
        return [$outgoing, $incoming];
    }
}
