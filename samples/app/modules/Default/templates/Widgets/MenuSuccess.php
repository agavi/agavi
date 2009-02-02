<div id="menu">
	<h3><?php echo $tm->_('Menu', 'default.layout'); ?></h3>
	<ul>
<?php foreach($t['items'] as $url => $description): ?>
		<li><a href="<?php echo $url; ?>"><?php echo htmlspecialchars($description); ?></a></li>
<?php endforeach; ?>
	</ul>
</div>