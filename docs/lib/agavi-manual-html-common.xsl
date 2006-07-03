<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:doc="http://nwalsh.com/xsl/documentation/1.0"
	version="1.0"
	exclude-result-prefixes="doc">

	<!-- common parameters -->
	                
	<!-- parameter reference http://docbook.sourceforge.net/release/xsl/current/doc/html/ -->
	                
	<!-- path to DocBook-XSL stylesheet (absolute, relative to build.xml or URL) -->
	<xsl:import href="http://docbook.sourceforge.net/release/xsl/current/xhtml/chunk.xsl" />

	<!-- output path (relative to build.xml) -->
	<xsl:param name="base.dir">manual-html/</xsl:param>
	
	<!-- stylesheet -->
	<xsl:param name="html.stylesheet">agavi-manual.css</xsl:param>

	<xsl:param name="chunker.output.encoding">ISO-8859-1</xsl:param>
	<xsl:param name="id.warnings" select="0" />
	<xsl:param name="header.rule" select="0" />
	<xsl:param name="footer.rule" select="0" />

	<xsl:template name="user.footer.content">
		<div class="footer">
			<p class="copyright">&#x00A9; Agavi Project</p>
		</div>
	</xsl:template>

</xsl:stylesheet>
