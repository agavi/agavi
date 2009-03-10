<?php

header('Content-Type: text/html; charset=utf-8');

if(!isset($_POST['step'])) {
	$_POST['data'] = array();
	$_POST['step'] = -1;
	// it's just dummy data for the first step, which always goes to /, no matter if rewrites are on or off
	// do not use REQUEST_URI
	$_POST['mode'] = 'rewrite';
	$_POST['prefix'] = dirname($_SERVER['PHP_SELF']);
	if($_POST['prefix'] == '/') {
		$_POST['prefix'] = '';
	}
} elseif($_POST['step'] == 0) {
	if($_POST['mode'] == 'rewrite') {
		$_POST['prefix'] = dirname($_SERVER['PHP_SELF']);
		if($_POST['prefix'] == '/') {
			$_POST['prefix'] = '';
		}
	} else {
		$_POST['prefix'] = $_SERVER['PHP_SELF'];
	}
}
$_POST['base'] = dirname($_SERVER['PHP_SELF']);
if($_POST['base'] != '/') {
	$_POST['base'] .= '/';
}

$steps = array(
	array(
		'description' => 'initial automatic call',
		'uri' => '/',
		'input' => '/',
	),
	array(
		'description' => 'empty call',
		'uri' => $_POST['mode'] == 'rewrite' ? '/' : '',
		'input' => '/',
	),
	array(
		'description' => 'empty call with query',
		'uri' => ($_POST['mode'] == 'rewrite' ? '/' : '') . '?foobar',
		'input' => '/',
	),
	array(
		'description' => 'empty call with query arg',
		'uri' => ($_POST['mode'] == 'rewrite' ? '/' : '') . '?foo=bar',
		'input' => '/',
	),
	array(
		'description' => 'empty call with query args',
		'uri' => ($_POST['mode'] == 'rewrite' ? '/' : '') . '?foo=bar&bar=baz',
		'input' => '/',
	),
	array(
		'description' => 'empty call with query args and trailing ampersand',
		'uri' => ($_POST['mode'] == 'rewrite' ? '/' : '') . '?foo=bar&bar=baz&',
		'input' => '/',
	),
	array(
		'description' => 'empty call with query args and trailing ampersands',
		'uri' => ($_POST['mode'] == 'rewrite' ? '/' : '') . '?foo=bar&bar=baz&&',
		'input' => '/',
	),
	array(
		'description' => 'empty call with empty query',
		'uri' => ($_POST['mode'] == 'rewrite' ? '/' : '') . '?',
		'input' => '/',
	),
	array(
		'description' => 'slash call',
		'uri' => '/',
		'input' => '/',
	),
	array(
		'description' => 'slash call with query',
		'uri' => '/?foobar',
		'input' => '/',
	),
	array(
		'description' => 'slash call with query arg',
		'uri' => '/?foo=bar',
		'input' => '/',
	),
	array(
		'description' => 'slash call with query args',
		'uri' => '/?foo=bar&bar=baz',
		'input' => '/',
	),
	array(
		'description' => 'slash call with empty query',
		'uri' => '/?',
		'input' => '/',
	),
	array(
		'description' => 'normal path call',
		'uri' => '/foobar',
		'input' => '/foobar',
	),
	array(
		'description' => 'normal path call with slash',
		'uri' => '/foo/bar',
		'input' => '/foo/bar',
	),
	array(
		'description' => 'normal path call with query',
		'uri' => '/foobar?foobar',
		'input' => '/foobar',
	),
	array(
		'description' => 'normal path call with query arg',
		'uri' => '/foobar?foo=bar',
		'input' => '/foobar',
	),
	array(
		'description' => 'normal path call with query args',
		'uri' => '/foobar?foo=bar&bar=baz',
		'input' => '/foobar',
	),
	array(
		'description' => 'normal path call with empty query',
		'uri' => '/foobar?',
		'input' => '/',
	),
	array(
		'description' => 'path call with spaces',
		'uri' => '/foo bar',
		'input' => '/foo bar',
	),
	array(
		'description' => 'path call with spaces in query',
		'uri' => '/foobar?foo%20bar',
		'input' => '/foobar',
	),
	array(
		'description' => 'path call with spaces and spaces in query',
		'uri' => '/foo%20bar?foo%20bar',
		'input' => '/foo bar',
	),
	array(
		'description' => 'path call with spaces and plus and spaces in query',
		'uri' => '/foo%20bar+baz?foo%20bar+baz',
		'input' => '/foo bar+baz',
	),
	array(
		'description' => 'path call with double slashes',
		'uri' => '/foo//bar',
		'input' => '/foo//bar',
	),
	array(
		'description' => 'path call with double slashes in query',
		'uri' => '/foobar?foo//bar',
		'input' => '/foobar',
	),
	array(
		'description' => 'path call with double slashes and double slashes in query',
		'uri' => '/foo//bar?foo//bar',
		'input' => '/foo//bar',
	),
	array(
		'description' => 'path call with ampersand',
		'uri' => '/foo&bar',
		'input' => '/foo&bar',
	),
	array(
		'description' => 'path call with ampersand and query',
		'uri' => '/foo&bar?foobar',
		'input' => '/foo&bar',
	),
	array(
		'description' => 'path call with trailing ampersand',
		'uri' => '/foo&bar&',
		'input' => '/foo&bar&',
	),
	array(
		'description' => 'path call with trailing ampersands',
		'uri' => '/foo&bar&&',
		'input' => '/foo&bar&&',
	),
	array(
		'description' => 'path call with trailing ampersand and query',
		'uri' => '/foo&bar&?foobar',
		'input' => '/foo&bar&',
	),
	array(
		'description' => 'path call with trailing ampersands and query',
		'uri' => '/foo&bar&&?foobar',
		'input' => '/foo&bar&&',
	),
	array(
		'description' => 'path call with query with question mark',
		'uri' => '/foobar?foo?bar',
		'input' => '/foobar',
	),
	array(
		'description' => 'path call with ampersand and query with question mark',
		'uri' => '/foo&bar?foo?bar',
		'input' => '/foo&bar',
	),
	array(
		'description' => 'last dummy step',
		'uri' => '/',
	)
);

if($_POST['step'] == count($steps)-1) {
	// we're done
	// let's assemble the output file and send it to the browser!
	header('Content-Type: application/x-httpd-php');
	header('Content-Disposition: attachment; filename=agavi-routing-testcase-' . $_POST['mode'] . '-' . gmdate('Ymd') . '.php');
	$data = array();
	foreach($steps as $step => $info) {
		if(!isset($_POST['data'][$step])) {
			continue;
		}
		$info['prefix'] = $_POST['prefix'];
		$info['base_path'] = $_POST['base'];
		$data[] = array(
			'info' => $info,
			'data' => unserialize($_POST['data'][$step]),
		);
	}
	echo "<?php\nreturn " . var_export($data, true) . ";\n?>";
	die();
}

?>
<html>
<head>
<title>Agavi Routing Test<?php if($_POST['step'] > 0): ?>, step <?php echo $_POST['step']; ?> of <?php echo (count($steps)-2); endif; ?></title>
</head>
<body>
<form id="zeform" action="<?php echo htmlspecialchars($_POST['prefix'] . $steps[$_POST['step']+1]['uri']); ?>" method="post">
<input type="hidden" name="prefix" value="<?php echo htmlspecialchars($_POST['prefix']); ?>" />
<input type="hidden" name="step" value="<?php echo $_POST['step']+1; ?>" />
<?php
foreach($_POST['data'] as $step => $data):
?>
<input type="hidden" name="data[<?php echo $step; ?>]" value="<?php echo htmlspecialchars($data); ?>" />
<?php
endforeach;
?>
<input type="hidden" name="data[<?php echo $_POST['step']; ?>]" value="<?php echo htmlspecialchars(serialize(array('_ENV' => $_ENV, '_GET' => $_GET, '_SERVER' => $_SERVER))) ; ?>" />
<?php
if($_POST['step'] > -1):
?>
<input type="hidden" name="mode" value="<?php echo $_POST['mode']; ?>" />
<script type="text/javascript">
document.getElementById('zeform').submit();
</script>
<?php
else:
?>
<input type="submit" name="mode" value="rewrite" />
<input type="submit" name="mode" value="norewrite" />
<?php
endif;
?>
</form>
</body>
</html>