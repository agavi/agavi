<?php header('Content-Type: text/html; charset=utf-8'); ?>
<html>
	<head>
		<title>Agavi XML-RPC Test</title>
		<meta http-equiv="Content-Type" value="text/html; charset=utf-8" />
	</head>
	<body>
		<h1>Agavi XML-RPC Test</h1>
<?php
$request = xmlrpc_encode_request('getProduct', array('id' => 123456), array('encoding' => 'utf-8', 'escaping' => 'markup'));

$url = "http://localhost/~dzuelke/Code/oss/agavi/branches/1.0/samples/pub/xmlrpc.php";

$header[] = "Content-type: text/xml; charset=utf-8";
$header[] = "Content-length: ".strlen($request);

$ch = curl_init();  
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
curl_setopt($ch, CURLOPT_HEADER, false);

$data = curl_exec($ch);
if(curl_errno($ch)) {
	$data = curl_error($ch);
} else {
	curl_close($ch);
}

?>
		<h2>XML-RPC Request</h2>
		<pre>
<?php echo htmlspecialchars($request); ?>
		</pre>
		<h2>XML-RPC Response</h2>
		<pre>
<?php echo htmlspecialchars($data); ?>
		</pre>
		<h2>Method call result</h2>
		<pre>
<?php var_dump(xmlrpc_decode($data, 'utf-8')); ?>
		</pre>
	</body>
</html>