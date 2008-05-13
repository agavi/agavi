<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:agavi10="http://agavi.org/agavi/1.0/config"
>
	
	<xsl:template match="agavi10:configurations | agavi10:configuration | agavi10:sandbox | agavi10:parameters | agavi10:parameter">
		<xsl:element name="{local-name()}" namespace="{namespace-uri()}">
			<xsl:copy-of select="@*" />
			<xsl:apply-templates />
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="*">
		<xsl:copy-of select="." />
	</xsl:template>
	
</xsl:stylesheet>