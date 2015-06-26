<?php
/**

 * 规则说明控制器
 * @Bin
 */


class RulesController extends BaseController{

    public function index(){
        if (IS_POST) {
            $content = htmlspecialchars_decode(I('post.rules'));
            $file = C('PARK_RULES_PATH');
            file_put_contents($file, $content);
            $this->redirect('Rules/index');
        }
        else{
            $file = C('PARK_RULES_PATH');
            $content = file_get_contents($file);
            $this->content = $content;
            $this->is_editor = UID == 7 ? 1:0;
            $this->meta_title = '规则说明 | 嘟嘟销售系统';
            $this->display();
        }
    }

}
