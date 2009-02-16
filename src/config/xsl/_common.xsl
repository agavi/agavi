<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:exslt-common="http://exslt.org/common"
	xmlns:saxon="http://icl.com/saxon"
	xmlns:envelope_0_11="http://agavi.org/agavi/1.0/config"
	xmlns:envelope_1_0="http://agavi.org/agavi/config/global/envelope/1.0">
	
	<xsl:variable name="envelope_0_11" select="'http://agavi.org/agavi/1.0/config'" />
	<xsl:variable name="envelope_1_0" select="'http://agavi.org/agavi/config/global/envelope/1.0'" />
	
	<!-- callable template for migrating envelope nodes -->
	<xsl:template name="_common-migrate-envelope-element">
		<!-- param for the target namespace; defaults to 1.0 -->
		<xsl:param name="namespace" select="$envelope_1_0" />
		<!-- attributes to insert, defaults to empty node set -->
		<xsl:param name="attributes" select="self::node()[false()]" />
		
		<xsl:call-template name="_common-migrate-element">
			<xsl:with-param name="namespace" select="$namespace" />
			<xsl:with-param name="attributes" select="$attributes" />
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template name="_common-migrate-element">
		<!-- param for the target namespace; no default -->
		<xsl:param name="namespace" />
		<!-- attributes to insert, defaults to empty node set -->
		<xsl:param name="attributes" select="self::node()[false()]" />
		
		<!-- create an element of the same name -->
		<xsl:element name="{local-name()}" namespace="{$namespace}">
			<!-- also copy all namespace declarations, except the one of the current element (otherwise, we'd overwrite the namespace in the <element> above if it's just xmlns etc) -->
			<xsl:copy-of select="namespace::*[not(. = namespace-uri(current()))]" />
			<xsl:copy-of select="@*" />
			<xsl:copy-of select="exslt-common:node-set($attributes)//@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="envelope_0_11:configurations">
		<xsl:call-template name="_common-migrate-envelope-element" />
	</xsl:template>
	<xsl:template match="envelope_0_11:configuration">
		<xsl:call-template name="_common-migrate-envelope-element" />
	</xsl:template>
	<xsl:template match="envelope_0_11:sandbox">
		<xsl:call-template name="_common-migrate-envelope-element" />
	</xsl:template>
	<xsl:template match="envelope_0_11:parameters">
		<xsl:call-template name="_common-migrate-envelope-element" />
	</xsl:template>
	<xsl:template match="envelope_0_11:parameter">
		<xsl:call-template name="_common-migrate-envelope-element" />
	</xsl:template>
	
	<!-- we need to apply templates to sub-elements, just in case someone wrapped a native agavi element and processed that with xsl, for example -->
	<!-- so we cannot use copy-of here -->
	<!-- node() and the copy will mean that everything is copied, even text nodes etc -->
	<xsl:template match="node()|@*">
		<xsl:copy>
			<xsl:apply-templates select="node()|@*"/>
		</xsl:copy>
	</xsl:template>
	
</xsl:stylesheet>