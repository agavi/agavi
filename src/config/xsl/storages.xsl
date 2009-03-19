<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:storages_1_1="http://agavi.org/agavi/config/parts/storages/1.1"
>
	
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:include href="_common.xsl" />
	
	<xsl:variable name="storages_1_1" select="'http://agavi.org/agavi/config/parts/storages/1.1'" />
	
	<!-- since this is brand new in Agavi 1.1, we don't have to do anything here; _common will copy everything over for us -->
	
</xsl:stylesheet>