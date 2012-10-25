<?php ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Algorithms</title>
<? echo $pageargs["htmllinktags"]; ?>
<link href="/css/ui-lightness/jquery-ui-1.8.21.custom.css" rel="stylesheet" type="text/css" />
<link href="/css/style.css" rel="stylesheet" type="text/css" />
<script src="/js/html5.js" type="text/javascript"></script>
<script type="text/javascript" src="/js/jquery-1.7.2.min.js" charset="utf-8"></script>
<script type="text/javascript" src="/js/jquery-ui.custom.min.js"></script>
<? if(isset($pageargs["javascript"])) { echo $pageargs["javascript"]; } ?>
<!-- slider starts -->
<!-- <script type="text/javascript" src="/js/jquery-1.4.2.min.js" charset="utf-8"></script> -->
<!--
<script src="/js/jquery.cycle.all.min.js" type="text/javascript" charset="utf-8"></script>
<script language="javascript">
 $(document).ready(function(){
 	$('div.slides').cycle({ 
			slideExpr: 'ul.list2 li',
			fx: 'scrollLeft', // choose your transition type, ex: fade, scrollUp, shuffle, etc...
			pager: '.controls-strip',
			timeout:  0,
			pagerAnchorBuilder: function(idx, slide) { 
			myClass = (idx==0)?' class="activeSlide"':'';
			return '<a href="#" '+myClass+'></a>'; 
			}
		});
	});
 </script>
-->
<!--/ slider ends -->
</head>
<body style="overflow-x: hidden;"><!-- MRR20120522: Note we need overflow-x:hidden to stop scrollbar from appearing in wizard. -->
<div class="layout">
  <!-- header starts -->
<? if(isset($pageargs["header_off"])) { ?>
<? } else { ?>
  <header>
    <div class="header-inner">
      <div class="logo npl"> <a href="/index/index"><img width="325" height="58" src="/images/logo.png" alt="logo" /></a></div>
      <div class="right-div">
        <div class="sign-in"><a href="/login/logout">Logout</a> </div>
        <nav>
          <ul id="links">
            <li><a href="/docs">Documentation</a></li>                                                                                      
            <li><a href="/index/aboutus"> About Us</a></li>                                                                       
            <li><a href="/Dashboard">Dashboard </a></li>          
          </ul>
        </nav>
      </div>
    </div>
  </header>
<? } ?>
  

<!-- header ends -->
  <!-- body starts -->
<? if (isset($pageargs["body_css"])) { ?>
  <div class="body-outer <?=$pageargs["body_css"];?>">
<? } else { ?>
  <div class="body-outer">  
<? } ?>
    <div class="body" style="min-width: 500px;">

