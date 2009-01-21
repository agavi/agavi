<p><?php echo $tm->_('You just found the #1 place to buy <strong>%s</strong> at low prices!', 'default.SearchEngineSpam', null, array($t['product']->getName())); ?></p>
<dl>
	<dt><?php echo $tm->_('Product Name', 'default.SearchEngineSpam'); ?></dt>
	<dd><?php echo $t['product']->getName(); ?></dd>
	<dt><?php echo $tm->_('Price', 'default.SearchEngineSpam'); ?></dt>
	<dd><?php echo $tm->_c($t['product']->getPrice()); ?></dd>
</dl>
<p><a href="<?php echo $ro->gen(null, array('name' => null)); ?>">Product ShortLink</a></p>