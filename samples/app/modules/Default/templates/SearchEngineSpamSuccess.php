<p><?php echo $tm->_('You just found the #1 place to buy <strong>%s</strong> at low prices!', 'default.SearchEngineSpam', null, array($t['product']->getName())); ?></p>
<table border="1">
	<tr>
		<th><?php echo $tm->_('Product Name', 'default.SearchEngineSpam'); ?></th>
		<th><?php echo $tm->_('Price', 'default.SearchEngineSpam'); ?></th>
	</tr>
	<tr>
		<td><?php echo $t['product']->getName(); ?></td>
		<td><?php echo $tm->_c($t['product']->getPrice()); ?></td>
	</tr>
</table>
<p><a href="<?php echo $ro->gen(null, array('name' => null)); ?>">Product ShortLink</a></p>