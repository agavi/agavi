<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:envelope_0_11="http://agavi.org/agavi/1.0/config"
	xmlns:routing_1_0="http://agavi.org/agavi/config/parts/routing/1.0"
>
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:include href="_common.xsl" />
	
	<xsl:variable name="routing_1_0" select="'http://agavi.org/agavi/config/parts/routing/1.0'" />
	
	<!-- pre-1.0 backwards compatibility for 1.0 -->
	<!-- non-"envelope" elements are copied to the 1.0 routing namespace -->
	<xsl:template match="envelope_0_11:*">
		<xsl:element name="{local-name()}" namespace="{$routing_1_0}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="envelope_0_11:route[@callback]">
		<xsl:element name="{local-name()}" namespace="{$routing_1_0}">
			<xsl:copy-of select="@*[local-name() != 'callback']" />
			<routing_1_0:callbacks>
				<routing_1_0:callback class="{@callback}" />
			</routing_1_0:callbacks>
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
</xsl:stylesheet>