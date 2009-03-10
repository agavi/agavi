<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:envelope_0_11="http://agavi.org/agavi/1.0/config"
	xmlns:module_1_0="http://agavi.org/agavi/config/parts/module/1.0"
>
	
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:include href="_common.xsl" />
	
	<xsl:variable name="module_1_0" select="'http://agavi.org/agavi/config/parts/module/1.0'" />
	
	<!-- pre-1.0 backwards compatibility for 1.0 -->
	<!-- non-"envelope" elements are copied to the 1.0 module namespace -->
	
	<!-- overwrite the configuration rule, as we need to work on the elements directly inside it -->
	<xsl:template match="envelope_0_11:configuration">
		<xsl:element name="{local-name()}" namespace="{$envelope_1_0}">
			<xsl:copy-of select="@*" />
			
			<module_1_0:module>
				<xsl:attribute name="enabled"><xsl:value-of select="envelope_0_11:enabled" /></xsl:attribute>
				
				<module_1_0:settings>
					<xsl:apply-templates mode="contents" />
				</module_1_0:settings>
			</module_1_0:module>
			
		</xsl:element>
	</xsl:template>
	
	<!-- we don't need those two anymore -->
	<xsl:template mode="contents" match="envelope_0_11:name" />
	<xsl:template mode="contents" match="envelope_0_11:enabled" />
	
	<!-- author elements have children which can have "email" attributes -->
	<xsl:template mode="contents" match="envelope_0_11:authors">
		<module_1_0:setting name="authors">
			<xsl:element name="parameters" namespace="{$envelope_1_0}">
				<xsl:apply-templates mode="contents" />
			</xsl:element>
		</module_1_0:setting>
	</xsl:template>
	
	<xsl:template mode="contents" match="envelope_0_11:author">
		<xsl:element name="parameter" namespace="{$envelope_1_0}">
			<xsl:if test="@email">
				<xsl:attribute name="name"><xsl:value-of select="@email" /></xsl:attribute>
			</xsl:if>
			<xsl:copy-of select="text()" />
		</xsl:element>
	</xsl:template>
	
	<xsl:template mode="contents" match="envelope_0_11:*">
		<module_1_0:setting>
			<xsl:attribute name="name"><xsl:value-of select="local-name()" /></xsl:attribute>
			<xsl:copy-of select="text()" />
		</module_1_0:setting>
	</xsl:template>
	
</xsl:stylesheet>