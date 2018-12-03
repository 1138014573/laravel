$(document).ready(function() {
    var dj = '';
    var coin ='';
    var key ='';
    //	第6列
    $('.table_s01 tbody td:nth-child(6) select').change(function () {
        dj = $(this);
        key = 6;
        coin = $(this).parent().prev().prev().prev().prev().prev().text();
        var ts = $(this).find("option:selected").val();
        if (confirm("确定要修改数据吗？"))
        {
            $.ajax({
                type: 'get',
                url: '/ajax/yjedit?value=' + ts+'&coin='+coin+'&key='+key,
                success: function (d) {
                    return;
                    try {
                        d = JSON.parse(d);
                    }catch (e){
                        d = eval(d);
                    }
                    if(d.status==1)
                    {
                        $(".selector").find("option[value='ts']").attr("selected",true);
                    }
                    else
                    {
                        alert(d.msg);
                    }
                },
                error: function (d) {
                    try {
                        d = JSON.parse(d);
                    }catch (e){
                        d = eval(d);
                    }
                    $("span").css({display: 'inline-block'});
                    alert(d.msg);
                }
            });
        }
    });

    var FIEX_DATA_BESE = false;
    // 处理修改 数据 方法
    function dealDataFn() {
        var fixedRes;
        // 修改
        if ($(this).data('fiextype') === 'sure' && !FIEX_DATA_BESE) {
            FIEX_DATA_BESE = true;
            fixedRes = $(this).siblings("input").val();
            var inputType = $(this).siblings("input").attr('type');
            // phone
            if (inputType === 'tel') {
                var reg = /^1[34578]\d{9}$/;
                if (fixedRes.length < 11) {
                    alert('电话号码长度不对');
                    return FIEX_DATA_BESE = false;
                } else if (!reg.test(fixedRes)) {
                    alert('电话号码格式不对');
                    return FIEX_DATA_BESE = false;
                }
            }
            // 获取当前列币种 code
            var coin = $(this).parents('tr').children("td:first-child").html(), // 币code
                ts = fixedRes,
                key = $(this).parents('td').data('col'),
                _this = this;
            // 百分百转小数
            if (key == '7') {
                ts = parseFloat(ts);
            }
            // 发送ajax success 显示 span
            $.ajax({
                type: 'get',
                // 值 币种类型 列数
                url: '/ajax/yjedit?value=' + ts+'&coin='+coin+'&key='+key,
                success: function (d) {
                    try {
                        d = JSON.parse(d);
                    }catch (e){
                        d = eval(d);
                    }
                    if(d.status == 1) {
                        sureFixed(_this, fixedRes);
                        FIEX_DATA_BESE = false;
                    }
                },
                error: function (d) {
                    try {
                        d = JSON.parse(d);
                    }catch (e){
                        d = eval(d);
                    }
                    alert(d.msg);
                    return FIEX_DATA_BESE = false;
                }
            });
        } else {
            //
            $(this).parent().siblings("span").show();
            $(this).parent().hide();
            return FIEX_DATA_BESE = false;
        }
    }
    //
    $('.table_s01 tbody button[data-fiextype]').click(function(e) {
        e.preventDefault && e.preventDefault();
        e.stopPropagation && e.stopPropagation();
        dealDataFn.call(this);
    });
    // 显示 修改 按钮 方法
    function showFixBtn(_this) {
        if ( $(_this).is(':visible') ) {
            // 显示 修改 按钮
            var val = $(_this).find('span[data-fixed="val"]').hide().html();
            var span = $(_this).find('span[data-modify]');
            $(span).find('input').val(val);
            $(span).show();
        }
    }
    // 隐藏 修改按钮
    function sureFixed(_this, fiexVal) {
        $(_this).parents('td').find('span[data-fixed="val"]').html(fiexVal).show();
        $(_this).parents('td').find('span[data-modify]').hide();
        alert('数据修改成功');
    }
    // 第7列
    $('.table_s01 tbody td:nth-child(7)').click(function () {
        showFixBtn(this);
    });
    // 第8列
    $('.table_s01 tbody td:nth-child(8)').click(function () {
        showFixBtn(this);
    });

    // 第9列
    $('.table_s01 tbody td:nth-child(9)').click(function () {
        dj = $(this);
        key = 9;
        coin = $(this).prev().prev().prev().prev().prev().prev().prev().prev().text();
        var ts=$(this).find('a').text();
        console.log(ts, coin, key);
        if (confirm("确定要修改数据吗？")) {
            if(ts == '关闭') {
                $.ajax({
                    type: 'get',
                    url: '/ajax/yjedit?value=' + ts+'&coin='+coin+'&key='+key,
                    success: function (d) {
                        try {
                            d = JSON.parse(d);
                        }catch (e){
                            d = eval(d);
                        }
                        if(d.status == 1) {
                            alert('成功');
                            dj.find('a').text('开启');
                        }
                    },
                    error: function (d) {
                        try {
                            d = JSON.parse(d);
                        }catch (e){
                            d = eval(d);
                        }
                        alert(d.msg);
                    }
                });
            } else {
                $.ajax({
                    type: 'get',
                    // 值 币种类型 列数
                    url: '/ajax/yjedit?value=' + ts+'&coin='+coin+'&key='+key,
                    success: function (d) {
                        try {
                            d = JSON.parse(d);
                        }catch (e){
                            d = eval(d);
                        }
                        if(d.status == 1) {
                            alert('成功');
                            dj.find('a').text('关闭');
                        }
                    },
                    error: function (d) {
                        try {
                            d = JSON.parse(d);
                        }catch (e){
                            d = eval(d);
                        }
                        alert(d.msg);
                    }
                });
            }
        }
    });

    // 隐藏
    $("span[data-modify] input").keyup(function (event){

        event = event || window.event;

        if(event.keyCode == 13) {

            // 获取当前输入input 的输入值
            var inpVal = $(event.target).val();

            var getBtn = $(this).siblings('button[data-fiextype]')[0];

            dealDataFn.call(getBtn);
        }
    });

});

