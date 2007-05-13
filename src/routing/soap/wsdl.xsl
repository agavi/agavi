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
xmlns:tns="http://myapp.com/foo"
>
	
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes" />
	
	<xsl:template match="/">
	<!-- TODO :
		Soapaction festlegen
		Soapaddress festlegen
	-->
		<wsdl:definitions name="RoutingWsdl">
			<xsl:attribute name="targetNamespace"><xsl:value-of select="agavi:configurations/@xsd:targetNamespace" /></xsl:attribute>
			<xsl:attribute name="xmlns:tns"><xsl:value-of select="agavi:configurations/@xsd:targetNamespace" /></xsl:attribute>
			<!-- Typedefintions -->
			<!--  <xi:include xmlns:xi="http://www.w3.org/2001/XInclude" href="sl_core.wsdl"/>-->
			<!-- Porttypedefinition -->
			
			<xsl:apply-templates select="/agavi:configurations/agavi:configuration[.//agavi:route//wsdl:part]" />
			
		</wsdl:definitions>
	</xsl:template>
	
	<xsl:template match="agavi:route" mode="port">
		<wsdl:operation>
			<xsl:attribute name="name"><xsl:value-of select="translate(@pattern, '^$', '')" /></xsl:attribute>
			<wsdl:input>
				<xsl:attribute name="message">tns:<xsl:value-of select="translate(@pattern, '^$', '')" />Request</xsl:attribute>
			</wsdl:input>
			<wsdl:output>
				<xsl:attribute name="message">tns:<xsl:value-of select="translate(@pattern, '^$', '')" />Response</xsl:attribute>
			</wsdl:output>
		</wsdl:operation>
	</xsl:template>
	
	<xsl:template match="agavi:route" mode="binding">
		<wsdl:operation>
			<xsl:attribute name="name"><xsl:value-of select="translate(@pattern, '^$', '')" /></xsl:attribute>
			<!-- TODO Handler festlegen -->
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
			<xsl:apply-templates select="wsdl:input" />
		</wsdl:message>
		<wsdl:message>
			<xsl:attribute name="name"><xsl:value-of select="translate(@pattern, '^$', '')" />Response</xsl:attribute>
			<xsl:apply-templates select="wsdl:output" />
		</wsdl:message>
	</xsl:template>
	
	<xsl:template match="wsdl:part">
		<wsdl:part>
			<xsl:attribute name="name"><xsl:value-of select="@name" /></xsl:attribute>
			<xsl:attribute name="type"><xsl:value-of select="@type" /></xsl:attribute>
		</wsdl:part>
	</xsl:template>
	
	<xsl:template match="agavi:configuration">
		
		<!-- Port-->
		<wsdl:portType name="RoutingPortType">
			<xsl:apply-templates select=".//agavi:route" mode="port" />
		</wsdl:portType>
		
		<!-- Binding -->
		<binding name="binding" type="tns:RoutingPortType">
			<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
			<xsl:apply-templates select=".//agavi:route" mode="binding" />
		</binding>
		
		<!-- Service -->
		<service name="MyVeryOwnSoapService">
			<port name="MyVeryOwnSoapServicePort" binding="tns:binding">
				<!-- TODO Soaphandler festlegen -->
				<soap:address>
					<xsl:attribute name="location"><xsl:value-of select="//soap:address/@soap:location" /></xsl:attribute>
				</soap:address>
			</port>
		</service>
		
		<xsl:apply-templates select=".//agavi:route" mode="message" />
		
		<!-- Typedefinitionen -->
		<!-- <xsl:apply-templates /> -->
		
	</xsl:template>
</xsl:stylesheet>