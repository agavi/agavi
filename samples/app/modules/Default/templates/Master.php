<?php
$locale = $tm->getCurrentLocale();
$rtl = ($locale->getCharacterOrientation() == 'right-to-left');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale->getLocaleLanguage(); ?>" lang="<?php echo $locale->getLocaleLanguage(); ?>"<?php if($rtl): ?> dir="rtl"<?php endif; ?>>
	<head>
		<meta http-equiv="Content-Type" content="<?php echo $container->getOutputType()->getParameter('Content-Type', 'text/html; charset=utf-8'); ?>" />
		<title><?php echo $tm->_('Default Agavi Module', 'default.layout'); ?></title>
		<base href="<?php echo $ro->getBaseHref(); ?>" />
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
<?php if($rtl): ?>
			margin-left:  15px;
			margin-right: 200px;
<?php else: ?>
			margin-left:  200px;
			margin-right: 15px;
<?php endif; ?>
		}

		h1 {
			display:					block;
			background-color: #EAEAEA;
			border-bottom:    solid 1px #505050;
			color:            #505050;
			font-family:      arial, helvetica, sans-serif;
			font-size:        2.0em;
			letter-spacing:   0.03em;
			margin:           0 0 0.5em 0;
			padding:          0.3em 0.4em
		}

		#languages {
			position: absolute;
<?php if($rtl): ?>
			left: 0;
<?php else: ?>
			right: 0;
<?php endif; ?>
			top: 0;
			padding: 0.5em;
		}

		#menu {
			border:           solid 1px #505050;
<?php if($rtl): ?>
			float: right;
<?php else: ?>
			float: left;
<?php endif; ?>
			margin:           0 1em;
			width:            14em;
		}

		#menu a {
			background-color: #EAEAEA;
			color:            #000000;
			display:          block;
			padding:          0.5em;
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
			padding:          0.3em 0.5em;
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

		input.error, textarea.error {
			background-color: #FFE0E0;
		}

		label.error {
			color: #D00;
		}

		p.error {
			padding:          0.5em;
			border:           2px solid #D66;
			background-color: #FFF0F0;
			color:            #D00;
		}

		p.runin {
<?php if($rtl): ?>
			float: left;
<?php else: ?>
			float: right;
<?php endif; ?>
			margin:  0 1em 1em 1em;
			padding: 0.3em 0.5em;
			border:  1px solid #DDD;
			background-color: #EEE;
		}

		</style>
	</head>
	<body>
		<h1><?php echo $tm->_('Agavi Sample Application', 'default.layout'); ?></h1>
<?php
$languages = array();
foreach($tm->getAvailableLocales() as $availableLocale) {
	$languages[$availableLocale['identifier']] = $availableLocale['parameters']['description'];
}

$currentLanguage = $tm->getCurrentLocaleIdentifier();

$otherLanguages = array_diff_key($languages, array($currentLanguage => null));
?>
		<div id="languages"><?php echo $tm->_('Current language:', 'default.layout'); ?> <a href="<?php echo $ro->gen(null); ?>" hreflang="<?php echo $currentLanguage; ?>"><?php echo $languages[$currentLanguage]; ?></a>.<br /><?php echo $tm->__('Alternative language:', 'Alternative languages:', count($otherLanguages), 'default.layout'); ?> <?php $first = true; foreach($otherLanguages as $key => $value): if(!$first) echo ', '; ?><a href="<?php echo $ro->gen(null, array('locale' => $key)); ?>" hreflang="<?php echo $key; ?>"><?php echo $value; ?></a><?php $first = false; endforeach; ?></div>
<p class="runin"><?php echo $tm->_d($tm->createCalendar()); ?>.<?php if($us->isAuthenticated()): ?> <?php echo $tm->_('You are logged in.', 'default.layout'); ?> <a href="<?php echo $ro->gen('logout'); ?>"><?php echo $tm->_('Log Out', 'default.layout'); ?></a><?php endif; ?></p>
		<div id="menu">
<?php echo $slots['menu']; ?>
		</div>
		<div id="content">
			<h2><?php echo $template['title']; ?></h2>
<?php if($container->getValidationManager()->hasErrors()): foreach($container->getValidationManager()->getErrorMessages() as $error): ?>
			<p class="error"><?php echo $error['message']; ?></p>
<?php endforeach; endif; ?>
<?php echo $inner; // print the content layer output ?>
		</div>
	</body>
</html>
