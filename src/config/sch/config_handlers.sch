<schema xmlns="http://purl.oclc.org/dsdl/schematron">
	<title>A test schema for Agavi</title>
	<ns prefix="ae" uri="http://agavi.org/agavi/config/global/envelope/1.1" />
	<ns prefix="ch" uri="http://agavi.org/agavi/config/parts/config_handlers/1.1" />
	<pattern name="Base structure">
		<rule context="ae:configuration">
			<assert test="ch:handlers">A configuration block contains handlers.</assert>
		</rule>
	</pattern>
</schema>