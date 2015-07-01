<?php
/**
 * 销售数据统计控制器
 * Created by PhpStorm.
 * User: Bin
 * Date: 15/6/25
 * Time: 下午6:15
 */

class StatisticController extends BaseController{

    public  function index()
    {
        $Sales = M('SalesAuth');
        $saleList = $Sales->select();

        $result = array();
        $ParkInfo = M('ParkInfo');
        foreach ($saleList as $key => $value) {
            $sid = $value['id'];
            $c_sum = $ParkInfo->where(array('responsible' => $sid))->count();
            $i_sum = $ParkInfo->where(array('responsible' => $sid, 'status' => array('EGT', 10)))->count();
            $n_sum = $ParkInfo->where(array('responsible' => $sid, 'style' => array('like', '%|BDWKF|%')))->count();
            $h_sum = $ParkInfo->where(array('responsible' => $sid, 'status' => array('in', '4,14')))->count();
            array_push($result, array($sid,$value['name'],$c_sum, $i_sum, $n_sum, $h_sum));
        }
        $this->saleList = $result;
        $this->display();

    }

}