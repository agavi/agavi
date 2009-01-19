<form action="<?php echo $ro->gen('login'); ?>" method="post">
	<dl>
		<dt><label for="fe-username"><?php echo $tm->_('Username:', 'default.Login'); ?></label></dt>
		<dd><input type="text" name="username" id="fe-username" /></dd>
		<dt><label for="fe-password"><?php echo $tm->_('Password:', 'default.Login'); ?></label></dt>
		<dd><input type="password" name="password" id="fe-password" /></dd>
		<dt>&#160;</dt>
		<dd><input type="checkbox" name="remember" id="fe-remember" /><label for="fe-remember"> <?php echo $tm->_('Log me in automatically.', 'default.Login'); ?></label></dd>
		<dt>&#160;</dt>
		<dd><input type="submit" value="<?php echo $tm->_('Login', 'default.Login'); ?>" /></dd>
	</dl>
</form>