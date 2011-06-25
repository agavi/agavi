<dl>
	<dt><?php echo $tm->_('Product Name', 'default.SearchEngineSpam'); ?></dt>
	<dd><?php echo $t['product']->getName(); ?></dd>
	<dt><?php echo $tm->_('Price', 'default.SearchEngineSpam'); ?></dt>
	<dd><?php echo $tm->_c($t['product']->getPrice()); ?></dd>
</dl>
<p><a href="<?php echo $ro->gen(null, array('name' => null)); ?>">Product ShortLink</a></p>
<?php if ($us->isAllowed($t['product'], 'write')): ?>
<p><a href="<?php echo $ro->gen('products.product.edit', array('name' => null, 'id' => $t['product']->getId())); ?>">Edit Product</a></p>
<?php endif; ?>