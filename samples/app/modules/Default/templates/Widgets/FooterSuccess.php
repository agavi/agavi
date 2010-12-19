<div id="footer">
	<p>
<?php
echo $tm->__('Alternative language:', 'Alternative languages:', count($t['locales']), 'default.layout');
foreach($t['locales'] as $locale): ?>
		<a href="<?php echo $ro->gen(null, array('locale' => $locale['identifierData']['locale_str'])); ?>" hreflang="<?php echo $locale['identifierData']['locale_str']; ?>"<?php if($locale['identifier'] == $t['current_locale']): ?> style="font-weight:bold"<?php endif;?>><?php echo htmlspecialchars($locale['parameters']['description']); ?></a>
<?php endforeach; ?>
	</p>
	<p>Powered by <?php echo $t['agavi_plug']; ?></p>
	<p>Copyright © 2005-2010 The Agavi Project</p>
</div>
