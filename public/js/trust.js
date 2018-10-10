var priceval = '此出价为一个币的价格',buy_max = 0, btcradio = 1, numberChange = 0;

"use strict";

function Trade() {
	this.name = 'Trade';
}

//总计
Trade.prototype.sumPrice = function(type,number,price,price_float) {
	$('#'+type+'sumprice').val(formatfloat(accMul(number,price), price_float, 0));
}

Trade.prototype.buyPrice = function(price_float, number_float) {
	var priceinput = $('#bprice').val();
	var price = formatfloat(priceinput, price_float, 0);
	if( isNaN(price) ){
		$('#bprice').val('');return false;
	}
	if(badFloat(priceinput, price_float)) $('#bprice').val(price);
	if(price > 0){
		btc_max = user.cny_over / price;
        btc_max = btc_max - Math.pow(10,-number_float);

		if(!numberChange || numberChange>formatfloat(btc_max * btcradio, number_float, 0)){
			$('#bnumber').val(formatfloat(btc_max * btcradio, number_float, 0));
		}
		//sumprice();
		this.sumPrice('b',$('#bnumber').val(),price,price_float);
	}
};

Trade.prototype.sellPrice = function(price_float) {
	var priceinput = $('#sprice').val();
	var price = formatfloat(priceinput, price_float, 0);
	if( isNaN(price) ){
		$('#sprice').val('');return false;
	}
	if(badFloat(priceinput, price_float)) $('#sprice').val(price);
	//if(price > 0) sumprice();
	this.sumPrice('s',$('#snumber').val(),price,price_float);
};

Trade.prototype.buyNumber =  function(number_float,price_float) {
	var numberinput = $('#bnumber').val();
	if(badFloat(numberinput, number_float)) {
		$('#bnumber').val(formatfloat(numberinput, number_float, 0));
	}
	var number = parseFloat($('#bnumber').val());
	var price  = parseFloat($('#bprice').val());
	var btcmax = formatfloat( formatfloat(user.cny_over/price, number_float, 0) - Math.pow(10,-number_float), number_float, 0);
	if(number > btcmax){
		number = btcmax;
		$('#bnumber').val(number);
	}
	this.sumPrice('b',number,price,price_float);
	numberChange = $('#bnumber').val();
};

Trade.prototype.sellNumber = function(number_float,price_float) {
	var numberinput = $('#snumber').val();
	if(badFloat(numberinput, number_float)) {
		$('#snumber').val(formatfloat(numberinput, number_float, 0));
	}
	var number = parseFloat($('#snumber').val());
	var btcmax = parseFloat($('#sale_max').html());
	var price  = $('#sprice').val();
	if(number > btcmax) $('#snumber').val(btcmax);
	this.sumPrice('s',number,price,price_float);
	numberChange = $('#snumber').val();
};


Trade.prototype.submit = function(type, coin, number_float) {
    TBType = (type=='b')?'in':'out';
	var data = {type:TBType, price:parseFloat($('#'+type+'price').val()), number: parseFloat($('#'+type+'number').val()), pwdtrade: encodeURIComponent($('#'+type+'pwdtrade').val()), coin_from:coin};
	$.post("/ajax/trustcoin/", data ,function(d){
		$('#'+type+'ErrorTips').html(d.msg).show();
		if (d.status == 1) {setTimeout(function() {
			$('#'+type+'ErrorTips').hide();
		}, 2000);}
		if(d.status){
			for(var i in d.data) user[i] = d.data[i];

			$('#bpwdtrade').parent().hide();
			$('#spwdtrade').parent().hide();

			freshAsset(user, coin, number_float);
		}
	}, 'json');
}

// 最佳买价和最佳卖价
Trade.prototype.curPrice = function(type,coin_from,number_float,price_float){
	var price = parseFloat($('#'+type+'pricenice').html());
	$('#'+type+'price').val(price);

	if(type=='b'){
		if(price > 0){
			var num_max = user.cny_over / price - Math.pow(10,-number_float);
			$('#buy_max').html(formatfloat(num_max, number_float, 0));
			$('#bnumber').val(formatfloat(num_max, number_float, 0));

			this.sumPrice('b',$('#bnumber').val(),price,price_float);
		}
	}else{
		var number = parseFloat($('#'+type+'number').val());
		if(number){
			this.sumPrice(type, number, price, price_float);
		}
	}
}

// 下单比例
Trade.prototype.numberRate = function(type, number_float, price_float, rate, index){
	$('.'+type+'rate').find('a').eq(index).addClass('active').siblings().removeClass('active');

	var price = parseFloat($('#'+type+'price').val());
	if(type=='b'){
		var buy_max = parseFloat($('#buy_max').html());
		if( buy_max ){
			$('#'+type+'number').val( formatfloat(parseFloat(buy_max/rate), number_float, 0) );
			if( price ){
				this.sumPrice(type, formatfloat(parseFloat(buy_max/rate), number_float, 0), price, price_float);
			}
		}
	}else if(type == 's'){
		var sell_max = parseFloat($('#sell_max').html());
		$('#'+type+'number').val( formatfloat(parseFloat(sell_max/rate), number_float, 0) );
		if( price ){
			this.sumPrice(type, formatfloat(parseFloat(sell_max/rate), number_float, 0), price, price_float);
		}
	}
}

var Trade = new Trade();
