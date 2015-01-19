//dom加载完成后执行的js
;$(function(){ 

    //侧边栏导航条
    $('#side-menu').metisMenu();

    //加载正确的工具条窗口
    //在窗口大小改变时调整侧边栏
    $(window).bind("load resize", function() {
        console.log($(this).width())
        if ($(this).width() < 768) {
            $('div.sidebar-collapse').addClass('collapse')
        } else {
            $('div.sidebar-collapse').removeClass('collapse')
        }
    });    
    
    
    //启用表格功能   dataTables  
    $('#dataTables').dataTable({
        "sPaginationType" : "full_numbers",
        "oLanguage" : {
            "sLengthMenu": "每页显示 _MENU_ 条记录",
            "sZeroRecords": "抱歉， 没有找到",
            "sInfo": "从 _START_ 到 _END_ /共 _TOTAL_ 条数据",
            "sInfoEmpty": "没有数据",
            "sInfoFiltered": "(从 _MAX_ 条数据中检索)",
            "sZeroRecords": "没有检索到数据",
             "sSearch": "查询:",
            "oPaginate": {
            "sFirst": "首页",
            "sPrevious": "前一页",
            "sNext": "后一页",
            "sLast": "尾页"
            }
                
        }
    });
    

    //新增按钮  点击事件，跳转到 url 地址
    $(".btn-add").click(function(){
		location.href = $(this).attr('url');
	});       
    
    // 树展开收起 //
    $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', '收起分支');
    $('.tree li.parent_li > span').on('click', function (e) {
        var children = $(this).parent('li.parent_li').find(' > ul > li');
        if (children.is(":visible")) {
            children.hide('fast');
            $(this).attr('title', '展开分支').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
        } else {
            children.show('fast');
            $(this).attr('title', '收起分支').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
        }
        e.stopPropagation();
    });       
    
	//全选的实现
	$(".check-all").click(function(){
		$(".ids").prop("checked", this.checked);
	});
	$(".ids").click(function(){
		var option = $(".ids");
		option.each(function(i){
			if(!this.checked){
				$(".check-all").prop("checked", false);
				return false;
			}else{
				$(".check-all").prop("checked", true);
			}
		});
	});
    
    //table和 form联动的功能，点击表格中的行，自动的将表格中的值替换到 form 的input元素中去
    //需要联动的表 id 为 dataTables
    $("#dataTables tbody tr").click(function(){  
        $(this).find("td").each(function(i){  
            $("form>input").eq(i).val($(this).text());
        }); 
    })    
    
    
   //弹出框的配置 顶部 ，主题 block
    Messenger.options = {
        extraClasses: 'messenger-fixed messenger-on-top',
        theme: 'block' 
        
    };
    
    //updateAlert 提示信息显示条，可以替代系统的 alert ,样式主题和位置由 Messenger.options 定义
    window.updateAlert =function(message,type){  
        message = message||'出错了  :(';
		type = type||'error'; 
        Messenger().post({
            message: message ,
            type: type ,   
            showCloseButton: true ,            
            id: 'updateAlert'            
        });  
    };     	
  
 
    //updateConfirm  对话框，主程序 需要回调函数执行确认后的操作
    //和原生的 confirm  不同的地方是，不会有 true  flase 返回值
    window.updateConfirm = function(message, callback){
        message = message||'确认要执行该操作吗?';
        msg = Messenger().post({
          message: message ,
          showCloseButton : true ,          
          type: 'info' ,
          id : 'updateAlert' ,          
          actions: {
            confirm: {
              label: '   确定  ', 
              action: function() {    
                callback(true);//方法回调
                msg.hide();
                return false ;
              }
            },
            cancel: {
              label: '   取消    ',     
              action: function() {
                return msg.cancel();
              }
            }
          }
        });
    };    
  
    ////////////////////////////////////////////////////
    // ajaxQuery  函数
    //执行ajax  请求操作 ,
    //参数为:
    //elem 发起ajax请求的页面元素
    // url  请求服务端 url 地址 ,
    //method 请求的的方式，GET 操作还是 POST 操作
    //
    //执行的操作有:
    // 如果url 地址为空 ,返回
    //如果url 地址错误 ,调用ajax失败，返回  ajax执行失败
    //如果url 正确 ,调用ajax成功，由  updateAlert 返回提示信息
    //如果data为空，则执行get操作    
    //如果elem不为空，则页面重载或者设为可用
    //no_refresh 如果为真值，不执行页面重载。默认为 false ,在更新完数据后，重载本页，保持前后台数据一致 
    //服务端返回的 json 格式为，必须包含 status项    { "status":1,"info": "服务端返回的提示信息","url": "jumpUrl" }
    // jumpUrl 为跳转地址，例如  Admin/Index/index.html
    /////////////////////////////////////////////////////////////    
    window.ajaxQuery = function( elem , url , method  , data ){  
    
        url = url||false ;
        data = data||'' ;
        no_refresh = $(elem).hasClass('no-refresh') ||false ;  
        
        if (url == false){ 
            //将提交按钮设置为可用状态
            $(elem).removeClass('disabled').prop('disabled',false);   
            return false ;  //url地址为空时返回
        }        
        if ( method != 'GET' || method != 'get' ){
            method = 'POST' ;   //默认用POST  避免数据缓存的问题
        }  
        $.ajax({
            url      :  url ,
            type     :  method ,
            dataType :  'json' ,
            data     :  data  ,
            success  : function(data){   //ajax执行成功后的回调函数
              if (data.status==1) {
                if (data.url) {
                    updateAlert(data.info + ' 页面即将自动跳转~','success');
                }else{
                    updateAlert(data.info,'success');
                }
                setTimeout(function(){
                    if (data.url) {
                        location.href=data.url;
                    }else if( no_refresh ){
                        $(elem).removeClass('disabled').prop('disabled',false);   
                    }else{
                        location.reload();
                    }
                },1500);             
              } else {
                updateAlert(data.info,'error');
                setTimeout(function(){
                    if (data.url) {
                        location.href=data.url;
                    }else{
                        $(elem).removeClass('disabled').prop('disabled',false);   
                    }
                },1500);              
              }
            } ,
            error   : function(){
                updateAlert('执行ajax操作失败，请检查 URL 地址'+url,'warning');
                $(elem).removeClass('disabled').prop('disabled',false);  
            }         
        });      
    };     
 
    //点击  ajax-get 操作  
    $('.ajax-get').click(function(){
        var target;
        var that = this; 
        if ( (target = $(this).attr('href')) || (target = $(this).attr('url')) ) { 
            if ( $(this).hasClass('confirm') ) {
                updateConfirm('确认要执行该操作吗?(ajax-get)', function(opts){
                    //需要确认,才执行的ajax-post
                    $(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true); 
                    ajaxQuery( that, target ,'POST' );      //为了避免数据缓存造成的不一致性，可以统一改用POST    
                    //ajax-post结束点
                });        
            } else {
                    //不需要确认就可以执行的ajax-post
                    $(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true);
                    ajaxQuery( that, target ,'POST' );    //为了避免数据缓存造成的不一致性，可以统一改用POST           
                    //ajax-post结束点
            }           
        }
        return false;
    }); 

    //点击  ajax-post 操作 
    $('.ajax-post').click(function(){
        //初始值
        var target,query,form;
        var target_form = $(this).attr('target-form');
        var that = this;
        var nead_confirm=false;
        if( ($(this).attr('type')=='submit') || (target = $(this).attr('href')) || (target = $(this).attr('url')) ){
            form = $('.'+target_form);

            if ($(this).attr('hide-data') === 'true'){//无数据时也可以使用的功能
            	form = $('.hide-data');
            	query = form.serialize();
            }else if (form.get(0)==undefined){
            	return false;
            }else if ( form.get(0).nodeName=='FORM' ){ 
                if($(this).attr('url') !== undefined){
                	target = $(this).attr('url');
                }else{
                	target = form.get(0).action;
                }
                query = form.serialize();
            }else if( form.get(0).nodeName=='INPUT' || form.get(0).nodeName=='SELECT' || form.get(0).nodeName=='TEXTAREA') {
                form.each(function(k,v){
                    if(v.type=='checkbox' && v.checked==true){
                        nead_confirm = true;
                    }
                })
                query = form.serialize();
            }else{
                query = form.find('input,select,textarea').serialize();
            }
            
            if ( nead_confirm && $(this).hasClass('confirm') ) {
                updateConfirm('确认要执行该操作吗?(ajax-post)', function(opts){
                    //需要确认,才执行的ajax-post
                    $(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true); 
                    ajaxQuery( that, target ,'POST', query );         
                    //ajax-post结束点
                });        
            } else {
                    //不需要确认就可以执行的ajax-post
                    $(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true);
                    ajaxQuery( that, target ,'POST', query );         
                    //ajax-post结束点
            }           
        }
        return false;
    });          
 
 
//////////////////////公共js 结束点///////////////// 
}); 


////////////////////////

 
/* 上传图片预览弹出层 */
$(function(){
    $(window).resize(function(){
        var winW = $(window).width();
        var winH = $(window).height();
        $(".upload-img-box").click(function(){
        	//如果没有图片则不显示
        	if($(this).find('img').attr('src') === undefined){
        		return false;
        	}
            // 创建弹出框以及获取弹出图片
            var imgPopup = "<div id=\"uploadPop\" class=\"upload-img-popup\"></div>"
            var imgItem = $(this).find(".upload-pre-item").html();

            //如果弹出层存在，则不能再弹出
            var popupLen = $(".upload-img-popup").length;
            if( popupLen < 1 ) {
                $(imgPopup).appendTo("body");
                $(".upload-img-popup").html(
                    imgItem + "<a class=\"close-pop\" href=\"javascript:;\" title=\"关闭\"></a>"
                );
            }

            // 弹出层定位
            var uploadImg = $("#uploadPop").find("img");
            var popW = uploadImg.width();
            var popH = uploadImg.height();
            var left = (winW -popW)/2;
            var top = (winH - popH)/2 + 50;
            $(".upload-img-popup").css({
                "max-width" : winW * 0.9,
                "left": left,
                "top": top
            });
        });

        // 关闭弹出层
        $("body").on("click", "#uploadPop .close-pop", function(){
            $(this).parent().remove();
        });
    }).resize();

    // 缩放图片
    function resizeImg(node,isSmall){
        if(!isSmall){
            $(node).height($(node).height()*1.2);
        } else {
            $(node).height($(node).height()*0.8);
        }
    }
});
