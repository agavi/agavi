<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
xmlns:agavi="http://agavi.org/agavi/1.0/config"
xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
xmlns="http://schemas.xmlsoap.org/wsdl/"
>
	
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:variable name="tns" select="name(/agavi:configurations/namespace::*[.=../@targetNamespace])" />
	
	<xsl:template match="/agavi:configurations">
		<wsdl:definitions name="Dummy">
			
			<xsl:copy-of select="namespace::*"/>
			
			<!-- copy targetNamespace -->
			<xsl:copy-of select="@targetNamespace" />
			
			<!-- copy type defs -->
			<xsl:apply-templates select="wsdl:types | wsdl:message" mode="typesandmessages" />
			
			<!-- all the rest -->
			<xsl:apply-templates select="agavi:configuration[.//agavi:route//wsdl:part]" />
			
		</wsdl:definitions>
	</xsl:template>
	
	<xsl:template match="agavi:route" mode="port">
		<wsdl:operation>
			<xsl:attribute name="name"><xsl:value-of select="translate(@pattern, '^$', '')" /></xsl:attribute>
			<wsdl:input>
				<xsl:attribute name="message"><xsl:value-of select="$tns" />:<xsl:value-of select="translate(@pattern, '^$', '')" />Request</xsl:attribute>
			</wsdl:input>
			<wsdl:output>
				<xsl:attribute name="message"><xsl:value-of select="$tns" />:<xsl:value-of select="translate(@pattern, '^$', '')" />Response</xsl:attribute>
			</wsdl:output>
		</wsdl:operation>
	</xsl:template>
	
	<xsl:template match="agavi:route" mode="binding">
		<wsdl:operation>
			<xsl:attribute name="name"><xsl:value-of select="translate(@pattern, '^$', '')" /></xsl:attribute>
			<soap:operation>
				<xsl:attribute name="soapAction"><xsl:value-of select=".//soap:operation/@soap:soapAction" /></xsl:attribute>
			</soap:operation>
			<wsdl:input>
				<soap:body use="literal"/>
			</wsdl:input>
			<wsdl:output>
				<soap:body use="literal"/>
			</wsdl:output>
		</wsdl:operation>
	</xsl:template>
	
	<xsl:template match="agavi:route" mode="message">
		<wsdl:message>
			<xsl:attribute name="name"><xsl:value-of select="translate(@pattern, '^$', '')" />Request</xsl:attribute>
			<xsl:copy-of select="wsdl:input/wsdl:part" />
		</wsdl:message>
		<wsdl:message>
			<xsl:attribute name="name"><xsl:value-of select="translate(@pattern, '^$', '')" />Response</xsl:attribute>
			<xsl:copy-of select="wsdl:output/wsdl:part" />
		</wsdl:message>
	</xsl:template>
	
	<xsl:template match="agavi:configuration">
		
		<wsdl:portType name="DummyPortType">
			<xsl:apply-templates select=".//agavi:route" mode="port" />
		</wsdl:portType>
		
		<binding name="DummyBinding" type="tns:DummyPortType">
			<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
			<xsl:apply-templates select=".//agavi:route" mode="binding" />
		</binding>
		
		<service name="DummyService">
			<port name="DummyPort" binding="tns:DummyBinding">
				<soap:address>
					<xsl:attribute name="location"><xsl:value-of select=".//soap:address/@soap:location" /></xsl:attribute>
				</soap:address>
			</port>
		</service>
		
		<xsl:apply-templates select=".//agavi:route" mode="message" />
		
	</xsl:template>
	
	<xsl:template match="wsdl:types | wsdl:message" mode="typesandmessages">
		<xsl:copy>
			<xsl:copy-of select="* | @*" />
		</xsl:copy>
	</xsl:template>
	
</xsl:stylesheet>