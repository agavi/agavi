<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:doc="http://nwalsh.com/xsl/documentation/1.0"
	version="1.0"
	exclude-result-prefixes="doc">

	<!-- path to DocBook-XSL stylesheet (absolute, relative to build.xml or URL) -->
	<xsl:import href="http://docbook.sourceforge.net/release/xsl/current/xhtml/chunk.xsl" />
	<!--xsl:import href="../docbook-xsl-1.71.1/xhtml/chunk.xsl" /-->

	<xsl:template name="user.footer.content">
		<div class="footer">
			<p class="copyright">&#x00A9; Agavi Project</p>
		</div>
	</xsl:template>

</xsl:stylesheet>
