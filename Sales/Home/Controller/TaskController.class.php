<?php
/**
 * 销售扫街V3.0控制器
 * Created by PhpStorm.
 * User: Bin
 * Date: 15/7/20
 * Time: 下午01:15
 */

class TaskController extends BaseController{

    public  function index()
    {

        $searchname = I('get.searchname');
        $marks = I('get.marks');
        $state = I('get.taskstate');

        $TaskParkInfo = M('TaskParkInfo');
        $landmark =$TaskParkInfo->distinct(true)->field('landmark')->select();

        $con = $this->condition($searchname,$marks,$state);
        $tasks = $TaskParkInfo->where($con)->order('landmark,_address')->select();

        $Parttime = M('PartTime');
        $con = array();
        $con['iswork'] = 1;
        $partimes = $Parttime->where($con)->select();

        $this->landmark = $landmark;
        $this->searchname = $searchname;
        $this->marks = empty($marks) ? array():$marks;
        $this->state = empty($state) ? array():$state;
        $this->tasks = empty($tasks) ? array():$tasks;
        $this->partimes = $partimes;
        $this->display();
    }


    public  function import()
    {
        if(IS_POST){
            $json = html_entity_decode(I('post.data'));
            $jsondata = json_decode($json,true);
            $parks = $jsondata['data'];
            $data = array();
            foreach($parks as $key => $value){
                foreach($value['places'] as $k => $v){
                    $t = array();
                    $t['landmark'] = $value['center'][0];
                    $t['m_id'] = $v['id'];
                    $t['_type'] = array_key_exists("type",$v) ? $v['type']:'';
                    $t['_name'] = array_key_exists("name",$v) ? $v['name']:'';
                    $t['_address'] = array_key_exists("address",$v) ? $v['address']:'';
                    $t['_lat'] = array_key_exists("location",$v) ? $v['location'][1]:0.0;
                    $t['_lng'] = array_key_exists("location",$v) ? $v['location'][0]:0.0;
                    $t['_tags'] = array_key_exists("tags",$v) ? $this->arrayToString($v['tags']):'';
                    $t['_prov'] = array_key_exists("prov",$v) ? $v['prov']:'';
                    $t['_city'] = array_key_exists("city",$v) ? $v['city']:'';
                    $t['_dist'] = array_key_exists("dist",$v) ? $v['dist']:'';
                    $t['_area'] = array_key_exists("area",$v) ? $v['area']:'';
                    $t['_parking'] = array_key_exists("parking",$v) ? $v['parking']:'';
                    $t['_proprietor'] = array_key_exists("proprietor",$v) ? $v['proprietor']:'';
                    $t['_propertyCompany'] = array_key_exists("propertyCompany",$v) ? $v['propertyCompany']:'';
                    $t['_builtyear'] = array_key_exists("builtyear",$v) ? $v['builtyear']:'';
                    $t['_source'] = array_key_exists("source",$v) ? $v['source']:'';
                    $t['createtime'] = date('Y-m-d H:i:s');
                    $t['status'] = 0;
                    array_push($data, $t);
                }
            }

            $result = array();

            $TaskParkInfo = M('TaskParkInfo');
            if(empty($data)){
                $result['code'] = 100;
                $result['data'] = '';
            }
            else{
                $TaskParkInfo->addAll($data);
                $result['code'] = 200;
                $result['data'] = 'OK';
            }

            echo json_encode($result);
        }
        else{
            $this->display();
        }
    }

    public  function preimport()
    {
        $locinput = I('get.locinput');
        $distance = I('get.distance');
        $data = array();
        $data['locinput'] = $locinput;
        $data['distance'] = $distance;
        $url = 'http://samui.knows.io/place_exporter';

        $json = $this->doCurlGetRequest($url, $data);
        $arr = json_decode($json,true)['data'];

        //去重
        $existMIDS = array();
        foreach($arr as $value){
            $existMIDS =array_merge($existMIDS,$this->existPark($value['center'][2],$value['center'][1],$distance));
        }
        $jsonarr = array();
        $data = array();
        foreach($arr as $v1){
            $t = array();
            $t['center'] = $v1['center'];
            $p = array();
            foreach($v1['places'] as $v2){
                if(in_array($v2['id'], $existMIDS)){

                }
                else{
                    array_push($p, $v2);
                }
            }
            $t['places'] = $p;
            array_push($data, $t);
        }
        $jsonarr['meta'] = array('code'=>200, 'resp'=>'OK');
        $jsonarr['data'] = $data;


        echo json_encode($jsonarr);

    }

    public function tparkinfo(){
        if(IS_POST){
            $id = I('post.tpid');
            $search = I('get.search');
            $_name = I('post._name');
            $_tags = I('post._tags');
            $_address = I('post._address');
            $_lat = I('post._lat');
            $_lng = I('post._lng');
            $status = I('post.status');

            $TaskParkInfo = M('TaskParkInfo');
            $data = array();
            $data['id'] = $id;
            $data['_name'] = $_name;
            $data['_tags'] = $_tags;
            $data['_address'] = $_address;
            $data['_lat'] = $_lat;
            $data['_lng'] = $_lng;
            $data['status'] = $status;
            $data['updater'] = UID;
            $TaskParkInfo->save($data);

            $this->redirect('Task/tparkinfo', array('tpid'=>$id, 'search'=>$search));
        }
        else{
            $tpid = I('get.tpid');
            $search = I('get.search');
            $search = urldecode($search);
            $arr = explode('|',$search);
            $searchname = $arr[0];
            $marks = $arr[1]==''? array():explode(',', $arr[1]);
            $state = $arr[2]==''? array():explode(',', $arr[2]);
            $con = $this->condition($searchname,$marks,$state);
            $TaskParkInfo = M('TaskParkInfo');
            $ids = $TaskParkInfo->where($con)->order('landmark,_address')->getField('id', true);
            $key = array_search($tpid, $ids);
            if($key+1 == count($ids)){
                $nextid = -1;
            }
            else{
                $nextid = $ids[$key+1];
            }

            $TaskParkInfo = M('TaskParkInfo');
            $map = array();
            $map['id'] = $tpid;
            $tpark = $TaskParkInfo->where($map)->find();

            $this->title = '停车场预审';
            $this->nextid = $nextid;
            $this->search = $search;
            $this->tpark = $tpark;
            $this->display();
        }
    }

    public function allocate(){
        $id = I('post.id');
        $name = I('post.name');
        $pwd = I('post.pwd');
        $parks = I('post.parks');

        //先处理兼职信息
        $Parttime = M('PartTime');
        $data = array();
        if(empty($id)){
            $data['name'] = $name;
            $data['pwd'] = $pwd;
            $data['iswork'] = 1;
            $data['creater'] = UID;
            $data['createtime'] = date('Y-m-d H:i:s');
            $data['updater'] = UID;
            $id = $Parttime->add($data);
        }
        else{
            $data['id'] = $id;
            $data['name'] = $name;
            $data['pwd'] = $pwd;
            $data['updater'] = UID;
            $Parttime->save($data);
        }

        $TaskParkInfo = M('TaskParkInfo');
        $map = array();
        $map['id'] = array('in', $parks);
        $map['status'] = 1;
        $data = array();
        $data['status'] = 2;
        $data['partime'] = $id;
        $data['allocatedate'] = date('Y-m-d');
        $data['updater'] =UID;
        $t  = $TaskParkInfo->where($map)->save($data);
        if($t === false){
            $jsonarr['meta'] = array('code'=>500, 'resp'=>'更新错误');
        }
        else{
            $jsonarr['meta'] = array('code'=>200, 'resp'=>'OK');
        }
        echo json_encode($jsonarr);
    }

    public function partimes(){
        $TaskParkInfo = M('TaskParkInfo');
        $pts = $TaskParkInfo->distinct(true)->field('partime')->order('allocatedate desc')->select();
        $ptss =array();
        foreach ($pts as $value) {
            if(!empty($value['partime'])){
                array_push($ptss, $value['partime']);
            }
        }

        $Partime = M('PartTime');
        $map = array();
        $map['id'] = array('not in', $ptss);
        $leftPT= $Partime->where($map)->order('iswork desc')->getField('id', true);

        $partimes = array();
        foreach($ptss as $v){
            $map['id'] = $v;
            $temp = $Partime->where($map)->find();
            array_push($partimes, $temp);
        }
        foreach($leftPT as $v){
            $map['id'] = $v;
            $temp = $Partime->where($map)->find();
            array_push($partimes, $temp);
        }

        $this->title = '兼职列表';
        $this->partimes = $partimes;
        $this->display();

    }


    public function  partime($partid){
        $TaskParkInfo = M('TaskParkInfo');
        $undo_parks = $TaskParkInfo->where(array('status' => 2, 'partime' => $partid))->order('allocatedate asc, _address')->select();
        $done_parks = $TaskParkInfo->where(array('status' => 3, 'partime' => $partid))->order('allocatedate asc, _address')->select();

        $Partime = M('PartTime');
        $partime = $Partime->where(array('id' => $partid))->find();

        $this->undo_parks = $undo_parks;
        $this->done_parks = $done_parks;
        $this->partime = $partime;
        $this->title = '兼职信息';
        $this->display();

    }

    public function  checkpark(){

        if(IS_POST){
            $data = array();
            $data['id'] = I('post.tpid');
            $data['name'] = I('post.name');
            $data['address'] = I('post.address');
            $data['lat'] = I('post.lat');
            $data['lng'] = I('post.lng');
            $styles = I('post.parkstyle');
            $parkstyle = "|";
            foreach ($styles as $key => $value) {
                $parkstyle = $parkstyle.$value.'|';
            }
            $data['style'] = $parkstyle;
            $data['parking'] = I('post.parking');
            $data['opentime'] = I('post.opentime');
            $data['startwork'] = I('post.startwork');
            $data['endwork'] = I('post.endwork');
            $data['startweek'] = I('post.startweek');
            $data['endweek'] = I('post.endweek');
            $data['chargingrules'] = I('post.chargingrules');
            $data['note'] = I('post.note');
            $data['intention'] = I('post.intention');
            $data['status'] = I('post.status');

            $TaskParkInfo = M('TaskParkInfo');
            $TaskParkInfo->save($data);

            if(I('post.status') == 3){
                $parkInfo = array();
                $parkInfo['name'] = $data['name'];
                $parkInfo['address'] = $data['address'];
                $parkInfo['lat'] = $data['lat'];
                $parkInfo['lng'] = $data['lng'];
                $parkInfo['style'] = $data['style'];
                $parkInfo['spacesum'] = $data['parking'];
                $parkInfo['opentime'] = $data['opentime'];
                $parkInfo['startmon'] = $data['startwork'];
                $parkInfo['starttue'] = $data['startwork'];
                $parkInfo['startwed'] = $data['startwork'];
                $parkInfo['startthu'] = $data['startwork'];
                $parkInfo['startfri'] = $data['startwork'];
                $parkInfo['startsat'] = $data['startweek'];
                $parkInfo['startsun'] = $data['startweek'];
                $parkInfo['endmon'] = $data['endwork'];
                $parkInfo['endtue'] = $data['endwork'];
                $parkInfo['endwed'] = $data['endwork'];
                $parkInfo['endthu'] = $data['endwork'];
                $parkInfo['endfri'] = $data['endwork'];
                $parkInfo['endsat'] = $data['endweek'];
                $parkInfo['endsun'] = $data['endweek'];
                $parkInfo['chargingrules'] = $data['chargingrules'];
                $corp = array('无意愿','一般','强烈');
                $corpstr = '合作意愿:'.$corp[I('post.intention')];
                $parkInfo['note'] = $data['note'].'  '.$corpstr;
                $parkInfo['status'] = 10;

                $parkInfo['responsible'] = UID;
                $PinYin = new Home\Common\PinYin();
                $pinYin = strtoupper($PinYin->getFirstPY($parkInfo['name']));
                $shortName = $this->getShort($pinYin, 0);
                $parkInfo['shortname'] = $shortName;

                $parkInfo['creater'] = 'PT-'.$data['id'];
                $parkInfo['createtime'] = date('Y-m-d H:i:s');
                $parkInfo['updater'] = UID;
                $parkInfo['updatetime'] = date('Y-m-d H:i:s');

                $ParkInfo = M('ParkInfo');
                $ParkInfo->add($parkInfo);
            }


            $this->redirect('Task/checkpark', array('partid'=>I('post.partid'), 'tpid'=>I('post.tpid')));

        }
        else{
            $partid = I('get.partid');
            $tpid = I('get.tpid');

            $TaskParkInfo = M('TaskParkInfo');
            $undo_ids = $TaskParkInfo->where(array('status' => 2, 'partime' => $partid))->order('allocatedate asc, _address')->getField('id', true);
            $done_ids = $TaskParkInfo->where(array('status' => 3, 'partime' => $partid))->order('allocatedate asc, _address')->getField('id', true);

            $key = array_search($tpid, $undo_ids);
            if($key === false){
                $key = array_search($tpid, $done_ids);
                if($key+1 == count($done_ids)){
                    $nextid = -1;
                }
                else{
                    $nextid = $done_ids[$key+1];
                }
            }
            else{
                if($key+1 == count($undo_ids)){
                    $nextid = -1;
                }
                else{
                    $nextid = $undo_ids[$key+1];
                }
            }

            $tpark = $TaskParkInfo->where(array('id'=>$tpid))->find();

            $this->tpark = $tpark;
            $this->nextid = $nextid;
            $this->title = '停车场信息 | 扫街';
            $this->display();

        }
    }


    //返回两倍范围内的所有停车场 mongoid
    protected function existPark($lat,$lng,$distance){
        $TaskParkInfo = M('TaskParkInfo');
        $gap = 0.009090*$distance*2;
        $con = '_lat > '.($lat-$gap).' AND _lat<'.($lat+$gap);
        $con .= ' AND _lng > '.($lng-$gap).' AND _lng<'.($lng+$gap);
        $result = $TaskParkInfo->where($con)->getField('m_id',true);
        return  empty($result) ? array():$result;
    }

    protected function condition($searchname, $marks, $state){
        $con = '';
        if(!empty($searchname)){
            $con .= '_name like "%'.$searchname.'%" or _address like "%'.$searchname.'%" ';
        }
        if(!empty($marks)){
            if(!empty($con)){
                $con .= ' AND';
            }
            $con .='(';
            foreach($marks as $key => $value){
                if($key == count($marks)-1){
                    $con .= 'landmark = "'.$value.'"';
                }
                else{
                    $con .= 'landmark = "'.$value.'" or ';
                }
            }
            $con .=')';
        }
        if(!empty($state)){
            if(!empty($con)){
                $con .= ' AND ';
            }
            $con .='(';
            foreach($state as $key => $value){
                if($key == count($state)-1){
                    $con .= 'status = '.$value;
                }
                else{
                    $con .= 'status = '.$value.' or ';
                }
            }
            $con .=')';
        }
        return $con;
    }


    //找出一个没有重复的停车场缩写
    private function getShort($pinyin, $i=0){
        if($i == 0){
            $str = $pinyin;
        }
        else{
            $str = $pinyin.$i;
        }

        $ParkInfo = M('ParkInfo');
        $map = array();
        $map['shortname'] = $str;
        $park = $ParkInfo->where($map)->find();
        if(is_array($park)){
            $i++;
            return $this->getShort($pinyin,$i);
        }
        else{
            return $str;
        }

    }

}