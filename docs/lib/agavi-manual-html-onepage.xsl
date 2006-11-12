<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:doc="http://nwalsh.com/xsl/documentation/1.0"
	version="1.0"
	exclude-result-prefixes="doc">

	<!-- common parameters -->
	<xsl:import href="http://docbook.sourceforge.net/release/xsl/current/xhtml/chunk.xsl" />
	<!--xsl:import href="docs/lib/agavi-manual-html-common.xsl"/-->
	<!--xsl:import href="../docbook-xsl-1.71.1/xhtml/chunk.xsl" /-->

	<xsl:template name="href.target.uri">
	  <xsl:param name="object" select="."/>
	  <xsl:text>#</xsl:text>
	  <xsl:call-template name="object.id">
	    <xsl:with-param name="object" select="$object"/>
	  </xsl:call-template>
	</xsl:template>



</xsl:stylesheet>
