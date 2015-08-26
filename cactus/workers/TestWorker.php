<?php

use Foundation\EqualQuery;
use Foundation\RangeQuery;
use Foundation\Worker;
use Node\ParkInfo;
use Node\Relationship;
use Utility\ChinaNet;

class TestWorker extends Worker {
    public function work() {
        $relations = new Relationship();
        $parks = $relations->find(new EqualQuery('spacesum', 1000));
        var_dump($parks);
    }
}

(new TestWorker())->doWork();
