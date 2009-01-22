<?php
$locale = $tm->getCurrentLocale();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale->getLocaleLanguage(); ?>" lang="<?php echo $locale->getLocaleLanguage(); ?>"<?php if($locale->getCharacterOrientation() == 'right-to-left'): ?> dir="rtl"<?php endif; ?>>
	<head>
		<meta http-equiv="Content-Type" content="<?php echo $container->getOutputType()->getParameter('http_headers[Content-Type]', 'text/html; charset=utf-8'); ?>" />
		<title><?php echo $tm->_('Default Agavi Module', 'default.layout'); ?></title>
		<base href="<?php echo $ro->getBaseHref(); ?>" />
		<link rel="stylesheet" type="text/css" href="css/style.css" />
	</head>
	<body>
		<p class="runin"><?php echo $tm->_d($tm->createCalendar()); ?></p>
<?php if($us->isAuthenticated()): ?>
		<p class="runin"><?php echo $tm->_('You are logged in.', 'default.layout'); ?> <a href="<?php echo $ro->gen('logout'); ?>"><?php echo $tm->_('Log Out', 'default.layout'); ?></a></p>
<?php endif; ?>
		<h1><?php echo $tm->_('Agavi Sample Application', 'default.layout'); ?></h1>
		
		<div id="menu">
<?php echo $slots['menu']; ?>
		</div>
		
		<div id="content">
			<h2><?php echo $t['_title']; ?></h2>
<?php echo $inner; // print the content layer output ?>
		</div>
		
		<div id="footer">
			<p><?php echo $tm->__('Alternative language:', 'Alternative languages:', count($availableLocales = $tm->getAvailableLocales()), 'default.layout'); ?>
<?php foreach($availableLocales as $availableLocale): ?>
				<a href="<?php echo $ro->gen(null, array('locale' => $availableLocale['identifier'])); ?>" hreflang="<?php echo $availableLocale['identifier']; ?>"<?php if($availableLocale['identifier'] == $tm->getCurrentLocaleIdentifier()): ?> style="font-weight:bold"<?php endif;?>><?php echo htmlspecialchars($availableLocale['parameters']['description']); ?></a>
<?php endforeach; ?>
			</p>
			<p>Copyright © 2005-2009 The Agavi Project</p>
		</div>
	</body>
</html>
