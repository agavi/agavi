<?php header('Content-Type: text/html; charset=utf-8'); ?>
<html>
	<head>
		<title>Agavi SOAP Test</title>
		<meta http-equiv="Content-Type" value="text/html; charset=utf-8" />
	</head>
	<body>
		<h1>Agavi SOAP Test</h1>
<?php

define('USE_WSDL', true);

if(!isset($_GET['item'])) {
	$_GET['item'] = 'nonsense';
}

if(USE_WSDL) {
	
	ini_set('soap.wsdl_cache_enabled', 0);
	
	// this test.wsdl contains the URL to the service. You have to edit it to match your setup.
	$client = new SoapClient('../app/data/test.wsdl', array(
		/* so we can get last request and response */
		'trace' => true,
	));
	
	try {
		$result = $client->getItemPrice($_GET['item']);
	} catch(SoapFault $e) {
		$result = $e->__toString();
	}
	
} else {
	
	$client = new SoapClient(null, array( 
		"location" => "http://localhost/~dzuelke/_projects/agavi/branches/0.11/samples/pub/soap.php", 
		"uri"      => "getItemPrice", 
		"style"    => SOAP_RPC, 
		"use"      => SOAP_ENCODED 
	)); 

	$result = $client->__call( 
		/* SOAP Method Name */ 
		"getItemPrice", 
		/* Parameters */ 
		array( 
			new SoapParam( 
				/* Parameter Value */ 
				$_GET['item'], 
				/* Parameter Name */ 
				"name"
			)
		), 
		/* Options */ 
		array( 
			/* so we can get last request and response */
			'trace' => true,
			/* SOAP Method Namespace */ 
			"uri" => "urn:agavi-sampleapp", 
			/* SOAPAction HTTP Header for SOAP Method */ 
			"soapaction" => "urn:agavi-sampleapp#getItemPrice" 
		)
	);

}

?>
		<h2>SOAP Request</h2>
		<pre>
<?php echo htmlspecialchars($client->__getLastRequest()); ?>
		</pre>
		<h2>SOAP Response</h2>
		<pre>
<?php echo htmlspecialchars($client->__getLastResponse()); ?>
		</pre>
		<h2>Method call result for item "<?php echo htmlspecialchars($_GET['item']); ?>"</h2>
		<pre>
<?php var_dump($result); ?>
		</pre>
	</body>
</html>