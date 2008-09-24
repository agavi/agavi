<?php header('Content-Type: text/html; charset=utf-8'); ?>
<html>
	<head>
		<title>Agavi SOAP Test</title>
		<meta http-equiv="Content-Type" value="text/html; charset=utf-8" />
	</head>
	<body>
		<h1>Agavi SOAP Test</h1>
<?php

if(!isset($_GET['item'])) {
	$_GET['item'] = 123456;
}

ini_set('soap.wsdl_cache_enabled', 0);

// this test.wsdl contains the URL to the service. You have to edit it to match your setup.
$client = new SoapClient('http://localhost/~dzuelke/_projects/agavi/branches/0.11/samples/pub/wsdl.php', array(
	/* so we can get last request and response */
	'trace' => true,
));

try {
	$result = $client->getItemPrice($_GET['item']);
} catch(SoapFault $e) {
	$result = $e->__toString();
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