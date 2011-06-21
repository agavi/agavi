<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:envelope_0_11="http://agavi.org/agavi/1.0/config"
	xmlns:config_handlers_1_0="http://agavi.org/agavi/config/parts/config_handlers/1.0"
	xmlns:config_handlers_1_1="http://agavi.org/agavi/config/parts/config_handlers/1.1"
>
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:include href="_common.xsl" />
	
	<xsl:variable name="config_handlers_1_0" select="'http://agavi.org/agavi/config/parts/config_handlers/1.0'" />
	<xsl:variable name="config_handlers_1_1" select="'http://agavi.org/agavi/config/parts/config_handlers/1.1'" />
	
	<!-- pre-1.0 backwards compatibility for 1.0 -->
	<!-- non-"envelope" elements are copied to the 1.0 config_handlers namespace -->
	<xsl:template match="envelope_0_11:*">
		<xsl:element name="{local-name()}" namespace="{$config_handlers_1_0}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	<xsl:template match="envelope_0_11:handler[@validate]">
		<xsl:element name="{local-name()}" namespace="{$config_handlers_1_0}">
			<xsl:copy-of select="@*[local-name() != 'validate']" />
			<config_handlers_1_0:validation for="single" step="transformations_before">
				<xsl:value-of select="@validate" />
			</config_handlers_1_0:validation>
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<!-- Agavi 1.1 -->
	<!-- for AgaviReturnArrayConfigHandler definitions, we need to copy the necessary transformation for Agavi 1.1+ -->
	<xsl:template match="config_handlers_1_0:handler[@class = 'AgaviReturnArrayConfigHandler' and not(.//config_handlers_1_0:transformation)]">
		<xsl:element name="{local-name()}" namespace="{$config_handlers_1_1}">
			<xsl:copy-of select="@*" />
			<config_handlers_1_1:transformation>%core.agavi_dir%/config/xsl/rach.xsl</config_handlers_1_1:transformation>
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	<!-- 1.0 backwards compatibility for 1.1 -->
	<xsl:template match="config_handlers_1_0:*">
		<xsl:element name="{local-name()}" namespace="{$config_handlers_1_1}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
</xsl:stylesheet>
