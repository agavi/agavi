<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:agavi10="http://agavi.org/agavi/1.0/config"
	xmlns:factories10="http://agavi.org/agavi/1.0/config/factories"
	xmlns:factories11="http://agavi.org/agavi/1.1/config/factories"
>

	<xsl:include href="_common.xsl" />

	<xsl:variable name="agavi10" select="'http://agavi.org/agavi/1.0/config'" />
	<xsl:variable name="factories10" select="'http://agavi.org/agavi/1.0/config/factories'" />
	<xsl:variable name="factories11" select="'http://agavi.org/agavi/1.1/config/factories'" />

	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<!-- pre-1.0 backwards compatibility for 1.0 -->
	<!-- non-"envelope" elements are copied to the 1.0 factories namespace -->
	<xsl:template match="agavi10:*">
		<xsl:element name="{local-name()}" namespace="{$factories10}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<!-- 1.0 BC for 1.1 -->
	<!-- namespace is simply changed to 1.1 for all elements except <storage> -->
	<xsl:template match="factories10:*">
		<xsl:element name="{local-name()}" namespace="{$factories11}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	<xsl:template match="agavi10:storage | factories10:storage">
		<factories11:storage_manager class="AgaviStorageManager">
			<xsl:apply-templates />
		</factories11:storage_manager>
	</xsl:template>
	
</xsl:stylesheet>