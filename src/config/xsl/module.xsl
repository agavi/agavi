<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:envelope-0.11="http://agavi.org/agavi/1.0/config"
	xmlns:envelope-1.0="http://agavi.org/agavi/config/global/envelope/1.0"
	xmlns="http://agavi.org/agavi/config/parts/module/1.0"
>
	
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:include href="_common.xsl" />
	
	<xsl:variable name="module-1.0" select="'http://agavi.org/agavi/config/parts/module/1.0'" />
	<xsl:variable name="envelope-1.0" select="'http://agavi.org/agavi/config/global/envelope/1.0'" />
	
	<!-- pre-1.0 backwards compatibility for 1.0 -->
	<!-- non-"envelope" elements are copied to the 1.0 factories namespace -->
	
	<!-- overwrite the configuration rule, as we need to work on the elements directly inside it -->
	<xsl:template match="envelope-0.11:configuration">
		<xsl:element name="{local-name()}" namespace="{$envelope-1.0}">
			<xsl:copy-of select="@*" />
			
			<module>
				<xsl:attribute name="enabled"><xsl:value-of select="envelope-0.11:enabled" /></xsl:attribute>
				
				<settings>
					<xsl:apply-templates mode="contents" />
				</settings>
			</module>
			
		</xsl:element>
	</xsl:template>
	
	<!-- we don't need those two anymore -->
	<xsl:template mode="contents" match="envelope-0.11:name" />
	<xsl:template mode="contents" match="envelope-0.11:enabled" />
	
	<!-- author elements have children which can have "email" attributes -->
	<xsl:template mode="contents" match="envelope-0.11:authors">
		<setting name="authors">
			<xsl:element name="parameters" namespace="{$envelope-1.0}">
				<xsl:apply-templates mode="contents" />
			</xsl:element>
		</setting>
	</xsl:template>
	<xsl:template mode="contents" match="envelope-0.11:author">
		<xsl:element name="parameter" namespace="{$envelope-1.0}">
			<xsl:if test="@email">
				<xsl:attribute name="name"><xsl:value-of select="@email" /></xsl:attribute>
			</xsl:if>
			<xsl:copy-of select="text()" />
		</xsl:element>
	</xsl:template>
	
	<xsl:template mode="contents" match="envelope-0.11:*">
		<setting>
			<xsl:attribute name="name"><xsl:value-of select="local-name()" /></xsl:attribute>
			<xsl:copy-of select="text()" />
		</setting>
	</xsl:template>
	
</xsl:stylesheet>