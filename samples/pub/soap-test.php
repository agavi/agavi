<?php header('Content-Type: text/html; charset=utf-8'); ?>
<html>
	<head>
		<title>Agavi SOAP Test</title>
		<meta http-equiv="Content-Type" value="text/html; charset=utf-8" />
	</head>
	<body>
		<h1>Agavi SOAP Test</h1>
<?php

ini_set('soap.wsdl_cache_enabled', 0);

// this test.wsdl contains the URL to the service. You have to edit it to match your setup.
$client = new SoapClient('http://localhost/~dzuelke/Code/oss/agavi/branches/1.0/samples/pub/products.wsdl', array(
	/* so we can get last request and response */
	'trace' => true,
));
?>
		<h2>getProduct()</h2>
<?php
try {
	if(!isset($_GET['item'])) {
		$_GET['item'] = 123456;
	}
	$result = $client->getProduct($_GET['item']);
} catch(SoapFault $e) {
	$result = $e->__toString();
}
?>
		<h3>Return Value</h3>
		<pre>
<?php var_dump($result); ?>
		</pre>
		<h3>SOAP Request</h3>
		<pre>
<?php echo htmlspecialchars($client->__getLastRequest()); ?>
		</pre>
		<h3>SOAP Response</h3>
		<pre>
<?php echo htmlspecialchars($client->__getLastResponse()); ?>
		</pre>
		<hr />
		<h2>listProducts()</h2>
<?php
try {
	$result = $client->listProducts();
} catch(SoapFault $e) {
	$result = $e->__toString();
}
?>
		<h3>Return Value</h3>
		<pre>
<?php var_dump($result); ?>
		</pre>
		<h3>SOAP Request</h3>
		<pre>
<?php echo htmlspecialchars($client->__getLastRequest()); ?>
		</pre>
		<h3>SOAP Response</h3>
		<pre>
<?php echo htmlspecialchars($client->__getLastResponse()); ?>
		</pre>
	</body>
</html>