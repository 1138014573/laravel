 @include('home.common.header')
<link rel="stylesheet" href="../css/swiper.css">
<style>
	.tn_newscon .left ul li:first-child a{
		color:red;
	}
</style>
<div class="tn_home">
	<div class="swiper-container">
		<div class="swiper-wrapper">
			<!--<?php  if(isset($isMobile)){ ?>
				<div class="swiper-slide slideA3"><a href="http://m.bijiaosuo.com/"></a></div>
			<?php  }else{ ?>
				<div class="swiper-slide slideA3"><a href="/trade"></a></div>
			<?php  } ?>-->
			<div class="swiper-slide slideA7"><a href="https://www.bijiaosuo.com/index/newsid?nid=282&type=1"></a></div>
                        <div class="swiper-slide slideA1"><a href="https://www.bijiaosuo.com/"></a></div>
			<div class="swiper-slide slideA2"><a href="https://www.bijiaosuo.com/"></a></div> 
			<div class="swiper-slide slideA3"><a href="https://www.bijiaosuo.com/index/newsid?nid=280&type=1"></a></div>
                         <div class="swiper-slide slideA6"><a href="https://www.bijiaosuo.com/"></a></div>
		<!--	<div class="swiper-slide slideA4"><a href="/trade"></a></div>
			<div class="swiper-slide slideA5"><a href="/trade"></a></div>-->
		</div>
		<a class="arrow-left" href="#"></a>
		<a class="arrow-right" href="#"></a>
	</div>
</div>
<div class="hmodifyNav">
	<div class="price_today" style="top: 0px;">
		<div class="autobox">
			<ul class="price_today_ull clearfix">
				<li style="cursor:default;">币种 </li>
				<li class="click-sort" >最新成交价<i class="cagret cagret-down"></i><i class="cagret cagret-up"></i></li>
				<li class="click-sort" >24H成交量<i class="cagret cagret-down"></i><i class="cagret cagret-up"></i></li>
				<li class="click-sort" >24H成交额(CNY)<i class="cagret cagret-down"></i><i class="cagret cagret-up"></i></li>
				<li class="click-sort" >总市值<i class="cagret cagret-down"></i><i class="cagret cagret-up"></i></li>
				<li class="click-sort" >日涨跌<i class="cagret cagret-down"></i><i class="cagret cagret-up"></i></li>
				<li class="click-sort" >周涨跌<i class="cagret cagret-down"></i><i class="cagret cagret-up"></i></li>
			</ul>
		</div>
	</div>
	<ul class="price_today_ul" id="price_today_ul"> 
		<script>var pairs = [];</script>
		<?php  foreach ($pairs as $k => $v1) { ?>

		<li class="list_coin<?php  echo $k%2;?>">
			<script>pairs.push("<?php  echo $v1->name;?>");</script>
			<a href="/trade/<?php  echo $v1->coin_from;?>_cny" rel="<?php  echo $v1->coin_from;?>_cny">
				<dl class="autobox clearfix">
					<dt>
						<i class="deal_list_pic"><img src="/img/coin_<?php  echo $v1->coin_from;?>.png" ></i><p><?php  echo $v1->display;?>(<?php  echo strtoupper($v1->coin_from);?>)</p>
					</dt>
					<dd class="orange">11</dd>
					<dd>22</dd>
					<dd>33</dd>
					<dd>44</dd>
					<dd class="red">55</dd>
					<dd class="red">66</dd>
					<dd class="gotrade"><input type="button" value="去交易"></dd>
				</dl>
			</a>
		</li>
		<?php  } ?>
	</ul>
</div>
<div class="tn_introduce">
	<div class="bjs_pt">
		<div class="bjs_ptcon">
			<div class="imginner">
				<div class="lt">
					<div class="imgmain ymainani">
						<img src="/img/yuanimg.png">
					</div>
					<div class="imgone imgsm yoani">
						<img src="/img/yuanimg_01.png">
					</div>
					<div class="imgtwo imgsm ytani">
						<img src="/img/yuanimg_02.png">
					</div>
					<div class="imgthree imgsm ytani">
						<img src="/img/yuanimg_03.png">
					</div>
					<div class="imgfourth imgsm ythani">
						<img src="/img/yuanimg_04.png">
					</div>
					<div class="imgfive imgsm yfiani">
						<img src="/img/yuanimg_05.png">
					</div>
					<div class="imgsix imgsm ysani">
						<img src="/img/yuanimg_06.png">
					</div>
				</div>
				<div class="rt">
					<img src="/img/rtimg.png">
				</div>
			</div>
		</div>
	</div>
	<div class="bjs_profe">
		<a href="http://www.cnboshang.com" target="__blank" >
			<div class="bjs_profecon">
				<h3>合作律师顾问</h3>
				<div class="lowerwen">
					<img src="/img/gwbslssws.png" alt="广东博商律师事务所">
					<span>广&nbsp;东&nbsp;博&nbsp;商&nbsp;律&nbsp;师&nbsp;事&nbsp;务&nbsp;所</span>
				</div>

				<p> <span>博&nbsp;&nbsp;开&nbsp;&nbsp;境&nbsp;&nbsp;界 </span> <span>商&nbsp;&nbsp;行&nbsp;&nbsp;大&nbsp;&nbsp;道</span></p>
			</div>
		</a>
	</div>
	<div class="bjs_icon">
		<div class="bjs_iconcon clearfix">
			<div class="bjs_coninfo left">
				<img src="/img/ha_safety.png" alt="安全">
				<h6>安全</h6>
				<p>钱包多层加密，离线存储于银行保险柜<br/>资金第三方托管，确保安全</p>
			</div>
			<div class="bjs_coninfo left">
				<img src="/img/ha_professional.png" alt="专业">
				<h6>专业</h6>
				<p>专业的客服团队，400电话和24小时在线VIP一对一专业服务</p>
			</div>
			<div class="bjs_coninfo left">
				<img src="/img/ha_world.png" alt="全球">
				<h6>全球互通</h6>
				<p>为您提供国际平台<br/>更多的选择，更便捷的交易</p>
			</div>
		</div>
	</div>
	<div class="bjs_turnover">
		<div class="bjs_tnorcon">
			<h2>累计交易额</h2>
			<span><?php  echo $total_24?></span>
			<p>我们的团队成员有着丰富的互联网运营经验</p>
			<p>主要来自大型互联网公司与金融机构各方面的精英人才</p>
			<p>在国内外大型平台积累了丰富的工作经验</p>
			<p>为平台的稳定和长远发展提供了坚实的保障，致力让客户感受到专业、安全、稳定的交易体验。</p>
			<a href="/trade">交易平台</a>
		</div>
	</div>
	<div class="tn_newscon">
		<div class="tn_introduce_con">
			<div class="tn_news clearfix" style="margin-top:0;">
				<div class="tn_notice left">
					<div class="tn_news_title clearfix">
						<h3 class="left">系统公告</h3>
						<a href="/html/gglog.html?type=1" class="right">更多</a>
					</div>
					<ul>
						<?php  foreach ($data as $v) {?>
						<li><a href="/index/newsid?nid=<?php  echo $v->id?>&type=1" class="left"><?php  echo $v->title?></a><span class="right"><?php  echo date('Y-m-d', $v->created)?></span></li>
						<?php  } ?>
					</ul>
				</div>
				<div class="tn_notice right">
					<div class="tn_news_title clearfix">
						<h3 class="left">行业新闻</h3>
						<a href="/html/gglog.html?type=2" class="right">更多</a>
					</div>
					<ul>
						<?php  foreach ($newsdata as $v) {?>
						<li><a href="/index/newsid?nid=<?php  echo $v->id?>&type=2" class="left"><?php  echo $v->title?></a><span class="right"><?php  echo date('Y-m-d', $v->created)?></span></li>
						<?php  } ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="tn_introduce_link">
		<div class="tn_link clearfix">
			<div class="tn_link_title clearfix">
				<h3>友情链接</h3>
			</div>
			<div class="link_logo">
				<ul>
					<li><a href="http://www.btctrade.com/" target="_blank"><img src="/img/link_logo001.jpg"></a></li>
					<li><a href="http://ethfans.org/" target="_blank"><img src="/img/link_logo002.jpg"></a></li>
					<li><a href="https://www.okcoin.cn/" target="_blank"><img src="/img/link_logo003.jpg"></a></li>
					<li><a href="https://www.chbtc.com/" target="_blank"><img src="/img/link_logo004.jpg"></a></li>
					<li><a href="https://www.huobi.com/" target="_blank"><img src="/img/link_logo005.jpg"></a></li>
					<li><a href="http://www.bitecoin.com/" target="_blank"><img src="/img/link_logo007.jpg"></a></li>
			</div>
		</div>
	</div>
	<div class="tn_risk_warning">
		<h3></h3>
		<h2 class="clearfix">
			风险提示：<span>数字货币交易具有极高的风险，请控制风险不要投入超过您风险承受能力的资金，警惕虚假宣传，拒绝传销组织！！</span>
		</h2>
	</div>
</div>
<!-- 侧导航 -->
<!-- <div class="kefuDiv">
	<div id="rightArrow"> <em><img src="/img/htel_icon.png" alt=""></em><a href="javascript:;" title="在线客户">在线客服</a></div>
	<div id="floatDivBoxs"> 
		<div class="floatDtt">在线客服</div>
		<div class="floatShadow">
			<ul class="floatDqq">
				<li><a target="_blank" href="tencent://message/?uin=363161547&Site=sc.chinaz.com&Menu=yes"><img src="/img/online_qq.png" align="absmiddle">在线客服1号</a></li>
				<li><a target="_blank" href="tencent://message/?uin=994984584&Site=sc.chinaz.com&Menu=yes"><img src="/img/online_qq.png" align="absmiddle">在线客服2号</a></li>
				<li><a target="_blank" href="tencent://message/?uin=1662906650&Site=sc.chinaz.com&Menu=yes"><img src="/img/online_qq.png" align="absmiddle">在线客服3号</a></li>
			</ul>
			<div class="floatDtt">交流群</div>
			<ul class="floatDqq">
				<li><a><img src="/img/online_qun.png" align="absmiddle">468312013</a></li>
				<li><a><img src="/img/online_qun.png" align="absmiddle">551833661</a></li>
			</ul>
			<div class="floatDtt">热线电话</div>
			<ul class="floatDqq hottel">
				<li><a><img src="/img/online_telphone.png" align="absmiddle">400-051-8422</a></li>
			</ul>
		</div>
	</div>
</div> -->
<!--风险提示 -->
    <div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="text-align: center; display: block; padding-right: 17px;">
       <div class="modal-dialog" style="width:640px;text-align:center;">
           <div id="autoCenter" class="modal-content" style="padding: 30px 30px 10px; height: 264px; margin-top: 310px;">
               <div class="modal-header" style="border:none;text-align:left;">
                   <h3 class="modal-title" id="myModalLabel" style="font-size:18px;">
   					币交所提醒您:
   				</h3>
               </div>
               <div class="modal-body" style="font-size:18px;width: 550px;margin-top:-10px;">
                   <div class="paragraph paragraph_news" style="font-size:18px;text-indent:2em;line-height: 30px;text-align:left;">
                       不投入超过风险承受能力的资金，不投资不了解的数字资产，不听信任何以币交所名义推荐买币投资的宣传，坚决抵制传销、电信诈骗和洗钱套汇等违法行为。
                   </div>
               </div>
               <div class="modal-footer" style="border:none;">
                   <button type="button" style="cursor:pointer;font-family:Microsoft YaHei !important;font-size:18px;background: #faad09;width:200px;height:40px;border:0px;color:white" class="btn btn-warning" data-dismiss="modal" id="yes_sure">我已了解以上风险
                   </button>
               </div>
           </div>
       </div>
   </div>
    <!--风险提示 -->
<script>/*关闭首页提示*/
	for (var i = 0; i < window.sessionStorage.length; i++) {
        var key = window.sessionStorage.key(i);
        var value = window.sessionStorage.getItem(key); 
        if (value == 'indexYes') {
            $("#myModal").hide();
        }
    }
	$("#yes_sure").click(function(){
		$("#myModal").hide();
		window.sessionStorage.setItem('indexRisk', 'indexYes');
	});
</script>
 @include('home.common.footer')
<script src="../js/swiper.min.js"></script>
<script>
	// 更新币种信息
	/*for (var name in pairs) {
		getCoinInfo(pairs[name]);
	}*/
	var json = {};
	for(var i=0; i<pairs.length; i++){
	    json[i] = pairs[i];
	}
	var pairs = JSON.stringify(json);
	getCoinInfo(pairs);


 	//轮播;
 	var mySwiper = new Swiper('.swiper-container',{
 		autoplay:2000,
 		loop:true,
 		speed:2000,
 	})
 	$('.arrow-left').on('click', function(e){
 		e.preventDefault()
 		mySwiper.swipePrev()
 	})
 	$('.arrow-right').on('click', function(e){
 		e.preventDefault()
 		mySwiper.swipeNext()
 	})

 	// 右侧联系客服导航
	$(function(){
		$("#rightArrow").css({'opacity':'0.8'});
		$('#floatDivBoxs').css({'opacity':'0.8'});

		$('.kefuDiv').mouseenter(function(){
			$("#floatDivBoxs").animate({right: '0'},300);
			$('#rightArrow').animate({right: '170px'},300);
			$('#rightArrow').css('background-position','0px 0');
		}).mouseleave(function(){
			$('#floatDivBoxs').animate({right: '-175px'},300);
			$('#rightArrow').animate({right: '-5px'},300);
			$('#rightArrow').css('background-position','-50px 0');
		});
	});
</script>

