	$(function(){
		changeWindowFun();
	});
	$(window).resize(function() {
		changeWindowFun();
	});

	$('.maskWrap').css('opacity', '0.7');

	// 窗口高度变化时方法
	function changeWindowFun(){
		//中间主区域的高度；
		var sHeight = $(window).height() - $(".header").height() - $(".scFooter:visible").height()-10;
		var listInHeight = sHeight-$('.depthHead').height()-21;
		$(".maincontent").css("height",sHeight);
		$('.depth').css('height',sHeight);
		$('.rtTrade').css('height',sHeight);
		$('.priceNumTim').css('height',sHeight);
		$('.listInfoMe').css('height',listInHeight);
		$('.ltKline iframe').css('width','100%');

		//深度的depthMiddleOut的高度；
		// var dHeight = $('.depth').height() - $('.depthHead').height()-$('.depthfoot').height();
		var dHeight = $('.depth').height() - $('.depthHead').height()-25;
		$('.depthMiddleOut').css('height',dHeight);

		//卖出depthsell和买入depthbuy的高度；
		// var sellBuyHeight = (dHeight - $('.depthNewPrice').height()-45)/2;
		// $(".depthsell").css({'height':sellBuyHeight,'overflow':'hidden'});
		// $(".depthbuy").css({'height':sellBuyHeight,'overflow':'hidden'});
	}

	function toggleBtm(){
		$('.scFooter').toggle();
		$('.toggleBtm').toggleClass('active');
		changeWindowFun();
	}

	function toggleRight(){
		$('.rtTrade').toggle();
		$('.ltKline').toggleClass('active');
		$('.toggleRt').toggleClass('active');
		// $('#klineFrame').attr('src', '/market?symbol=goc_cny');
	}

	//当前委托与历史委托的切换；
	$('.ulList ul li').click(function(){
		if (!$(this).hasClass('this')) {
			$(this).addClass('this').siblings().removeClass('this');
			$('.tabelBox .weituoInfo').eq($(this).index()).show().siblings().hide();
		}
	});

	//买入卖出的mouseover事件；
	//鼠标移入
	$('.depthsell').delegate('.depthList', 'mouseover', function(){
		$(this).addClass('active').siblings().removeClass('active');
	}).delegate('.depthList', 'mouseout', function(){
		$(this).removeClass('active');
	})
	$('.depthbuy').delegate('.depthList', 'mouseover', function(){
		$(this).addClass('active').siblings().removeClass('active');
	}).delegate('.depthList', 'mouseout', function(){
		$(this).removeClass('active');
	})

	//点击价格数量改变.inputCon的value；
	/*$('.listInfoMe').delegate('.depthList', 'click', function(){
		var clickPrice = $(this).find('.delist3').html().replace('<b>','').replace('</b>','');
		$('#buy_price').val(clickPrice);
		$('#sell_price').val(clickPrice);
	})*/
	$('.depthMiddleOut').delegate('.depthList', 'click', function(){
		var huoquPrice = $(this).find('.delist2').html().replace('<b>','').replace('</b>','');
		var huoquNum = $(this).find('.delist3').html();
		$('#buy_price').val(huoquPrice);
		$('#sell_price').val(huoquPrice);
		$('#buy_number').val(huoquNum);
		$('#sell_number').val(huoquNum);
	})

	//买入卖出输入框的mouseover、focus事件
	$('.ftRight').delegate('.inputCon', 'mouseover focus', function(){
		$(this).css('background','#052348');
		$(this).find('input').css('background','#052348');
	}).delegate('.inputCon', 'mouseout blur', function(){
		$(this).css('background','#212020');
		$(this).find('input').css('background','#212020');
	});

	// 交易密码取消
	$('#pwdcancel').click(function(){
		$('.maskWrap').hide();
		$('.maskTradePwd').hide();
	});


	$('.maskTradePwd a').click(function(){
		$('.maskWrap').hide();
		$('.maskTradePwd').hide();
	});
	// 立即买入按钮
	/*var el;
	Zepto(".buyBtn").on('tap', function(){
		$(".buyBtn").find('input').addClass('buyBtnActive');

		var data = {type:'in', price:parseFloat($('#buy_price').val), number:parseFloat($('#buy_number').val())}
		TBType = (type=='b')?'in':'out';
	var data = {type:TBType, price:parseFloat($('#'+type+'price').val()), number: parseFloat($('#'+type+'number').val()), pwdtrade: encodeURIComponent($('#'+type+'pwdtrade').val()), coin_from:coin};
	$.post("/ajax/trustcoin/", data ,function(d){
		$('#'+type+'ErrorTips').html(d.msg).show();
		if (d.status == 1) {setTimeout(function() {
			$('#'+type+'ErrorTips').hide();
		}, 2000);}
		if(d.status) for(var i in d.data) user[i] = d.data[i];
	}, 'json');
		el=Zepto.tips({
			content:'请先登录后再进行交易',
			stayTime:2000,
			type:"success"
		})
		setTimeout(function(){
			$('.buyBtn').find('input').removeClass('buyBtnActive');
		},1000)
	});
	// 立即卖出按钮
	Zepto(".sellBtn").on('tap', function(){
		$(".sellBtn").find('input').addClass('buyBtnActive');
		el=Zepto.tips({
			content:'请先登录后再进行交易',
			stayTime:2000,
			type:"success"
		})
		setTimeout(function(){
			$('.sellBtn').find('input').removeClass('buyBtnActive');
		},1000)
	});*/
