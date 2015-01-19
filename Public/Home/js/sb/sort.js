$(function(){
    //排序操作
    sort();
    $(".top").click(function(){
        rest();
        $("option:selected").prependTo("select");
        sort();
    })
    $(".bottom").click(function(){
        rest();
        $("option:selected").appendTo("select");
        sort();
    })
    $(".up").click(function(){
        rest();
        $("option:selected").after($("option:selected").prev());
        sort();
    })
    $(".down").click(function(){
        rest();
        $("option:selected").before($("option:selected").next());
        sort();
    })
    $(".search").click(function(){
        var v = $("input").val();
        $("option:contains("+v+")").attr('selected','selected');
    })
    function sort(){
        $('option').text(function(){return ($(this).index()+1)+'.'+$(this).text()});
    }

    //重置所有option文字。
    function rest(){
        $('option').text(function(){
            return $(this).text().split('.')[1]
        });
    }

    //获取排序并提交
    $('.sort_confirm').click(function(){
        var arr = new Array();
        $('.ids').each(function(){
            arr.push($(this).val());
        });
        $('input[name=ids]').val(arr.join(','));
        $.post(
            $('form').attr('action'),
            {
            'ids' :  arr.join(',')
            },
            function(data){
                if (data.status) {
                    updateAlert(data.info + ' 页面即将自动跳转~','alert-success');
                }else{
                    updateAlert(data.info,'alert-success');
                }
                setTimeout(function(){
                    if (data.status) {
                        $('.sort_cancel').click();
                    }
                },1500);
            },
            'json'
        );
    });

    //点击取消按钮
    $('.sort_cancel').click(function(){
        window.location.href = $(this).attr('url');
    });
})