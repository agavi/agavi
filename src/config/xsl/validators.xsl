<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl"
	xmlns:envelope_0_11="http://agavi.org/agavi/1.0/config"
	xmlns:envelope_1_1="http://agavi.org/agavi/config/global/envelope/1.1"
	xmlns:validators_1_0="http://agavi.org/agavi/config/parts/validators/1.0"
	xmlns:validators_1_1="http://agavi.org/agavi/config/parts/validators/1.1"
>
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:include href="_common.xsl" />
	
	<xsl:variable name="validators_1_0" select="'http://agavi.org/agavi/config/parts/validators/1.0'" />
	<xsl:variable name="validators_1_1" select="'http://agavi.org/agavi/config/parts/validators/1.1'" />
	
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
	
	<!-- 1.0 backwards compatibility for 1.1 -->
	<!-- <validator> elements without translation_domain  -->
	<xsl:template match="validators_1_0:validator[not(@translation_domain) and not(envelope_1_1:parameter[@name='translation_domain'])]">
		<xsl:element name="{local-name()}" namespace="{$validators_1_1}">
			<!-- ... have their attributes copied -->
			<xsl:copy-of select="@*" />
			<!-- ... get an empty translation_domain attribute -->
			<xsl:attribute name="translation_domain"></xsl:attribute>
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<!-- <validator> elements with <arguments base="..." />... -->
	<xsl:template match="validators_1_0:validator[validators_1_0:arguments[@base]]">
		<xsl:element name="{local-name()}" namespace="{$validators_1_1}">
			<!-- ... have their attributes copied, but not "provides", or "depends" with a value starting on "["... -->
			<xsl:copy-of select="@*[not(local-name()='provides' and not(local-name()='depends' and substring(.,1,1)='['))]" />
			<!-- since those get special rules -->
			<xsl:apply-templates select="@provides | @depends[substring(.,1,1)='[']" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	<!-- rule for "provides" attribute in <validator> with <arguments base="..." /> -->
	<xsl:template match="validators_1_0:validator[validators_1_0:arguments[@base]]/@provides">
		<xsl:attribute name="provides"><xsl:value-of select="php:function('AgaviDependencyManager::populateArgumentBaseKeyRefs', concat(../validators_1_0:arguments/@base,'[',.,']'))" /></xsl:attribute>
	</xsl:template>
	<!-- rule for "depends" attribute with value starting on "[" in <validator> with <arguments base="..." /> -->
	<xsl:template match="validators_1_0:validator[validators_1_0:arguments[@base]]/@depends[substring(.,1,1)='[']">
		<xsl:attribute name="depends"><xsl:value-of select="php:function('AgaviDependencyManager::populateArgumentBaseKeyRefs', concat(../validators_1_0:arguments/@base,.))" /></xsl:attribute>
	</xsl:template>
	<!-- rule for all other elements -->
	<xsl:template match="validators_1_0:*">
		<xsl:element name="{local-name()}" namespace="{$validators_1_1}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
</xsl:stylesheet>
