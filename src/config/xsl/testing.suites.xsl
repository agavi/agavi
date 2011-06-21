<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:testing-suites_1_0="http://agavi.org/agavi/config/parts/testing/suites/1.0"
	xmlns:testing-suites_1_1="http://agavi.org/agavi/config/parts/testing/suites/1.1"
>
	
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:include href="_common.xsl" />
	
	<xsl:variable name="testing-suites_1_0" select="'http://agavi.org/agavi/config/parts/testing/suites/1.0'" />
	<xsl:variable name="testing-suites_1_1" select="'http://agavi.org/agavi/config/parts/testing/suites/1.1'" />
	
	<!-- 1.0 backwards compatibility for 1.1 -->
	<xsl:template match="testing-suites_1_0:*">
		<xsl:element name="{local-name()}" namespace="{$testing-suites_1_1}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
</xsl:stylesheet>