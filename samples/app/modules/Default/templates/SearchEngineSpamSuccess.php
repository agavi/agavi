<p><?php echo $tm->_('You just found the #1 place to buy <strong>%s</strong> at low prices!', 'default.SearchEngineSpam', null, array($template['product_name'])); ?></p>
<table border="1">
	<tr>
		<th><?php echo $tm->_('Product Name', 'default.SearchEngineSpam'); ?></th>
		<th><?php echo $tm->_('Price', 'default.SearchEngineSpam'); ?></th>
	</tr>
	<tr>
		<td><?php echo $template['product_name']; ?></td>
		<td><?php echo $tm->_c($template['product_price']); ?></td>
	</tr>
</table>

<?=time()?>