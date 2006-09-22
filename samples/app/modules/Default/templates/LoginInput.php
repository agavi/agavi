<form action="<?php echo $r->gen('login'); ?>" method="post" id="foo">
	<dl>
		<dt><label for="fe-username">Username:</label></dt>
		<dd><input type="text" name="username" id="fe-username" /></dd>
		<dt><label for="fe-password">Password:</label></dt>
		<dd><input type="password" name="password" id="fe-password" /></dd>
		<dt>&#160;</dt>
		<dd><input type="checkbox" name="remember" id="fe-remember" /><label for="fe-remember"> Log me in automatically.</label></dd>
		<dt>&#160;</dt>
		<dd><input type="submit" value="Login" /></dd>
	</dl>
</form>