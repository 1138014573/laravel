WVKE = {
    $: function(o) {
        return document.getElementById(o);
    },
    open: function(u) {
        WVKE.$('main_iframe').src = u;
    },
    menu: function(k) {
        var html = '<div class="menu">';
        for (var i = 0, max = menu[k].left.length; i < max; i++) {
            html += '<p' + (i ? '' : ' class="cur"') + ' onclick="WVKE.open(\'' + menu[k].left[i].url + '\')">' + menu[k].left[i].name + '</p>';
        }
        // console.log(html);
        this.open(menu[k].left[0].url);
        $('#left_bar').html(html + '</div>');
        $('.menu > p').bind("click", function() {
            $('.menu > p').removeClass("cur");
            $(this).addClass("cur");
        });
    },
    confirm: function(u, a) {
        var msg = { del: '删除操作不可恢复，确认要删除吗？', ok: '操作不可恢复，确认执行吗？' };
        if (confirm(msg[a])) {
            $.ajax({
                url: self.location.origin + u,
                type: 'GET', //GET
                async: true, //或false,是否异步  
                beforeSend: function(xhr) {
                    console.log('发送前');
                },
                success: function(data, textStatus, jqXHR) {
                      console.log('success');
                    //self.location.href = self.location.href;
                },
                error: function(xhr, textStatus) {
                    console.log('错误'); 
                },
                complete: function() {
                    console.log('结束');
                    self.location.href = self.location.origin + u;
                }
            }); 
        }
    },
    subchk: function(id, v) {
        $('#' + id + ' input').attr('checked', v);
    },
    blurpost: function(url, json, id) {
        var val = $('#' + id).val();
        if ($('#' + id).attr('title') == val) return;
        $.post(url, json, function() {
            $('#' + id).attr('title', $('#' + id).val());
        });
    }
};
