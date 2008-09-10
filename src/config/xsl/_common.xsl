<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:envelope_0_11="http://agavi.org/agavi/1.0/config"
	xmlns:envelope_1_0="http://agavi.org/agavi/config/global/envelope/1.0">
	
	<xsl:variable name="envelope_0_11" select="'http://agavi.org/agavi/1.0/config'" />
	<xsl:variable name="envelope_1_0" select="'http://agavi.org/agavi/config/global/envelope/1.0'" />
	
	<xsl:template match="envelope_0_11:configurations | envelope_0_11:configuration | envelope_0_11:sandbox | envelope_0_11:parameters | envelope_0_11:parameter">
		<xsl:element name="{local-name()}" namespace="{$envelope_1_0}">
			<xsl:copy-of select="@*" />
			<!-- also copy all namespace declarations, except the one of the current element (otherwise, we'd overwrite the namespace in the <element> above if it's just xmlns etc) -->
			<xsl:copy-of select="namespace::*[not(. = namespace-uri(current()))]" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="*">
		<xsl:copy-of select="." />
	</xsl:template>
	
</xsl:stylesheet>