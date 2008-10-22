<schema xmlns="http://purl.oclc.org/dsdl/schematron">
	<title>A test schema for Agavi</title>
	<ns prefix="agavi" uri="http://agavi.org/agavi/1.0/config" />
	<pattern name="Base structure">
		<rule context="agavi:configuration">
			<assert test="agavi:handlers">A configuration block contains handlers.</assert>
		</rule>
	</pattern>
</schema>