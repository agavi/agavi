<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $t['_locale']['language'] ?>" lang="<?php echo $t['_locale']['language']; ?>"<?php if($t['_locale']['rtl']): ?> dir="rtl"<?php endif; ?>>
	<head>
		<meta http-equiv="Content-Type" content="<?php echo $t['_content_type']; ?>" />
		<title><?php echo $tm->_('Default Agavi Module', 'default.layout'); ?></title>
		<base href="<?php echo $t['_base_href']; ?>" />
		<link rel="stylesheet" type="text/css" href="css/style.css" />
	</head>
	<body>
<?php echo $slots['header']; ?>
		
<?php echo $slots['menu']; ?>
		
		<div id="content">
			<h2><?php echo $t['_title']; ?></h2>
<?php echo $inner; // print the content layer output ?>
		</div>
		
<?php echo $slots['footer']; ?>
	</body>
</html>