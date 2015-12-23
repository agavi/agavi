<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:envelope_0_11="http://agavi.org/agavi/1.0/config"
	xmlns:databases_1_0="http://agavi.org/agavi/config/parts/databases/1.0"
	xmlns:databases_1_1="http://agavi.org/agavi/config/parts/databases/1.1"
>
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:include href="_common.xsl" />
	
	<xsl:variable name="databases_1_0" select="'http://agavi.org/agavi/config/parts/databases/1.0'" />
	<xsl:variable name="databases_1_1" select="'http://agavi.org/agavi/config/parts/databases/1.1'" />
	
	<!-- pre-1.0 backwards compatibility for 1.0 -->
	<!-- non-"envelope" elements are copied to the 1.0 databases namespace -->
	<xsl:template match="envelope_0_11:*">
		<xsl:element name="{local-name()}" namespace="{$databases_1_0}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<!-- remove attribute 'default' for all <database> blocks without children -->
	<xsl:template match="databases_1_0:*[not(databases_1_0:database)]">
		<xsl:element name="{local-name()}" namespace="{$databases_1_1}">
			<xsl:copy-of select="@*[name() != 'default']" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<!-- 1.0 backwards compatibility for 1.1 -->
	<xsl:template match="databases_1_0:*">
		<xsl:element name="{local-name()}" namespace="{$databases_1_1}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
</xsl:stylesheet>
