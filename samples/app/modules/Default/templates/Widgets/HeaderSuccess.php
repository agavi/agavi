<div id="header">
	<p class="runin"><?php echo $tm->_d(time()); ?></p>
<?php if($us->isAuthenticated()): ?>
	<p class="runin"><?php echo $tm->_('You are logged in.', 'default.layout'); ?> <a href="<?php echo $ro->gen('logout'); ?>"><?php echo $tm->_('Log Out', 'default.layout'); ?></a></p>
<?php endif; ?>
	<h1><?php echo $tm->_('Agavi Sample Application', 'default.layout'); ?></h1>
</div>
