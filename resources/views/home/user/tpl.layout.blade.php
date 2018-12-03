<?php  include PATH_TPL.'/tpl.header.phtml';?>
<div class="content clearfix">
	<div class="Left left">
		<div class="accountBox">
			<div class="box">
				<div>
					<h3 class="tn_title">我的资产</h3>
				</div>
				<ul class="user_asset list">
					<?php  foreach(array(
						 '/user_index' => '账户资产',
					 ) as $k1 => $v1){?>
						<li<?php if(REDIRECT_URL==$k1){?> class="listActive"<?php }?>><a href="<?php  echo $k1?>"><?php  echo $v1?></a><?php if($k1=='/user_info/member'){echo "";}?></li>
					<?php  }?>
				</ul>
				<div>
					<h3 class="tn_title">我的交易</h3>
				</div>
				<ul class="user_trade list">
					<?php  foreach(array(
						 '/user_trust/list' => '委托管理',
						 '/user_order/list' => '成交查询',
					 ) as $k1 => $v1){?>
						<li<?php if(REDIRECT_URL==$k1){?> class="listActive"<?php }?>><a href="<?php  echo $k1?>"><?php  echo $v1?></a><?php if($k1=='/user_info/member'){echo "";}?></li>
					<?php  }?>
				</ul>
				<div>
					<h3 class="tn_title">安全中心</h3>
				</div>
				<ul class="user_safe list">
					<?php  foreach(array(
						 '/user_index/pwd' => '修改登录密码',
						 '/user_index/tradepwd' => '修改交易密码',
						 '/user_index/twofactor' => '账户双重验证',
					 ) as $k1 => $v1){?>
						<li<?php if(REDIRECT_URL==$k1){?> class="listActive"<?php }?>><a href="<?php  echo $k1?>"><?php  echo $v1?></a><?php if($k1=='/user_info/member'){echo "";}?></li>
					<?php  }?>
				</ul>
				<div>
					<h3 class="tn_title">账户中心</h3>
				</div>
				<ul class="user_info list">
					<?php  foreach(array(
						 '/user_exchange/bankbind' => '绑定银行卡',
						 // '/user_exchange/addressbind' => '绑定提币地址',
						 '/user_index/userinfo' => '账户信息',
						 '/user_index/invite' => '邀请好友',
					 ) as $k1 => $v1){?>
						<li<?php if(REDIRECT_URL==$k1){?> class="listActive"<?php }?>><a href="<?php  echo $k1?>"><?php  echo $v1?></a><?php if($k1=='/user_info/member'){echo "";}?></li>
					<?php  }?>
				</ul>
			</div>
		</div>
	</div>
	<div class="Rightinfo">
		<?php  echo $this->content?>
	</div>
</div>
<?php  include PATH_TPL.'/tpl.footer.phtml';?>
<script>
	// 更改左侧导航里li的图标
	$('.list').each(function(){
		var ul_index = $(this).index();
		$(this).find('li').each(function(){
			var li_index = $(this).index();
			if( $(this).hasClass('listActive') ){
				$(this).addClass('ucion'+ul_index+'_'+li_index+'_on');
			}else{
				$(this).addClass('ucion'+ul_index+'_'+li_index);
			}
		});
	});
	// .list 下li de mouseover事件
	$('.list').each(function(){
		var ul_index = $(this).index();
		$(this).find('li').each(function(){
			var li_index = $(this).index();
			if( !$(this).hasClass('listActive') ){
				$(this).mouseover(function(){
					$(this).removeClass().addClass('ucion'+ul_index+'_'+li_index+'_on');
				}).mouseout(function(){
					$(this).removeClass().addClass('ucion'+ul_index+'_'+li_index);
				});
			}
		});
	});

</script>