// 渲染月份选择选项
function randeMonth(len) {
  // 清空
  $('.month_set').html('');
  var options = '<option value="">-请选择月份-</option>';
  for (var i = 1; i <= len; i++) {
    options += '<option value="' + i + '">' + i +'月</option>';
  }
  $('.month_set').append(options);
}
var monthLen = new Date();
randeMonth(monthLen.getMonth()+1);
// 默认选择当月
$('.month_set').val((new Date()).getMonth()+1);
// 设置时间输入框默认显示
(function(){
  var resTime = getThisMonthDate();
  $("#startTime").val(resTime.startDate);
  $("#endTime").val(resTime.lastDate);
}());
// 设置时间
function setTime() {
  var res = getThisMonthDate();
  var lastTime = (new Date(res.lastDate)).getTime(),
  startTime = (new Date(res.startDate)).getTime(),
  nowTime = (new Date()).getTime();
  if (startTime > nowTime || lastTime > nowTime) {
    $(this).find('option[value=""]').attr('selected', 'selected');
    return ;
  }
  $('#startTime').val(res.startDate);
  $('#endTime').val(res.lastDate);
}
$('.month_set').change(function(){
  setTime.call(this);
});
$('.year_set').change(function(){
  // 记录当前选择的月份
  var selMonth = $(".month_set").val();
  // 根据不同的年份 渲染不同的
  if ($(this).val() < (new Date()).getFullYear()) {
    randeMonth(12);
  } else {
    randeMonth((new Date()).getMonth()+1);
  }
  if (selMonth <= (new Date()).getMonth()+1) {
    $('.month_set option[value="'+selMonth+'"]').attr("selected", 'selected');
  }
  setTime.call(this);
});
// 获取当月时间开始结束日期时间
function getThisMonthDate() {
  var month = $('.month_set').val() || 1;
  var year = $('.year_set').val() || 2017;
  var res = {};
  month = month < 10 ? ('0'+month) : month;
  res.startDate = year + '-' + month + '-01 00:00';
  //
  var nextMonthStartDay = (new Date(year +'-'+ (parseInt(month) + 1) + '-01 00:00')).getTime();
  var lastDate = (new Date(nextMonthStartDay - 1));
  if (lastDate < (new Date()).getTime()) {
    lastDate = lastDate.getDate();
    lastDate = lastDate < 10 ? '0' + lastDate : lastDate;
    res.lastDate = year + '-' + month + '-' + lastDate + ' 23:59';
  } else {
    lastDate = new Date();
    var nLastDate = lastDate.getDate() < 10 ? '0' + lastDate.getDate() : lastDate.getDate(),
      hour = lastDate.getHours() < 10 ? '0'+lastDate.getHours() : lastDate.getHours(),
      minute = lastDate.getMinutes() < 10 ? '0' + lastDate.getMinutes() : lastDate.getMinutes();
    res.lastDate = year + '-' + month + '-' + nLastDate + ' '+ hour + ":" + minute;
  }
  return res;
}
function momentTime(ct) {
  var year = ct.getFullYear(),
    month = ct.getMonth() > 9 ? ct.getMonth() : '0' + ct.getMonth(),
    date = ct.getDate() > 9 ? ct.getDate : '0' + ct.getDate(),
    hour = ct.getHours() > 9 ? ct.getHours() : '0' + ct.getHours(),
    minute = ct.getMinutes() > 9 ? ct.getMinutes() : '0' + ct.getMinutes();
  return year + (month+1) + date + ' ' + hour + ":" + minute;
}
// 时间初始化
function timeSelect(ct, elem, where) {
  var resTime = getThisMonthDate();
  var nowTime = new Date();
  var  time = momentTime(ct);
  if (ct.getTime() <= nowTime.getTime()) {
    $(elem).val(time);
  }
  return time;
}
function setAllowTimes() {
  var allowTimes = ['00:00'];
  for (var tm = 0;tm < 24; tm++) {
    allowTimes.push(tm + ':59');
  }
  return allowTimes;
}
// 条件初始化方法
function conditionSettings() {
  //
  function monthChange(ct) {
    var nowData = new Date();
    var month = $('.month_set').val();

    if ((ct.getMonth()+1) === parseInt(month) && ct.getFullYear() === nowData.getFullYear()) {
      this.setOptions({
        timepicker: true
      });
    } else {
      this.setOptions({
        timepicker: false
      });
    }
  }
  // 开始时间配置
  $("#startTime").datetimepicker({
    formatDate: 'Y-m-d h:s',
    minDate: '2016-03-25 00:00',
    todayButton: false,
    allowTimes: setAllowTimes(),
    maxTime: '',
    // 设置默认时间
    onShow: function() {
      var month = $('.month_set').val();
      var year = $('.year_set').val() || 2017;
      var dateRes = getThisMonthDate(year, month);
      // console.log(dateRes);
      this.setOptions({
        minDate: dateRes.startDate || false,
        maxDate: dateRes.lastDate || false,
        startDate: dateRes.startDate || false,
        timepicker: true
      });
    },
    onChangeMonth: function(ct) {
      monthChange.call(this, ct);
    },
    onSelectTime: function(ct) {
      var endTime = new Date($('#endTime').val());
      if (ct.getTime() > endTime.getTime()) {
        alert('开始时间不能大于结束时间');
      }
    }
  });
  // 结束时间
  $("#endTime").datetimepicker({
    formatDate: 'Y-m-d h:s',
    minDate: '2016-03-25 00:00',
    todayButton: false,
    allowTimes: setAllowTimes(),
    onShow: function() {
      var month = $('.month_set').val();
      var year = $('.year_set').val() || 2017;
      var dateRes = getThisMonthDate(year, month);
      // console.log(dateRes);
      // 设置配置
      this.setOptions({
        minDate: dateRes.startDate || false,
        maxDate: dateRes.lastDate || false,
        startDate: '2016-03-25' || false,
        timepicker: true
      });
    },
    onChangeMonth: function(ct) {
      monthChange.call(this, ct);
    },
    onSelectTime: function(ct) {
      var startTime = new Date($('#startTime').val());
      if (ct.getTime() < startTime.getTime()) {
        alert('结束时间不能小于开始时间');
      }
    }
  });
  // 切换月份，隐藏选择 时间
  $('.finance_s .t_condition .sel_tab[data-type="stat"] label').click(function(e) {
    e = e || window.event;
    var val;
    // 判断 点击的 元素
    if (e.target.nodeName === 'LABEL') {
      val = $(e.target).find('input').val();
    } else if (e.target.nodeName === 'INPUT') {
      val = $(e.target).val();
    }
    // $("div[data-stat]").hide();
    // 操作 选择日期显示
    if (val === 'month') {
      $("[data-stat='day']").hide();
    } else {
      $("[data-stat='day']").show();
    }
  });
  // 选择月份 重置选择时间 当月
  $('#selMonth').change(function(e) {
    // 选择月份
    var selMonth = $(this).val();
    var month = selMonth < 10 ? '0' + selMonth : selMonth;
    // 当前选择年份份
    var year  = $("#selYear").val() || (new Date()).getFullYear();
    // 没选择年份
    if (!$("#selYear").val()) {
      // 选择月份是否大于当前月份
      if (month > (new Date()).getMonth() + 1) {
        alert('不能大于当前月份')
        return;
      }
    }
    // 更改开始时间为选择月份第一天
    $("#startTime").val(year + '-' + month + '-01 00:00');
    // 当月最后一天
    var nextMonthStartDay = (new Date(year + '-' + (parseInt(month) + 1) + '-01 00:00'));
    // 判断最后一天是否大于今天
    if (nextMonthStartDay > (new Date()).getTime()) {
      // $("#endTime").val(laydate.now());
    } else {
      var lastMonthDay = (new Date(nextMonthStartDay.getTime() - 10)).getDate();
      $("#endTime").val(year + '-' + month + '-' + lastMonthDay + ' 00:00');
    }
  });
  // 选择币种
  $('.coin_list_chk label[data-chk="all"] input').change(function(e) {
    if ($(this).is(':checked')) {
      $('.finance_s .coin_list_chk_sel input[type="checkbox"]').prop('checked', "checked");
    } else {
      $('.finance_s .coin_list_chk_sel input[type="checkbox"]').prop('checked', false);
    }
  });
  // 获取 统计选项 日 / 月 方法  返回选择的筛选类型的 value 值 不选择，返回undefined
  function getStatType() {
    return $('.finance_s .t_condition .sel_tab input[name="filterTime"]:checked').val();
  }
  // 选择器 值
  function getSelVal(sel) {
    return $(sel).val();
  }
  /*// 点击筛选按钮
  $("#selBtn").click(function() {
    // 获取所有选择的币的code
    var coinsCode = [];
    [].slice.call($('.finance_s .coin_list_chk_sel input:checked')).forEach(function(elem) {
      console.log(elem);
      coinsCode.push($(elem).val());
      console.log(coinsCode);
    });
    // $(".coin_list_chk_sel input[checked='checked']");
    // 提交 json
    var postJson = {
      getStatType: getStatType(), // 获取 统计选项 日 / 月
      startTime: getSelVal("#startTime"), // 开始时间
      endTime: getSelVal("#endTime"), // 结束时间
      selMonth: getSelVal(".month_set"), // 获取选取的月份
      selYear: getSelVal(".year_set"), // 获取 选取 年份
      coins: coinsCode
    }
    console.log(postJson);
  });*/

}
