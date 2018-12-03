<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<?php  isset($seot) || $seot = isset($this->layout['seot']) ? $this->layout['seot'] : '区块链权益资产交易平台'; ?>
	<title><?php  echo $seot?></title>
	<meta name="keywords" content="虚拟货币,虚拟货币交易平台,多币种,数字货币,比特币,莱特币" />
	<meta name="description" content="多币种数字货币交易平台,目前提供富途币、比特币、莱特币等实时在线交易" />
	<meta content="telephone=no,email=no" name="format-detection">
	<link rel="stylesheet" href="<?php  //echo host()?>/js/alert/alert.css"/>
	<link href="<?php  //echo host()?>/css/newstyle.css?v=1" rel="stylesheet"/>
	<link href="<?php  //echo host()?>/css/boot.css" rel="stylesheet"/>
	<script src="<?php  //echo host()?>/js/alert/alertComponent.js"></script>
	<script src="<?php  //echo host()?>/js/jquery/1.9.1.min.js"></script>
	<!-- <script src="<?php  //echo host()?>/js/index.js?v=45"></script> -->
	<script src="<?php  //echo host()?>/js/index.js?v=49"></script>
	<script src="<?php // echo host()?>/js/tab.js"></script>
	<style>
		.qq_qq {
			background-image: url(/images/qqkefu.png);
			background-position: 0px -24px;
			width: 81px;
			height: 24px;
			display: inline-block;
			margin-top: 8px;
			float: left;
		}
		.qq_qq:hover {
			background-image: url(/images/qqkefu.png);
			background-position: 0px 0px;
			width: 81px;
			height: 24px;
			display: inline-block;
			margin-top: 8px;
			float: left;
		}
		.header_asset div{float: left;margin-right: 20px;}
		.asset_dis{display: inline-block;height: 20px;padding: 0px 10px;line-height: 20px;background: #faad09;text-align: center;border-radius: 3px;color: #fff;}
		.asset_coin{margin-left: 10px;color:#676767;}
		.header_notice { float: left; margin-left: 30px; box-sizing: border-box; line-height: 40px; }
		.header_notice a {color: #ea2626; font-size: 12px;}
		.header_notice::before { content: ""; position: relative; top: 3px;display: inline-block; margin-right: 10px;width: 15px; height: 15px; background: url(/images/idx_notice.png) center center no-repeat; }
	</style>
	<!--[if IE 6]><script src="js/DD_belatedPNG_0.0.8a.js"></script><![endif]-->
	<script type="text/javascript">
		<?php  # 用户信息(模板初始化)
		isset($user) || $user = empty($this->layout['user'])? array(): $this->layout['user'];
		if($user){?>
		user = {uid:<?php  echo $user['uid']?>, email:'<?php  echo $user['email']?>', name:'<?php  echo $user['name']?>',phone:'<?php  echo $user['mo']?>'};
		<?php foreach($user as $k=>$v){
		if(strpos($k, '_over') !== false || strpos($k, '_lock') !== false){?>
		user['<?php  echo $k?>'] = '<?php  echo $v?>';
		<?php 	}
		}
		}else{ ?>
		user = {};
		<?php  } ?>
	</script>
</head>
<body>
<div class="topheaderWrap">
	<div class="topheader">
		<!--左边成交信息-->
		<div class="topheaderleft">
			<a href="javascript:void(0)" onclick="javascript:window.open('http://wpa.b.qq.com/cgi/wpa.php?ln=1&key=XzkzODAxNzM4OF80NjQxOTFfNDAwMDYwNjEwNl8yXw');" class="qq_qq"></a>
			<div class="kftel">客服电话：400-060-6106</div>
			<div class="header_notice"><a href="/index/newsid?nid=282&type=1">关于币交所反洗钱政策的公告</a></div>

		</div>
		<!--右边语言网站地图-->
		<div class="topheaderright" <?php if($user){?>style="width:auto;"<?php }?>>
			<?php  if($user){?>
				<div class="topinfo">
					<div class="infoEmail">
						<span>您好，<?php  if($user['name']){echo $user['name'];}elseif ($user['mo']){echo substr($user['mo'], 0, 3).'*****'.substr($user['mo'],7, 10);}else{echo substr($user['email'], 0, 3).'****'.substr($user['email'], strpos($user['email'], '@'));} ?></span>
					</div>
					<div class="infoHideDiv">
						<h3><?php  if($user['mo']){echo $user['mo'];}else{echo substr($user['email'], 0, 3).'****';} ?></h3>
						<div>
							<span>UID：<?php  echo $user['uid']?></span>
							<a href="/user_index/userinfo">设置</a>
						</div>
					</div>
				</div>
			<?php  } ?>
			<div class="topwapmap">
				<span class="hspannav">网站导航</span>
				<div class="wapmaplist">
					<div class="mapchangyong">
						<h2>常用</h2>
						<ul>
							<li><a href="/guide.html">新手引导</a></li>
							<li><a href="/faq.html">常见问题</a></li>
							<li><a href="/contactus.html">联系我们</a></li>
						</ul>
					</div>
					<div class="maptrade">
						<h2>交易中心</h2>
						<ul>
							<li><a href="/trade/btc_cny">比特币</a></li>
							<li><a href="/trade/ltc_cny">莱特币</a></li>
							<li><a href="/trade">富途币</a></li>
						</ul>
					</div>
					<div class="mapusercen">
						<h2>用户中心</h2>
						<ul>
							<li><a href="/user_index/twofactor">安全设置</a></li>
							<li><a href="/user_order/list">交易记录</a></li>
							<li><a href="/upcoin.html">上新币</a></li>
						</ul>
					</div>
				</div>
			</div>

			<div class="toplanguage">
				<img src="/img/hcountry.png" alt="中文" class="hcurimg" />
				<div class="hselectlang">
					<ul>
						<li>
							<a href="javascript:;">
								<img src="/img/hcountry.png" alt="中文" />
								<span>中文</span>
							</a>
						</li>
						<!-- <li>
                            <a href="javascript:;">
                                <img src="/img/usa.png" alt="英文" />
                                <span>English</span>
                            </a>
                        </li> -->
					</ul>
				</div>
			</div>
			<?php  if($user){ ?><a class="infoLogout" href="/user/logout">退出</a><?php  } ?>
		</div>
	</div>
</div>
<div class="header_area">
	<!-- <div class="header" style="width: <?php  //echo REDIRECT_URL == '/trade' ? 1200 : 1000?>px;"> -->
	<div class="header">
		<div class="indexlogo" style="float:left;padding: 5px 0">
			<a href="/" style="display: block;margin: 14px 10px 0 0;">
				<img src="/img/fullLogo.png" alt="币交所" />
			</a>
		</div>
		<div class="navBox left">
			<ul class="nav">
				<?php
				$top_menu = array(
					'/' => '首页',
					'/trade' => '交易中心',
					'/user_index'=>'个人中心',
					'/upcoin.html'=>'上新币',
				);
				foreach($top_menu as $k1 => $v1){?>
					<li>
						<a<?php if(REDIRECT_URL==$k1){?> class="navActive"<?php }?> href="<?php  echo $k1; ?>"><?php  echo $v1?></a>
						<!-- <?php  isset($allCoins) || $allCoins = empty($this->layout['allCoins'])? array(): $this->layout['allCoins']; ?> -->
						<?php if($k1=='/trade'){?>
							<div class="typeList">
								<!-- <?php  foreach ($allCoins as $k2 => $v2) { ?>
									<p onclick="javascript:window.location.href='/trade/<?php  echo $v2['name']?>';">
										<img src="/img/coin_<?php  echo $v2['coin_from']?>.png" alt=""/>
										<span><?php  echo $v2['display']?>(<?php  echo strtoupper($v2['coin_from'])?>)</span>
									</p>
									<?php  } ?> -->
								<p onclick="javascript:window.location.href='/trade/btc_cny';">
									<img src="/img/coin_btc.png" alt=""/>
									<span>比特币(BTC)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/ltc_cny';">
									<img src="/img/coin_ltc.png" alt=""/>
									<span>莱特币(LTC)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/osc_cny';">
									<img src="/img/coin_osc.png" alt=""/>
									<span>桂花币(OSC)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/hxi_cny';">
									<img src="/img/coin_hxi.png" alt=""/>
									<span>华硒币(HXI)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/mryc_cny';">
									<img src="/img/coin_mryc.png" alt=""/>
									<span>美人鱼币(MRYC)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/rss_cny';">
									<img src="/img/coin_rss.png" alt=""/>
									<span>红贝壳(RSS)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/qtc_cny';">
									<img src="/img/coin_qtc.png" alt=""/>
									<span>禾中量子(QTC)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/jhp_cny';">
									<img src="/img/coin_jhp.png" alt=""/>
									<span>健和(JHP)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/jnc_cny';">
									<img src="/img/coin_jnc.png" alt=""/>
									<span>剑南Q9(JNC)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/bcc_cny';">
									<img src="/img/coin_bcc.png" alt=""/>
									<span>本草集(BCC)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/npc_cny';">
									<img src="/img/coin_npc.png" alt=""/>
									<span>NPC(NPC)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/jjf_cny';">
									<img src="/img/coin_jjf.png" alt=""/>
									<span>九九链(JJF)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/fdc_cny';">
									<img src="/img/coin_fdc.png" alt=""/>
									<span>开心粮票(FDC)</span>
								</p>
								<p onclick="javascript:window.location.href='/trade/etw_cny';">
									<img src="/img/coin_etw.png" alt="" />
									<span>以太智能(ETW)</span>
								</p>
							</div>
						<?php  }else if($k1=='/fulltrade/goc_cny'){ ?>
							<div class="typeList">
								<!-- <?php foreach ($allCoins as $k3 => $v3) { ?>
									<p onclick="javascript:window.location.href='/fulltrade/<?php  echo $v3['name']?>';">
										<img src="/img/coin_<?php  echo $v3['coin_from']?>.png" alt=""/>
										<span><?php  echo $v3['display']?>(<?php  echo strtoupper($v3['coin_from'])?>)</span>
									</p>
									<?php  } ?> -->
								<p onclick="javascript:window.location.href='/fulltrade/btc_cny';">
									<img src="/img/coin_btc.png" alt=""/>
									<span>比特币(BTC)</span>
								</p>
								<p onclick="javascript:window.location.href='/fulltrade/ltc_cny';">
									<img src="/img/coin_ltc.png" alt=""/>
									<span>莱特币(LTC)</span>
								</p>
								<p onclick="javascript:window.location.href='/fulltrade/goc_cny';">
									<img src="/img/coin_goc.png" alt=""/>
									<span>富途币(GOC)</span>
								</p>
								<p onclick="javascript:window.location.href='/fulltrade/lc_cny';">
									<img src="/img/coin_lc.png" alt=""/>
									<span>金全币(LC)</span>
								</p>
								<p onclick="javascript:window.location.href='/fulltrade/mtc_cny';">
									<img src="/img/coin_mtc.png" alt=""/>
									<span>美通币(MTC)</span>
								</p>
								<p onclick="javascript:window.location.href='/fulltrade/uc_cny';">
									<img src="/img/coin_uc.png" alt=""/>
									<span>赢联通兑(UC)</span>
								</p>
								<p onclick="javascript:window.location.href='/fulltrade/lbc_cny';">
									<img src="/img/coin_lbc.png" alt=""/>
									<span>直播币(LBC)</span>
								</p>
								<p onclick="javascript:window.location.href='/fulltrade/dsc_cny';">
									<img src="/img/coin_dsc.png" alt=""/>
									<span>打赏币(DSC)</span>
								</p>
							<!-- 	<p onclick="javascript:window.location.href='/fulltrade/gec_cny';">
									<img src="/img/coin_gec.png" alt=""/>
									<span>时代合约(GEC)</span>
								</p> -->
								<p onclick="javascript:window.location.href='/fulltrade/mac_cny';">
									<img src="/img/coin_mac.png" alt=""/>
									<span>里程积分(MAC)</span>
								</p>
								<p onclick="javascript:window.location.href='/fulltrade/lcc_cny';">
									<img src="/img/coin_lcc.png" alt=""/>
									<span>低碳币(LCC)</span>
								</p>
							</div>
						<?php  } ?>
					</li>
				<?php  }?>
			</ul>
		</div>
		<div class="nt_user right">
			<?php  if($user){?>
				<?php  if( strpos(REDIRECT_URL, '/trade') !== false && strpos(REDIRECT_URL, '/trade') == 0 ){ ?>
					<div class="header_asset">
						<div>
							<span class="asset_dis">可用</span>
							<span class="asset_coin"><?php  echo strtoupper($cData['coin_to'])?>：<span id="header_over_cny"><?php  echo (float)$user[$cData['coin_to'].'_over']?></span></span>
							<span class="asset_coin"><?php  echo strtoupper($cData['coin_from'])?>：<span id="header_over_<?php  echo $cData['coin_from']?>"><?php  echo (float)$user[$cData['coin_from'].'_over']?></span></span>
						</div>
						<div>
							<span class="asset_dis">冻结</span>
							<span class="asset_coin"><?php  echo strtoupper($cData['coin_to'])?>：<span id="header_lock_cny"><?php  echo (float)$user[$cData['coin_to'].'_lock']?></span></span>
							<span class="asset_coin"><?php  echo strtoupper($cData['coin_from'])?>：<span id="header_lock_<?php  echo $cData['coin_from']?>"><?php  echo (float)$user[$cData['coin_from'].'_lock']?></span></span>
						</div>
					</div>
				<?php  } ?>
			<?php  } else {
				isset($isMobile) || $isMobile = empty($this->layout['isMobile'])? false : true;
				?>
				<div class="hello2">
					<a href="<?php  if($isMobile){echo 'http://m.bijiaosuo.com/user/login';}else{echo '/user/login';} ?>" class="hello_login">登录</a>
					<a href="<?php  if($isMobile){echo 'http://m.bijiaosuo.com//user/register';}else{echo '/user/register';} ?>"  class="hello_register">注册</a>
				</div>
			<?php  }?>
		</div>
	</div>
</div>
