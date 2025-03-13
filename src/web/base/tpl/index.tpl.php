<!DOCTYPE html>
<html>
<head>
	<title>openQRM Enterprise Server</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">

	<link rel="icon" href="{baseurl}/img/favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="{baseurl}/img/favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="{baseurl}/css/bootstrap/normalize.css" type="text/css" media="all">
	<link rel="stylesheet" href="{baseurl}/css/bootstrap/bootstrap.css" type="text/css" media="all">
	<link rel="stylesheet" href="{baseurl}/css/bootstrap/glyphicons.css" type="text/css" media="all">
	<link rel="stylesheet" href="{baseurl}/css/bootstrap/halflings.css" type="text/css" media="all">

	<link rel="stylesheet" href="{baseurl}/css/htmlobject.css" type="text/css" media="all">
	<link rel="stylesheet" href="{baseurl}/css/default.css" type="text/css" media="all">
	
	<link rel="stylesheet" href="{baseurl}/css/menu.css" type="text/css" media="all">
	<!-- following stylesheet is used by progressbars in DC dashboard -->
	<link rel="stylesheet" href="{baseurl}/js/jquery/development-bundle/themes/smoothness/ui.all.css" type="text/css" media="all">
	<link rel="stylesheet" href="{baseurl}/css/openqrm-ui.css" type="text/css" media="all">
	{style}

	{jstranslation}
	
	<script src="{baseurl}/js/jquery/js/jquery-1.3.2.min.js" type="text/javascript"></script>
	<script src="{baseurl}/js/jquery/js/jquery-ui-1.7.1.custom.min.js" type="text/javascript"></script>
	<script src="{baseurl}/js/interface/interface.js" type="text/javascript"></script>
	<script src="{baseurl}/js/menu.js" type="text/javascript"></script>
	<script src="{baseurl}/js/helpers.js" type="text/javascript"></script>
	{script}

</head>
<body lang="{lang}">

	<div id="page">
		<div id="wrapper">
			<div id="head">{top}</div>
		
			<div class="container">
				<div class="row">
					<div class="span2 menu">{menu}</div>
					<div class="span10 middle">{content}</div>
				</div>
			</div>
		
			<div id="footer">
				<div id="performance-stats" class="pull-left">
					<small>{memory}<br>{time}</small>
				</div>
				<div id="openqrm_enterprise_footer" class="pull-right">
					<a href="http://www.openqrm-enterprise.com/" target="_blank">openQRM Community Edition | &copy; 2012 - {currentyear} OPENQRM AUSTRALIA PTY LTD</a>
				</div>
			</div>
		</div>
	</div>
	
	<script src="{baseurl}/js/openqrm-ui.js" type="text/javascript"></script>
</body>
</html>
