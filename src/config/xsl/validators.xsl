<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:envelope_0_11="http://agavi.org/agavi/1.0/config"
	xmlns:validators_1_0="http://agavi.org/agavi/config/parts/validators/1.0"
>
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:include href="_common.xsl" />
	
	<xsl:variable name="validators_1_0" select="'http://agavi.org/agavi/config/parts/validators/1.0'" />
	
	<!-- pre-1.0 backwards compatibility for 1.0 -->
	<!-- non-"envelope" elements are copied to the 1.0 validators namespace -->
	<xsl:template match="envelope_0_11:*">
		<xsl:element name="{local-name()}" namespace="{$validators_1_0}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<!-- add a "parent" attribute pointing to our default validators.xml for 0.11 configs that lack it -->
	<!-- we moved the default mappings ("string" => AgaviStringValidator with "min"=1 etc) from the config handler to that file -->
	<xsl:template match="envelope_0_11:configurations[not(@parent)]">
		<xsl:call-template name="_common-migrate-envelope-element">
			<xsl:with-param name="attributes"><dummy parent="%core.agavi_dir%/config/defaults/validators.xml" /></xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	
</xsl:stylesheet>
