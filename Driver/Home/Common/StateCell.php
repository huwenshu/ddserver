<?php
/**
 * 车位状态时间段node
 * @Bin
 * @2015-06-05
 */
class StateCell{
    public $starttime; //开始时间
    public $endtime; //结束时间
    public $state; //状态值 0:空闲，1：一般，2：满
    public $next; //下一节点指针

    public function __construct($starttime, $endtime, $state) {
        $this->starttime = $starttime;
        $this->endtime = $endtime;
        $this->state = $state;
        $this->next = null;
    }

    public static function currentCell($head){
        $nowTime = date("H:i:s");
        $current = $head;
        while(true){
            if($current->starttime < $current->endtime){
                if($nowTime > $current->starttime && $nowTime < $current->endtime){
                    break;
                }
                else{
                    $current = $current->next;
                    continue;
                }
            }
            else{
                if($nowTime > $current->starttime || $nowTime < $current->endtime){
                    break;
                }
                else{
                    $current = $current->next;
                    continue;
                }
            }
        }

        return $current;

    }
}

?>
