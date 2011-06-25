<form action="<?php echo $ro->gen(null); ?>" method="post">
	<dl>
		<dt><label for="fe-name"><?php echo $tm->_('Product Name', 'default.SearchEngineSpam'); ?></label></dt>
		<dd><input type="text" name="name" id="fe-name" /></dd>
		<dt><label for="fe-price"><?php echo $tm->_('Price', 'default.SearchEngineSpam'); ?></label></dt>
		<dd><input type="text" name="price" id="fe-price" /></dd>
		<dt>&#160;</dt>
		<dd><input type="submit" value="Save" /></dd>
	</dl>
</form>