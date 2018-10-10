$(function () {
//////////////////////////////////////////////////////////////////////////
////// START - CODE FOR LOADING THE TOP MENU CHASING BAR
//////////////////////////////////////////////////////////////////////////

	var $doc = $(document), $win = $(window), $chaser, $forch,
		forchBottom, visible;
	function shown() {
		visible = true;
	}
	function hidden() {
		visible = false;
	}
	$chaser = $('#header ul.menu').clone().hide().appendTo(document.body).wrap("<div class='chaser'></div>");
	$forch = $('.forchaser').first();
	forchBottom = $forch.height() + $forch.offset().top;
	hidden();
	$win.bind('scroll', function () {
		var top = $doc.scrollTop();
		if (!visible && top > forchBottom) {
			$chaser.slideDown(300, shown);
		} else if (visible && top < forchBottom) {
			$chaser.slideUp(200, hidden);
		}
	});

});


$(window).load(function() {
//////////////////////////////////////////////////////////////////////////
// SOCIAL ICONS SMALL SLIDER
//////////////////////////////////////////////////////////////////////////
	$('li.social-icons > a').click(function() {
		var $t = $(this).next();
		if($(this).hasClass('opened')) {
			$t.parent().animate({width:$(this).width()+'px'}, 300, 'easeOutExpo');
			$(this).removeClass('opened');
		}else{
			$t.parent().animate({width:($t.find('.inner').outerWidth()+$(this).width())+'px'}, 300, 'easeOutExpo');
			$(this).addClass('opened');
		}
	});


//////////////////////////////////////////////////////////////////////////	
// SEARCH FIELD SMALL SLIDER
//////////////////////////////////////////////////////////////////////////
	$('li.search-box > a').click(function() {
		var $t = $(this).next();
		if($(this).hasClass('opened')) {
			$t.parent().animate({width:$(this).width()+'px'}, 300, 'easeOutExpo');
			$(this).removeClass('opened');
		}else{
			$t.parent().animate({width:($t.find('input[type=text]').outerWidth()+$(this).width())+'px'}, 300, 'easeOutExpo');
			$(this).addClass('opened');
		}
	});


//////////////////////////////////////////////////////////////////////////
// INSERT SHADOW UNDER IMAGES HAVING
//////////////////////////////////////////////////////////////////////////
    function shadow_eash() {
        $(this).wrap('<div class="block-img-shadow" />');
        $('<div class="under-shadow"><img src="/static/img/shadow_210.png" /></div>').insertAfter(this);
    }
	$('img.with-shadow-1').each(shadow_eash);
	$('img.with-shadow-2').each(shadow_eash);
	$('img.with-shadow-3').each(shadow_eash);

	$('.head-image').each(function() {
		$(this).parent().append('<span class="zoom-overlay"></span>');
	});


//////////////////////////////////////////////////////////////////////////
// SWAPPERS FROM SLIDESHOW
//////////////////////////////////////////////////////////////////////////
	var sp = 300;
	$('#swappers div.swapper').hover(
		function() {
			$(this).stop().animate({top:'-180px'}, {queue: false, duration: sp, easing: "easeOutExpo"});
			$(this).find('.bgr').stop().animate({'opacity':0.9}, {queue: false, duration: sp, easing: "easeOutExpo"});
			$(this).find('p').stop().animate({'opacity':1}, {queue: false, duration: sp, easing: "easeOutExpo"});
			$(this).find('.indicator').css('background-position','center top');
		},
		function() {
			$(this).stop().animate({top:'0px'}, {queue: false, duration: sp, easing: "easeOutExpo"});
			$(this).find('.bgr').stop().animate({'opacity':0.5}, {queue: false, duration: sp, easing: "easeOutExpo"});
			$(this).find('p').stop().animate({'opacity':0.5}, {queue: false, duration: sp, easing: "easeOutExpo"});
			$(this).find('.indicator').css('background-position','center bottom');
		}
	);


//////////////////////////////////////////////////////////////////////////
// ADD A LAST CLASS TO DIFFERENT ELEMENTS TO REMOVE MARGINS/PADDINGS
//////////////////////////////////////////////////////////////////////////
	$("#flickr_container li:nth-child(3n)").addClass("last");
	$(".small_ads li:nth-child(2n)").addClass("last");
	$(".projects .items li:nth-child(4n)").addClass("last");
	


//////////////////////////////////////////////////////////////////////////	
// LIVE COMMENTS OPENER
//////////////////////////////////////////////////////////////////////////
	$('.live-comments .opener').click(function(e) {
		e.preventDefault();
		var $t = $(this).next();
		if($t.hasClass('opened')) {
			$(this).removeClass('active');
			$t.removeClass('opened');
		}else{
			$(this).addClass('active');
			$t.addClass('opened');
		}
	})

	
//////////////////////////////////////////////////////////////////////////	
// TRIGGER TO SHOW THE HIDDEN MAP
//////////////////////////////////////////////////////////////////////////
	$('.map_link').click(function(e) {
		e.preventDefault();
		var $m = $('.hidden-map-wrapper');
		if($m.hasClass('opened')) {
			$m.stop().animate({height:0}, {queue: false, duration: 300, easing: "easeOutExpo"});
			$m.removeClass('opened');
		}else{
			$m.stop().animate({height:'350px'}, {queue: false, duration: 300, easing: "easeOutExpo"});
			$('html, body').animate({scrollTop: '5000px'}, 300,'easeOutExpo');
			$m.addClass('opened');
            var map = new BMap.Map("allmap");
            var point = new BMap.Point(116.331398,39.897445);
            new BMap.Geocoder().getPoint("元大都7号", function(point){map.centerAndZoom(point, 16);map.addOverlay(new BMap.Marker(point));}, "北京市");
		}
	})
	$('.close-map').click(function(e) {
		e.preventDefault();
		var $m = $('.hidden-map-wrapper');
		$m.stop().animate({height:0}, {queue: false, duration: 300, easing: "easeOutExpo"});
		$m.removeClass('opened');
	})


//////////////////////////////////////////////////////////////////////////
//INITIALIZE THE SUPERFISH MENU
//////////////////////////////////////////////////////////////////////////
	$(function($){ $("ul.sf-menu").supersubs({minWidth:13, maxWidth:30, extraWidth:0}).superfish({hoverClass:'sfHover', pathClass:'sf-active', pathLevels:0, delay:500, animation:{height:'show'}, speed:'def', autoArrows:1, dropShadows:0}) });


//////////////////////////////////////////////////////////////////////////
// INIT INFIELD LABELS
//////////////////////////////////////////////////////////////////////////
	$("#newsletter-form label, .infield label").inFieldLabels();


//////////////////////////////////////////////////////////////////////////
// LOAD TESTIMONIALS FADE TRANSITIONS
//////////////////////////////////////////////////////////////////////////
	$('#testimonials blockquote').quovolver();

//////////////////////////////////////////////////////////////////////////	
// ACCORDION - Tutorial by Soh Tanaka - http://www.sohtanaka.com/web-design/easy-toggle-jquery-tutorial/
//////////////////////////////////////////////////////////////////////////	

$('.acc_container').hide(); //Hide/close all containers

// if you want to show the first div uncomment the line below  <-- read this
//Add "active" class to first trigger, then show/open the immediate next container
//$('.acc_trigger:first').addClass('active').next().show(); 

$('.acc_trigger').click(function(e){
	if( $(this).next().is(':hidden') ) { //If immediate next container is closed...
		$('.acc_trigger').removeClass('active').next().slideUp(); //Remove all "active" state and slide up the immediate next container
		$(this).toggleClass('active').next().slideDown(); //Add "active" state to clicked trigger and slide down the immediate next container
	} else {
		$('.acc_trigger').removeClass('active').next().slideUp(); //Remove all "active" state and slide up the immediate next container
	}
	e.preventDefault(); //Prevent the browser jump to the link anchor
});


//////////////////////////////////////////////////////////////////////////	
// SIMPLE TABS - Tutorial by Soh Tanaka - http://www.sohtanaka.com/web-design/simple-tabs-w-css-jquery/
//////////////////////////////////////////////////////////////////////////	

	$("#simple-tabs .tab_content").hide(); //Hide all content
	$("#simple-tabs ul.tabs li:first").addClass("active").show(); //Activate first tab
	$("#simple-tabs .tab_content:first").show(); //Show first tab content
	
	//On Click Event
	$("#simple-tabs ul.tabs li").click(function(e) {
		$("#simple-tabs ul.tabs li").removeClass("active"); //Remove any "active" class
		$(this).addClass("active"); //Add "active" class to selected tab
		$("#simple-tabs .tab_content").hide(); //Hide all tab content
		var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
		$(activeTab).fadeIn(); //Fade in the active content
		e.preventDefault();
	});

//////////////////////////////////////////////////////////////////////////	
// TOGGLES - Tutorial by Soh Tanaka - http://www.sohtanaka.com/web-design/easy-toggle-jquery-tutorial/
//////////////////////////////////////////////////////////////////////////	

	//Hide (Collapse) the toggle containers on load
	$(".toggle_container").hide(); 

	//Switch the "Open" and "Close" state per click then slide up/down (depending on open/close state)
	$(".tgg-trigger").click(function(){
		$(this).toggleClass("active").next().slideToggle("slow");
		return false; //Prevent the browser jump to the link anchor
	});
	
	
//////////////////////////////////////////////////////////////////////////	
// ADD ODD CLASS TO ROWS
//////////////////////////////////////////////////////////////////////////	

	$(".zebra-style tr:odd, .faq-style .tgg-trigger:odd").addClass("odd");

})// end of window load
