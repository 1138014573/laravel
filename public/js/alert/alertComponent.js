function loadJs(id, url, callback) {
  var script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = url;
  script.id = id;
  script.onload = script.onreadystatechange = function() {
    alert(script.readyState);
    if (script.readyState && script.readyState != 'loaded' && script.readyState != 'complete')
      return;
    script.onreadystatechange = script.onload = null
    if (callback)
      callback();
    }
  document.body.appendChild(script);
}

// tips:
var TIPS = function(settings) {
  this.id = settings
    ? settings.id
    : 'tips_win';
  this.content = '提示内容';
  //
  this.elm = '';
  //
  this.init(settings || "");
}

// init
TIPS.prototype.init = function(settings) {
  if (settings) {
    console.log(settings);
  }
  // template
  var container = document.createElement('div');
  container.id = this.id;
  container.innerHTML = this.template;
  document.body.appendChild(container);
  // 缓存 alert elementS
  this.elm = document.getElementById(this.id);
  // 禁止浏览器滚动
  this.stopBodyScroll();
  //
  this.sureBtn = this.elm.getElementsByClassName('btn')[0];
  //
  this.sureBtn.onclick = function() {
    this.closed();
  }.bind(this)
  //
  this.closeCur = this.elm.getElementsByClassName('close_cur')[0];
  this.closeCur.onclick = function() {
    this.closed();
  }.bind(this);
  // css
  // var link = document.createElement('link');
  // link.type = 'text/css';
  // link.rel = 'stylesheet';
  // link.href = '../js/alert/alert.css';
  // document.getElementsByTagName('HEAD')[0].appendChild(link);
};

// template
TIPS.prototype.template = '<div class="mid_win">\
                            <div class="tips_box">\
                              <span class="close_cur">x</span>\
                              <div class="tips_header">header</div>\
                              <div class="tips_msg">msg</div>\
                              <div class="tips_footer"><span class="btn">确定</span></div>\
                            </div>\
                          </div>';
// 更改header
TIPS.prototype.setHeader = function(headerContent) {
  var header = this.elm.getElementsByClassName('tips_header')[0];
  header.innerHTML = headerContent;
}
// 更改显示内容
TIPS.prototype.setContent = function(chgMsg) {
  var content = this.elm.getElementsByClassName('tips_msg')[0];
  content.innerHTML = chgMsg;
}
// show
TIPS.prototype.show = function(msg) {
  this.setContent(msg);
  this.elm.style.display = 'block';
}
// hide
TIPS.prototype.closed = function() {
  this.elm.style.display = 'none';
}
// 禁止浏览器滚动事件 指定规定元素内部可以执行滚动事件
TIPS.prototype.stopBodyScroll = function() {
  this.elm.onmousewheel = function scrollWheel(e) {
    e = e || window.event;
    if (navigator.userAgent.toLowerCase().indexOf('msie') >= 0) {
      event.returnValue = false;
    } else {
      e.preventDefault();
    };
  };
  if (navigator.userAgent.toLowerCase().indexOf('firefox') >= 0) {
    //firefox支持onmousewheel
    addEventListener('DOMMouseScroll',
      function(e) {
        var obj = e.target;
        var onmousewheel;
        while (obj) {
          onmousewheel = obj.getAttribute('onmousewheel') || obj.onmousewheel;
          if (onmousewheel) break;
          if (obj.tagName == 'BODY') break;
          obj = obj.parentNode;
        };
        if (onmousewheel) {
          if (e.preventDefault) e.preventDefault();
          e.returnValue = false; //禁止页面滚动
          if (typeof obj.onmousewheel != 'function') {
            //将onmousewheel转换成function
            eval('window._tmpFun = function(event){' + onmousewheel + '}');
            obj.onmousewheel = window._tmpFun;
            window._tmpFun = null;
          };
          // 不直接执行是因为若onmousewheel(e)运行时间较长的话，会导致锁定滚动失效，使用setTimeout可避免
          setTimeout(function() {
            obj.onmousewheel(e);
          }, );
        };
      },
      false);
  };
}
