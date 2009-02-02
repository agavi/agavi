<div id="footer">
	<p>
<?php
$availableLocales = $tm->getAvailableLocales();
echo $tm->__('Alternative language:', 'Alternative languages:', count($availableLocales), 'default.layout');
foreach($availableLocales as $availableLocale): ?>
		<a href="<?php echo $ro->gen(null, array('locale' => $availableLocale['identifier'])); ?>" hreflang="<?php echo $availableLocale['identifier']; ?>"<?php if($availableLocale['identifier'] == $tm->getCurrentLocaleIdentifier()): ?> style="font-weight:bold"<?php endif;?>><?php echo htmlspecialchars($availableLocale['parameters']['description']); ?></a>
<?php endforeach; ?>
	</p>
	<p>Copyright © 2005-2009 The Agavi Project</p>
</div>
