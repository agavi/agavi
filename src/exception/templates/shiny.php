<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2011 the Agavi Project.                                |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.agavi.org/LICENSE.txt                   |
// |   vi: set noexpandtab:                                                    |
// |   Local Variables:                                                        |
// |   indent-tabs-mode: t                                                     |
// |   End:                                                                    |
// +---------------------------------------------------------------------------+

/**
 * A drop dead gorgeous exception template with eye candy embedded as inline SVG
 *
 * @package    agavi
 * @subpackage exception
 *
 * @author     David Zülke <dz@bitxtender.com>
 * @copyright  Authors
 * @copyright  The Agavi Project
 *
 * @since      0.11.0
 *
 * @version    $Id$
 */

// we're not supposed to display errors
// let's throw the exception so it shows up in error logs
if(!ini_get('display_errors')) {
	throw $e;
}

$svg = false;
$ua = '';
if(isset($_SERVER['HTTP_USER_AGENT'])) {
	$ua = $_SERVER['HTTP_USER_AGENT'];
} elseif($container !== null && ($rd = $container->getRequestData()) !== null && $rd instanceof AgaviIHeadersRequestDataHolder && $rd->hasHeader('User-Agent')) {
	$ua = $rd->getHeader('User-Agent');
} elseif($context !== null && ($rq = $context->getRequest()) !== null && !$rq->isLocked() && ($rd = $rq->getRequestData()) !== null && $rd instanceof AgaviIHeadersRequestDataHolder) {
	$ua = $rd->getHeader('User-Agent');
}
if(strpos($ua, 'AppleWebKit') !== false) {
	if(preg_match('#AppleWebKit/(\d+)#', $ua, $matches)) {
		if((int)$matches[1] >= 420) {
			$svg = true;
		}
	}
} elseif(strpos($ua, 'Gecko') !== false) {
	if(preg_match('#rv:([0-9\.]+)#', $ua, $matches)) {
		if(version_compare($matches[1], '1.8', '>=')) {
			$svg = true;
		}
	}
}

header('HTTP/1.0 500 Internal Server Error');
if($svg) {
	header('Content-Type: application/xhtml+xml; charset=utf-8');
	echo '<?xml version="1.0" encoding="utf-8" standalone="no" ?>';
} else {
	header('Content-Type: text/html; charset=utf-8');
}

?>
<!--
<?php ob_start(); include('plaintext.php'); $plaintext = ob_get_contents(); ob_end_clean(); echo str_replace('--', '~~', $plaintext); /* or else unclosed comments break XHTML */ ?>
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"<?php if($svg): ?> xmlns:svg="http://www.w3.org/2000/svg"<?php endif; ?>>
	<head>
		<meta http-equiv="Content-Type" content="<?php if($svg): ?>application/xhtml+xml<?php else: ?>text/html<?php endif; ?>; charset=utf-8" />
		<title>Application Error</title>
		<meta http-equiv="Content-Language" content="en" />
		<meta name="robots" content="none" />
		<style type="text/css">
			html {
				background-color:		#EEE;
			}

			body {
				margin:							5em;
				padding:						2em;
				border:							1px solid #DDD;
				-moz-border-radius:	0.2em;
				-webkit-border-radius: 0.2em;
				background-color:		#FFF;
				font-family:				Verdana, Arial, sans-serif;
				line-height:				1.5em;
				font-size:					10pt;
			}

			h1 {
				margin:							0 0 1.5em 0;
			}

			h2 {
				margin:							1em 0;
			}

			h2 a {
				color:							#000;
				text-decoration:		none;
			}

			h3 {
				margin:							1em 0 0 0;
			}

			div.nice {
				margin:							2em 0 2em 1em;
				padding-left:				3.5em !important;
			}

			div.box {
				font-weight:				bold;
				padding:						0.5em;
				-moz-border-radius:	0.2em;
				-webkit-border-radius: 0.2em;
				border:							1px solid #CCC;
				background-color:		#FCFCFC;
				position:						relative;
			}

			div.error {
				border:							1px solid #F22;
				background-color:		#FCC;
			}

			div.message {
				border:							1px solid #FB2;
				background-color:		#FFC;
			}

			div.help {
				border:							1px solid #66D;
				background-color:		#F0F0FF;
			}

			ol {
				font-size:					8pt;
				line-height:				1.5em;
			}

			li a.toggle:after {
				content:						' ▾';
			}

			li.closed a.toggle:after {
				content:						' ▸';
			}

			li.closed ol {
				display:						none;
			}

			ol li {
				margin:							0 0 1em 0;
			}

			ol ol li {
				margin:							auto;
			}

			dl {
				margin-top:					0;
			}

			section h2 a:before {
				content:						'▾ ';
				display:						inline-block;
				width:							1em;
			}

			section.closed h2 a:before {
				content:						'▸ ';
				display:						inline-block;
				width:							1em;
			}

			section div.container {
				padding-left:				1em;
			}

			section.closed div.container {
				display:						none;
			}

			ol ol {
				border:							1px solid #DDD;
				-moz-border-radius:	0.2em;
				-webkit-border-radius: 0.2em;
				font-family:				monospace;
				font-size:					10pt;
				line-height:				1em;
				min-height:					7em;
				padding-left:				auto;
				padding-top:				0.5em;
				padding-right:			0.5em;
				padding-bottom:			0.5em;
			}

			li.highlight code {
				background-color:		#EEE;
			}

			#svgDefinitions {
				width:							0;
				height:							0;
				overflow:						hidden;
			}

			abbr {
				border-bottom:			1px dotted #000;
				cursor:							help;
			}

			code {
				display:						block;
				margin:							0;
				padding:						0;
			}
<?php if($svg): ?>

			ol ol li {
				padding-left:				1em;
			}
<?php endif; ?>
		</style>
	</head>
	<body>
		<div id="svgDefinitions">
<?php if($svg): ?>
			<svg:svg id="exceptionSignContainer" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="48px" height="48px" viewBox="1 0 46 46">
				<svg:defs>
					<svg:linearGradient id="linearGradient3957">
						<svg:stop style="stop-color:#fffeff;stop-opacity:0.33333334;" offset="0" id="stop3959"/>
						<svg:stop style="stop-color:#fffeff;stop-opacity:0.21568628;" offset="1" id="stop3961"/>
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient2536">
						<svg:stop style="stop-color:#a40000;stop-opacity:1;" offset="0" id="stop2538"/>
						<svg:stop style="stop-color:#ff1717;stop-opacity:1;" offset="1" id="stop2540"/>
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient2479">
						<svg:stop style="stop-color:#ffe69b;stop-opacity:1;" offset="0" id="stop2481"/>
						<svg:stop style="stop-color:#ffffff;stop-opacity:1;" offset="1" id="stop2483"/>
					</svg:linearGradient>
					<svg:linearGradient xlink:href="#linearGradient2536" id="linearGradient3978" gradientUnits="userSpaceOnUse" x1="36.917976" y1="66.288063" x2="19.071495" y2="5.5410109"/>
					<svg:linearGradient xlink:href="#linearGradient2479" id="linearGradient3980" gradientUnits="userSpaceOnUse" x1="43.93581" y1="53.835983" x2="20.064686" y2="-8.5626707"/>
					<svg:linearGradient xlink:href="#linearGradient3957" id="linearGradient3982" gradientUnits="userSpaceOnUse" x1="21.993773" y1="33.955299" x2="20.917078" y2="15.814602"/>
				</svg:defs>
				<svg:g id="exceptionSign">
					<svg:g>
						<svg:path transform="matrix(0.920488,0,0,0.920488,2.368532,0.97408)" d="M 46.857143 23.928572 A 23.357143 23.357143 0 1 1  0.1428566,23.928572 A 23.357143 23.357143 0 1 1  46.857143 23.928572 z" id="path1314" style="fill:url(#linearGradient3978);fill-opacity:1;fill-rule:nonzero;stroke:#b20000;stroke-width:1.08638;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"/>
						<svg:path transform="matrix(0.856093,0,0,0.856093,1.818275,0.197769)" d="M 49.901535 26.635273 A 23.991123 23.991123 0 1 1  1.9192886,26.635273 A 23.991123 23.991123 0 1 1  49.901535 26.635273 z" id="path3560" style="opacity:0.34659089;fill:#cc0000;fill-opacity:0;stroke:url(#linearGradient3980);stroke-width:1.16809607;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"/>
					</svg:g>
					<svg:g>
						<svg:rect style="fill:#efefef;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:0.73876643;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:0.8627451" id="rect2070" width="27.836435" height="7.1735945" x="10.078821" y="19.164932" transform="matrix(1.005876,0,0,1.115201,-0.138045,-2.372708)"/>
					</svg:g>
					<svg:g>
						<svg:path transform="matrix(1.002994,0,0,1.002994,-7.185874e-2,1.968356e-2)" id="path3955" d="M 43.370686,21.715486 C 43.370686,32.546102 33.016357,15.449178 24.695948,22.101874 C 16.569626,28.599385 4.0989837,34.292422 4.0989837,23.461806 C 4.0989837,12.377753 12.79438,2.0948032 23.625,2.0948032 C 34.455619,2.0948032 43.370686,10.884868 43.370686,21.715486 z " style="fill:url(#linearGradient3982);fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:1;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dashoffset:0;stroke-opacity:1"/>
					</svg:g>
				</svg:g>
			</svg:svg>
			<svg:svg id="importantSignContainer" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="48px" height="48px" viewBox="3 0 43 43">
				<svg:g id="importantSign">
					<svg:path style="opacity:1.0000000;fill:#f57900;fill-opacity:1.0000000;fill-rule:nonzero;stroke:#914900;stroke-width:0.98214942;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.0000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000" id="path1650" d="M 46.138718 23.428040 A 22.008699 21.213203 0 1 1  2.1213188,23.428040 A 22.008699 21.213203 0 1 1  46.138718 23.428040 z" transform="matrix(0.944630,0.000000,0.000000,0.980053,1.504174,-1.556912)"/>
					<svg:path style="opacity:1.0000000;fill:none;fill-opacity:1.0000000;fill-rule:nonzero;stroke:#fcaf3e;stroke-width:0.98214942;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.0000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000" id="path3392" d="M 46.138718 23.428040 A 22.008699 21.213203 0 1 1  2.1213188,23.428040 A 22.008699 21.213203 0 1 1  46.138718 23.428040 z" transform="matrix(0.914086,0.000000,0.000000,0.948364,2.380576,-0.905815)"/>
					<svg:path style="fill:#ffffff;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:4.1224999;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.0000000;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000" d="M 21.464926,10.373268 C 21.336952,10.373268 21.230316,10.547762 21.230316,10.757175 L 22.295085,25.197999 C 22.295085,25.407412 22.401721,25.581906 22.529695,25.581907 C 22.529695,25.581907 23.370516,25.593810 24.063684,25.581907 C 24.292022,25.577986 24.361898,25.602219 24.568998,25.581907 C 25.262166,25.593810 26.102987,25.581907 26.102987,25.581907 C 26.230961,25.581907 26.337597,25.407412 26.337597,25.197999 L 27.402366,10.757175 C 27.402366,10.547762 27.295730,10.402799 27.167755,10.402799 L 24.587044,10.402799 C 24.577532,10.400862 24.578842,10.373268 24.568998,10.373268 L 21.464926,10.373268 z " id="rect1872"/>
					<svg:path style="opacity:1.0000000;fill:#ffffff;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:4.1224999;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.0000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000" id="path2062" d="M -11.875000 34.062500 A 4.5625000 3.8125000 0 1 1  -21.000000,34.062500 A 4.5625000 3.8125000 0 1 1  -11.875000 34.062500 z" transform="matrix(0.504864,0.000000,0.000000,0.604182,32.65935,9.608845)"/>
					<svg:path style="fill:#fffeff;fill-opacity:0.21390374;fill-rule:nonzero;stroke:none;stroke-width:1.0000000;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.0000000;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000" d="M 43.676426,20.476780 C 43.676426,31.307396 37.624257,16.170581 25.001688,20.863168 C 12.279172,25.592912 4.4350535,31.307396 4.4350535,20.476780 C 4.4350535,9.6461627 13.225120,0.85609769 24.055740,0.85609769 C 34.886359,0.85609769 43.676426,9.6461627 43.676426,20.476780 z " id="path3068"/>
				</svg:g>
			</svg:svg>
			<svg:svg id="warningSignContainer" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="48px" height="48px" viewBox="3 3 42 42">
				<svg:defs id="defs1379">
					<svg:linearGradient y2="56.0523" x2="47.3197" y1="11.1133" x1="4.1914" gradientUnits="userSpaceOnUse" id="aigrd1">
						<svg:stop id="stop6490" style="stop-color:#D4D4D4" offset="0"/>
						<svg:stop id="stop6492" style="stop-color:#E2E2E2" offset="0.3982"/>
						<svg:stop id="stop6494" style="stop-color:#FFFFFF" offset="1"/>
					</svg:linearGradient>
					<svg:linearGradient y2="56.0523" x2="47.3197" y1="11.1133" x1="4.1914" gradientUnits="userSpaceOnUse" id="linearGradient7451" xlink:href="#aigrd1"/>
					<svg:linearGradient id="linearGradient4126">
						<svg:stop id="stop4128" offset="0" style="stop-color:#000000;stop-opacity:1;"/>
						<svg:stop id="stop4130" offset="1" style="stop-color:#000000;stop-opacity:0;"/>
					</svg:linearGradient>
					<svg:radialGradient r="17.142857" fy="40.000000" fx="23.857143" cy="40.000000" cx="23.857143" gradientTransform="matrix(1,0,0,0.5,2.139286e-14,20)" gradientUnits="userSpaceOnUse" id="radialGradient7449" xlink:href="#linearGradient4126"/>
					<svg:linearGradient xlink:href="#linearGradient6525" id="linearGradient5250" x1="8.5469341" y1="30.281681" x2="30.85088" y2="48.301884" gradientUnits="userSpaceOnUse" gradientTransform="matrix(0.899009,0,0,0.934235,1.875108,1.193645)"/>
					<svg:linearGradient xlink:href="#aigrd1" id="linearGradient3922" gradientUnits="userSpaceOnUse" x1="4.1914" y1="11.1133" x2="47.3197" y2="56.0523"/>
					<svg:linearGradient xlink:href="#linearGradient6525" id="linearGradient3924" gradientUnits="userSpaceOnUse" gradientTransform="matrix(0.899009,0,0,0.934235,1.875108,1.193645)" x1="8.5469341" y1="30.281681" x2="30.85088" y2="48.301884"/>
					<svg:linearGradient xlink:href="#linearGradient6525" id="linearGradient3933" gradientUnits="userSpaceOnUse" gradientTransform="matrix(0.899009,0,0,0.934235,1.875108,1.193645)" x1="8.5469341" y1="30.281681" x2="30.85088" y2="48.301884"/>
					<svg:linearGradient xlink:href="#aigrd1" id="linearGradient3935" gradientUnits="userSpaceOnUse" x1="4.1914" y1="11.1133" x2="47.3197" y2="56.0523"/>
					<svg:linearGradient xlink:href="#aigrd1" id="linearGradient3946" gradientUnits="userSpaceOnUse" x1="4.1914" y1="11.1133" x2="47.3197" y2="56.0523"/>
					<svg:linearGradient xlink:href="#linearGradient6525" id="linearGradient3948" gradientUnits="userSpaceOnUse" gradientTransform="matrix(0.899009,0,0,0.934235,1.875108,1.193645)" x1="8.5469341" y1="30.281681" x2="30.85088" y2="48.301884"/>
				</svg:defs>
				<svg:g id="warningSign">
					<svg:g transform="matrix(1.566667,0.000000,0.000000,1.566667,-8.925566,-23.94764)">
						<svg:g transform="matrix(1,0,4.537846e-3,1,-0.138907,-1.394718e-15)">
							<svg:path transform="matrix(1,0,-8.726683e-3,1,0.328074,1.276596)" id="path6485" d="M 33.282781,38.644744 L 22.407791,18.394765 C 22.095292,17.832266 21.532792,17.519767 20.907793,17.519767 C 20.282793,17.519767 19.720294,17.894765 19.407795,18.457265 L 8.7828048,38.707245 C 8.5328048,39.207244 8.5328048,39.894744 8.8453048,40.394743 C 9.1578038,40.894743 9.6578038,41.144742 10.282804,41.144742 L 31.782782,41.144742 C 32.407781,41.144742 32.97028,40.832243 33.220281,40.332243 C 33.53278,39.832243 33.53278,39.207244 33.282781,38.644744 z " style="fill:#cc0000;fill-rule:nonzero;stroke:#9f0000;stroke-width:0.6382978;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"/>
							<svg:g transform="matrix(0.625,0,-5.534934e-3,0.634254,6.164053,15.76055)" style="fill-rule:nonzero;stroke:#000000;stroke-miterlimit:4">
								<svg:linearGradient y2="56.052299" x2="47.319698" y1="11.1133" x1="4.1914001" gradientUnits="userSpaceOnUse" id="linearGradient6525">
									<svg:stop id="stop6529" style="stop-color:#ffffff;stop-opacity:1;" offset="0"/>
									<svg:stop id="stop6531" style="stop-color:#ffffff;stop-opacity:0.34020618;" offset="1"/>
								</svg:linearGradient>
								<svg:path id="path6496" d="M 9.5,37.6 C 9.2,38.1 9.5,38.5 10,38.5 L 38.2,38.5 C 38.7,38.5 39,38.1 38.7,37.6 L 24.4,11 C 24.1,10.5 23.7,10.5 23.5,11 L 9.5,37.6 z " style="fill:url(#linearGradient3946);stroke:none"/>
							</svg:g>
							<svg:path transform="matrix(1,0,-8.726683e-3,1,0.318277,1.276596)" id="path1325" d="M 32.323106,38.183905 L 22.150271,19.265666 C 21.71698,18.45069 21.561698,18.189213 20.908406,18.189213 C 20.346525,18.189213 20.054127,18.57002 19.651305,19.339291 L 9.7489285,38.242296 C 9.1737649,39.303588 9.1128238,39.580228 9.3937644,40.047345 C 9.6747034,40.514462 10.032797,40.48902 11.356441,40.519491 L 30.974593,40.519491 C 32.206825,40.534726 32.483988,40.440837 32.70874,39.97372 C 32.989681,39.506602 32.867799,39.136 32.323106,38.183905 z " style="opacity:0.5;fill:none;fill-opacity:1;fill-rule:nonzero;stroke:url(#linearGradient3948);stroke-width:0.63829792;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"/>
						</svg:g>
						<svg:g style="fill-rule:nonzero;stroke:#000000;stroke-miterlimit:4" transform="matrix(0.555088,0,0,0.555052,7.749711,17.80196)">
							<svg:path style="stroke:none" d="M 23.9,36.5 C 22.6,36.5 21.6,35.5 21.6,34.2 C 21.6,32.8 22.5,31.9 23.9,31.9 C 25.3,31.9 26.1,32.8 26.2,34.2 C 26.2,35.5 25.3,36.5 23.9,36.5 L 23.9,36.5 z M 22.5,30.6 L 21.9,19.1 L 25.9,19.1 L 25.3,30.6 L 22.4,30.6 L 22.5,30.6 z " id="path6500"/>
						</svg:g>
					</svg:g>
				</svg:g>
			</svg:svg>
			<svg:svg id="arrowContainer" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" height="48px" width="48px">
				<svg:defs>
					<svg:linearGradient id="linearGradient1442">
						<svg:stop id="stop1444" offset="0" style="stop-color:#73d216"/>
						<svg:stop id="stop1446" offset="1.0000000" style="stop-color:#4e9a06"/>
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient8650">
						<svg:stop id="stop8652" offset="0" style="stop-color:#ffffff;stop-opacity:1;"/>
						<svg:stop id="stop8654" offset="1" style="stop-color:#ffffff;stop-opacity:0;"/>
					</svg:linearGradient>
					<svg:radialGradient xlink:href="#linearGradient1442" id="radialGradient1469" gradientUnits="userSpaceOnUse" gradientTransform="matrix(1.871885e-16,-0.843022,1.020168,2.265228e-16,0.606436,42.58614)" cx="35.292667" cy="20.494493" fx="35.292667" fy="20.494493" r="16.956199"/>
					<svg:radialGradient xlink:href="#linearGradient8650" id="radialGradient1471" gradientUnits="userSpaceOnUse" gradientTransform="matrix(3.749427e-16,-2.046729,-1.557610,-2.853404e-16,44.11559,66.93275)" cx="15.987216" cy="1.5350308" fx="15.987216" fy="1.5350308" r="17.171415"/>
				</svg:defs>
				<svg:g id="arrow">
					<svg:g transform="matrix(-1.000000,0.000000,0.000000,-1.000000,47.02856,43.99921)">
						<svg:path style="opacity:1.0000000;color:#000000;fill:url(#radialGradient1469);fill-opacity:1.0000000;fill-rule:evenodd;stroke:#3a7304;stroke-width:1.0000004;stroke-linecap:round;stroke-linejoin:round;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:10.000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1;visibility:visible;display:inline;overflow:visible" d="M 14.519136,38.500000 L 32.524165,38.496094 L 32.524165,25.504468 L 40.519531,25.496656 L 23.374809,5.4992135 L 6.5285585,25.497284 L 14.524440,25.501074 L 14.519136,38.500000 z " id="path8643"/>
						<svg:path style="opacity:0.50802141;color:#000000;fill:url(#radialGradient1471);fill-opacity:1.0000000;fill-rule:evenodd;stroke:none;stroke-width:1.0000000;stroke-linecap:round;stroke-linejoin:round;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:10.000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000;visibility:visible;display:inline;overflow:visible" d="M 39.429889,24.993467 L 32.023498,25.005186 L 32.026179,37.998023 L 16.647623,37.98887 C 17.417545,19.64788 27.370272,26.995797 32.029282,16.341991 L 39.429889,24.993467 z " id="path8645"/>
						<svg:path id="path8658" d="M 15.520704,37.496094 L 31.522109,37.500000 L 31.522109,24.507050 L 38.338920,24.491425 L 23.384644,7.0388396 L 8.6781173,24.495782 L 15.518018,24.501029 L 15.520704,37.496094 z " style="opacity:0.48128340;color:#000000;fill:none;fill-opacity:1.0000000;fill-rule:evenodd;stroke:#ffffff;stroke-width:1.0000004;stroke-linecap:butt;stroke-linejoin:miter;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:10.000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000;visibility:visible;display:inline;overflow:visible"/>
					</svg:g>
				</svg:g>
			</svg:svg>
<?php endif; ?>
		</div>
<?php if($svg): ?>
		<div style="float:right; margin:-6em -6em 0 0; width:10em; height:10em"><svg:svg viewBox="1 0 46 46" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><svg:use xlink:href="#exceptionSign" /></svg:svg></div>
<?php endif; ?>
		<h1>Application Error</h1>
<?php if(count($exceptions) > 1): ?>
		<div class="box <?php if($svg): ?> nice<?php endif; ?>">
<?php if($svg): ?>
			<div style="float:left; position:relative; margin-top:-1.75em; margin-left:-5.5em; height:5em; width:5em;"><svg:svg viewBox="4 7 39 34" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><svg:use xlink:href="#arrow" /></svg:svg></div>
<?php endif; ?>
			The <?php echo get_class($e); ?> was caused by <?php if(count($exceptions) == 2): ?>another exception<?php else: ?>other exceptions<?php endif; ?>. A full chain of exceptions is listed below.
		</div>
<?php endif; ?>
<?php foreach($exceptions as $ei => $e): ?>
		<section id="exception<?php echo $ei; ?>" class="<?php if($ei+1 != count($exceptions)): ?>closed<?php endif; ?>">
			<h2 class="exception"><a href="#exception<?php echo $ei; ?>" title="Toggle exception information" onclick="this.parentNode.parentNode.className = this.parentNode.parentNode.className == 'closed' ? '' : 'closed'; return false;"><?php echo get_class($e); ?></a></h2>
			<div class="container" id="exception<?php echo $ei; ?>container">
<?php $msg = nl2br(htmlspecialchars($e->getMessage())); ?>
<?php if($msg != ''): ?>
				<div class="box message<?php if($svg): ?> nice<?php endif; ?>">
<?php if($svg): ?>
					<div style="position:absolute; top:-1.25em; left:-2em; height:5em; width:5em;"><svg:svg viewBox="3 0 43 43" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><svg:use xlink:href="#importantSign" /></svg:svg></div>
<?php endif; ?>
					<?php echo $msg; ?>
				</div>
<?php endif; ?>
				<h3>Stack Trace</h3>
				<ol>
<?php
$i = 0;
$highlights = array();
$filepaths = array();
foreach(array(
	'core.module_dir',
	'core.template_dir',
	'core.config_dir',
	'core.cache_dir',
	'core.lib_dir',
	'core.app_dir',
	'core.agavi_dir',
) as $directive) {
	$filepaths['#^' . preg_quote(AgaviConfig::get($directive)) . '(?<=.)#'] = sprintf('<abbr title="%s">%s</abbr>', htmlspecialchars(AgaviConfig::get($directive)), $directive);
} 
foreach(AgaviException::getFixedTrace($e, isset($exceptions[$ei+1]) ? $exceptions[$ei+1] : null) as $trace):
	$i++;
	if(isset($trace['file']) && !isset($highlights[$trace['file']])) {
		$highlights[$trace['file']] = AgaviException::highlightFile($trace['file']);
	}
?>
					<li id="exception<?php echo $ei; ?>frame<?php echo $i; ?>"<?php if($i != 2): ?> class="closed"<?php endif; ?>>at <?php if($i > 1): ?><strong><?php if(isset($trace['class'])): ?><?php echo $trace['class'], htmlspecialchars($trace['type']); ?><?php endif; ?><?php echo $trace['function']; ?>(</strong><?php if(isset($trace['args'])): ?><?php echo AgaviException::buildParamList($trace['args']); ?><strong>)</strong><?php endif; ?><?php else: ?><em>exception origin</em><?php endif; ?><br />in <?php if(isset($trace['file'])): echo preg_replace(array_keys($filepaths), $filepaths, $trace['file']); ?> <a href="#frame<?php echo $i; ?>" class="toggle" title="Toggle source code snippet" onclick="this.parentNode.className = this.parentNode.className == 'closed' ? '' : 'closed'; return false;">line <?php echo $trace['line']; ?></a><ol start="<?php echo $start = $trace['line'] < 4 ? 1 : $trace['line'] - 3; ?>" style="padding-left:<?php echo strlen($start+6)*0.6+2; ?>em"><?php
$lines = array_slice($highlights[$trace['file']], $start - 1, 7, true);
foreach($lines as $key => &$line) {
	if($key + 1 == $trace['line']): ?><li class="highlight"><?php if($svg): ?><div style="float:left; width:1em; height:1em; margin-left:-1.35em; background-color:#FFF;"><svg:svg viewBox="3 3 42 42" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><svg:use xlink:href="#warningSign" /></svg:svg></div><?php endif; else: ?><li><?php endif; ?><code><?php
	echo $line;
?></code></li>
<?php } ?></ol><?php else: // no info about origin file ?><em>unknown</em><?php endif; ?></li>
<?php endforeach; ?>
				</ol>
			</div>
		</section>
<?php
endforeach;
?>
		<section>
			<h2>Version Information</h2>
			<div class="container">
				<dl>
					<dt>Agavi:</dt>
					<dd><?php echo htmlspecialchars(AgaviConfig::get('agavi.version')); ?></dd>
					<dt>PHP:</dt>
					<dd><?php echo htmlspecialchars(phpversion()); ?></dd>
					<dt>System:</dt>
					<dd><?php echo htmlspecialchars(php_uname()); ?></dd>
					<dt>Timestamp:</dt>
					<dd><?php echo gmdate(DATE_ISO8601); ?></dd>
				</dl>
			</div>
		</section>
	</body>
</html>
<!--
<?php ob_start(); include('plaintext.php'); $plaintext = ob_get_contents(); ob_end_clean(); echo str_replace('--', '~~', $plaintext); /* or else unclosed comments break XHTML */ ?>
-->
