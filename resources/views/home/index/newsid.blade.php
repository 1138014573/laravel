<?php  include PATH_TPL.'/tpl.header.phtml';?>
<style>
.news-detail {height:30px;line-height: 30px;}
.news-detail span {display: block;float:right;height:30px;padding:0 5px;text-align: center;}
strong{font-size: 14px;font-weight: bolder;color: #000;}
strong span{font-size: 16px;font-weight: bolder;color: #000;font-family: 'Microsoft YaHei';}
</style>
<div class="content clear">
		<div class="Right" style="width: 1000px;">
		<div class="accountBox">
			<div class="wrap">
				<?php foreach($data as $v) {?>
				<div class="box">
					<div class="TitleBox">
						<h3 class="PlateTitle"><?php  echo $v['title']?></h3>
					</div>
					<div class="aboutText">
						<?php  if($v['id'] == 15){ ?>
							<embed wmode="direct" flashvars="vid=y0181c16uh4&amp;tpid=0&amp;showend=1&amp;showcfg=1&amp;searchbar=1&amp;pic=http://shp.qpic.cn/qqvideo_ori/0/y0181c16uh4_496_280/0&amp;skin=http://imgcache.qq.com/minivideo_v1/vd/res/skins/TencentPlayerMiniSkin.swf&amp;shownext=1&amp;list=2&amp;autoplay=0" src="https://imgcache.qq.com/tencentvideo_v1/player/TPout.swf?max_age=86400&amp;v=20140714" quality="high" name="tenvideo_flash_player_1464170459241" id="tenvideo_flash_player_1464170459241" bgcolor="#000000" width="670px" height="502px" align="middle" allowscriptaccess="always" allowfullscreen="true" type="application/x-shockwave-flash" pluginspage="http://get.adobe.com/cn/flashplayer/">
						<?php  } ?>
						<?php  echo $v['content']?>
						<div class="news-detail">
							<!-- <span class="news-time">发布时间：<?php  echo date('Y-m-d', $v["created"])?></span> -->
						</div>
					</div>
				</div>
				<?php }?>
			</div>
		</div>
	</div>
</div>
<?php  include PATH_TPL.'/tpl.footer.phtml';?>
