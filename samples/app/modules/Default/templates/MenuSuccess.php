<h3><?php echo $tm->_('Menu', 'default.layout'); ?></h3>
<ul>
	<li><a href="<?php echo $ro->gen('index'); ?>"><?php echo $tm->_('Home', 'default.menu'); ?></a></li>
<?php if(!$us->isAuthenticated()): ?>
	<li><a href="<?php echo $ro->gen('login'); ?>"><?php echo $tm->_('Login', 'default.menu'); ?></a></li>
<?php endif; ?>
	<li><a href="<?php echo $ro->gen('secure'); ?>"><?php echo $tm->_('A Secure Action', 'default.menu'); ?></a></li>
	<li><a href="<?php echo $ro->gen('secure2'); ?>"><?php echo $tm->_('Another Secure Action', 'default.menu'); ?></a></li>
	<li><a href="<?php echo $ro->gen('asdjashdasd'); ?>" onclick="return alert('<?php echo $tm->_('You will now be redirected to an invalid URL. If no rewrite rules are in place, this means you will see a standard 404 page of your web server, unless you configured an ErrorDocument 404 or some similar setting. If rewrite rules are in place (i.e. no index.php part in the URL), you will be shown the Agavi 404 document. This is correct and expected behavior.', 'default.menu'); ?>');"><?php echo $tm->_('Call invalid URL', 'default.menu'); ?></a></li>
	<li><a href="<?php echo $ro->gen('disabled'); ?>"><?php echo $tm->_('Try Disabled Module', 'default.menu'); ?></a></li>
	<li><a href="<?php echo $ro->gen('search_engine_spam', array('name' => $t['product']->getName(), 'id' => $t['product']->getId())); ?>"><b><?php echo $tm->_('Search Engine Spam', 'default.menu'); ?></b></a></li>
</ul>
