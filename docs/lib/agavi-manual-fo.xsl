<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:doc="http://nwalsh.com/xsl/documentation/1.0"
	version="1.0"
	exclude-result-prefixes="doc">

	<!-- path to DocBook-XSL stylesheet (absolute, relative to build.xml or URL) -->
	<xsl:import href="http://docbook.sourceforge.net/release/xsl/current/fo/docbook.xsl" />
	
	<!-- output path (relative to build.xml) -->
	<xsl:param name="base.dir">manual-fo/</xsl:param>
	
	<xsl:param name="paper.type">A4</xsl:param>

</xsl:stylesheet>
