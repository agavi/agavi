<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Default Agavi Module</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<style type="text/css">

body {
	background-color: #FFFFFF;
	color:            #000000;
	font-family:      arial, helvetica, sans-serif;
	font-size:        76%;
	font-style:       normal;
	font-weight:      normal;
	margin:           0;
}

#content {
	margin-left:  200px;
	margin-right: 15px;
}

#header {
	background-color: #EAEAEA;
	border-bottom:    solid 1px #505050;
	color:            #505050;
	font-family:      arial, helvetica, sans-serif;
	font-size:        2.0em;
	letter-spacing:   0.03em;
	margin:           0 0 15px 0;
	padding:          10px 0 10px 15px;
}

#menu {
	border:           solid 1px #505050;
	float:            left;
	margin-left:      15px;
	width:            160px;
}

#menu a {
	background-color: #EAEAEA;
	color:            #000000;
	display:          block;
	padding:          5px 0 5px 10px;
	text-decoration:  none;
}

#menu a:hover {
	background-color: #505050;
	color:            #FFFFFF;
}

#menu h3 {
	background-color: #750000;
	color:            #FFFFFF;
	font-size:        1.3em;
	margin:           0;
	padding:          5px 0 5px 10px;
}

#menu li {
	background-color: #909090;
	height:           1%;
	list-style-type:  none;
	margin:           0;
	padding:          0;
}

#menu ul {
	margin:  0;
	padding: 0;
}

#title {
	border-bottom:  solid 1px #373737;
	color:          #373737;
	font-size:      2.0em;
	letter-spacing: 0.03em;
	margin-bottom:  15px;
}

/* IE Windows hack */

* html #content {
	margin-left: 197px;
}

* html #menu {
	margin-left: 8px;
}

* html #menu li {
	margin-bottom: -5px;
}

</style>
</head>
<body>

<div id="header">
	Default Agavi Module
</div>

<div id="menu">
	<h3>Actions</h3>
	<ul>
		<li><a href="<?php echo $template['link']['default_action'] ?>">Default Action</a></li>
		<li><a href="<?php echo $template['link']['error_404_action'] ?>">Error 404 Action</a></li>
		<li><a href="<?php echo $template['link']['login_action'] ?>">Login Action</a></li>
		<li><a href="<?php echo $template['link']['module_disabled_action'] ?>">Module Disabled Action</a></li>
		<li><a href="<?php echo $template['link']['secure_action'] ?>">Secure Action</a></li>
		<li><a href="<?php echo $template['link']['unavailable_action'] ?>">Unavailable Action</a></li>
	</ul>
</div>

<div id="content">

	<div id="title"><?php echo $template['title'] ?></div>
