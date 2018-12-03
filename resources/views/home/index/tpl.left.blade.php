<div class="box">
	<div class="TitleBox">
		<h3 class="PlateTitle">币交所</h3>
	</div>
	<div class="about clear">
		<?php  foreach($pages as $k1 => $v1){?>
			<a <?php if($k1==$page)echo 'class="aboutAction"'?> href="/<?php  echo $k1?>.html"><?php  echo $v1?></a>
		<?php  }?>
	</div>
</div>