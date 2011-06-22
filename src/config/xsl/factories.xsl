<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:envelope_0_11="http://agavi.org/agavi/1.0/config"
	xmlns:factories_1_0="http://agavi.org/agavi/config/parts/factories/1.0"
	xmlns:factories_1_1="http://agavi.org/agavi/config/parts/factories/1.1"
>
	<!--xmlns:factories_1_1="http://agavi.org/agavi/1.1/config/factories"-->
	
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:include href="_common.xsl" />
	
	<xsl:variable name="factories_1_0" select="'http://agavi.org/agavi/config/parts/factories/1.0'" />
	<xsl:variable name="factories_1_1" select="'http://agavi.org/agavi/config/parts/factories/1.1'" />
	
	<!-- pre-1.0 backwards compatibility for 1.0 -->
	<!-- non-"envelope" elements are copied to the 1.0 factories namespace -->
	<xsl:template match="envelope_0_11:*">
		<xsl:element name="{local-name()}" namespace="{$factories_1_0}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<!-- 1.0 backwards compatibility for 1.1 -->
	<xsl:template match="factories_1_0:*">
		<xsl:element name="{local-name()}" namespace="{$factories_1_1}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
</xsl:stylesheet>