<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:agavi="http://agavi.org/agavi/1.0/config" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">
<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:template match="/agavi:configurations/agavi:configuration[agavi:route/wsdl:*]">
	<!-- TODO :
		Soapaction festlegen
		Soapaddress festlegen
	-->
		<wsdl:definitions name="RoutingWsdl"
			xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
			xmlns:xsd="http://www.w3.org/2001/XMLSchema"
			xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
			xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
			xmlns="http://schemas.xmlsoap.org/wsdl/"
		>
			<xsl:attribute name="targetNamespace">http://slCore.sevenload.com/Routing</xsl:attribute>
			<xsl:attribute name="xmlns:tns">http://slCore.sevenload.com/Routing</xsl:attribute>
			<!-- Typedefintions -->
			<!--  <xi:include xmlns:xi="http://www.w3.org/2001/XInclude" href="sl_core.wsdl"/>-->
			<!-- Porttypedefinition -->
			<wsdl:portType name="RoutingPortType">	
				<xsl:for-each select="/agavi:route[wsdl:*]">
					<wsdl:operation>
						<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
						<input>
							<xsl:attribute name="message">tns:<xsl:value-of select="@name"/>Request</xsl:attribute>
						</input>
						<output>
							<xsl:attribute name="message">tns:<xsl:value-of select="@name"/>Response</xsl:attribute>
						</output>
					</wsdl:operation>
				</xsl:for-each>
			</wsdl:portType>
			
			<!-- Binding -->
			<binding name="binding" type="tns:RoutingPortType">
				<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
				<xsl:for-each select="/agavi:route[wsdl:*]">
					<wsdl:operation>
						<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
						<!-- TODO Handler festlegen -->
						<soap:operation soapAction="http://soaptest.lc/SoapHandler"/>
						<input>
							<soap:body use="literal"/>
						</input>
						<output>
							<soap:body use="literal"/>
						</output>
					</wsdl:operation>
				</xsl:for-each>
			</binding>
			
			<!-- Service -->
			<service name="MyVeryOwnSoapService">
				<port name="MyVeryOwnSoapServicePort" binding="tns:binding">
					<!-- TODO Soaphandler festlegen -->
					<soap:address location="http://soaptest.lc/SoapHandler"/>
				</port>
			</service>
			<!-- Messagedefinitionen -->
			
			<xsl:for-each select="/agavi:route[wsdl:*]">
				<wsdl:message>
					<xsl:attribute name="name"><xsl:value-of select="@name"/>Request</xsl:attribute>									
					<xsl:for-each select="wsdl:input/wsdl:part">
						<wsdl:part>
							<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
							<xsl:attribute name="type"><xsl:value-of select="@type"/></xsl:attribute>
						</wsdl:part>
					</xsl:for-each>
				</wsdl:message>
				<wsdl:message>
					<xsl:attribute name="name"><xsl:value-of select="@name"/>Response</xsl:attribute>									
					<xsl:for-each select="wsdl:output/wsdl:part">
						<wsdl:part>
							<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
							<xsl:attribute name="type"><xsl:value-of select="@type"/></xsl:attribute>
						</wsdl:part>
					</xsl:for-each>
				</wsdl:message>
			</xsl:for-each>
			
			<!-- Typedefinitionen -->
			<xsl:apply-templates />			
			
		</wsdl:definitions>
	</xsl:template>
	
	<xsl:template match="//wsdl:types">
		<xsl:value-of select="."/>
	</xsl:template>
</xsl:stylesheet>