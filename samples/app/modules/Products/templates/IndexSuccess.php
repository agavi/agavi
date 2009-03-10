<table>
	<tr>
		<th><?php echo $tm->_('Product Name', 'default.SearchEngineSpam'); ?></th>
		<th><?php echo $tm->_('Price', 'default.SearchEngineSpam'); ?></th>
	</tr>
<?php foreach($t['products'] as $product): ?>
	<tr>
		<td><a href="<?php echo $ro->gen('products.product.view', array('id' => $product->getId(), 'name' => $product->getName())); ?>"><?php echo htmlspecialchars($product->getName()); ?></a></td>
		<td align="right"><?php echo $tm->_c($product->getPrice()); ?></td>
	</tr>
<?php endforeach; ?>
</table>
