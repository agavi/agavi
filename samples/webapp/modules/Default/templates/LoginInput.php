<form action="<?php echo $this->getContext()->getRouting()->gen('login'); ?>" method="post">
	<dl>
		<dt><label for="fe-username">Username:</label></dt>
		<dd><input type="text" name="username" id="fe-username" /></dd>
		<dt><label for="fe-password">Password:</label></dt>
		<dd><input type="password" name="password" id="fe-password" /></dd>
		<dt>&nbsp;</dt>
		<dd><input type="checkbox" name="remember" id="fe-remember" /><label for="fe-remember"> Log me in automatically.</label></dd>
		<dt>&nbsp;</dt>
		<dd><input type="submit" value="Login" /></dd>
	</dl>
</form>