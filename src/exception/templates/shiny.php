<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Agavi package.                                   |
// | Copyright (c) 2005-2010 the Agavi Project.                                |
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

$fixedTrace = AgaviException::getFixedTrace($e);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"<?php if($svg): ?> xmlns:svg="http://www.w3.org/2000/svg"<?php endif; ?>>
	<head>
		<meta http-equiv="Content-Type" content="<?php if($svg): ?>application/xhtml+xml<?php else: ?>text/html<?php endif; ?>; charset=utf-8" />
		<title><?php echo get_class($e); ?></title>
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
				-moz-border-radius:	0.5em;
				background-color:		#FFF;
				font-family:				Verdana, Arial, sans-serif;
				line-height:				1.5em;
				font-size:					10pt;
			}

			h1 {
				margin:							0 0 1.5em 0;
			}

			h3 {
				margin:							2em 0 0 0;
			}

			.nice {
				margin:							1.5em 0 1.5em 1em;
				padding-left:				3.5em !important;
			}

			#message {
				font-weight:				bold;
				padding:						0.5em;
				-moz-border-radius:	0.5em;
				border:							1px solid #FB2;
				background-color:		#FFC;
				position:						relative;
			}

			#help {
				font-weight:				bold;
				padding:						0.5em;
				-moz-border-radius:	0.5em;
				border:							1px solid #66D;
				background-color:		#F0F0FF;
				position:						relative;
			}

			ol {
				font-size:					8pt;
				line-height:				1.5em;
			}

			li.hidecode ol {
				display:						none;
			}

			ol li {
				margin:							0 0 1em 0;
			}

			ol ol li {
				margin:							auto;
			}

			a.toggle:before {
				content:						'« ';
			}

			.hidecode a.toggle:before {
				content:						'';
			}

			.hidecode a.toggle:after {
				content:						' »';
			}

			ol ol {
				border:							1px solid #DDD;
				-moz-border-radius:	0.5em;
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
			<svg:svg viewBox="0 0 48 48" preserveAspectRatio="xMaxYMax meet" width="0" height="0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://web.resource.org/cc/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="svg1306">
				<svg:defs id="defs1308">
					<svg:linearGradient id="linearGradient3957">
						<svg:stop style="stop-color:#fffeff;stop-opacity:0.33333334;" offset="0" id="stop3959" />
						<svg:stop style="stop-color:#fffeff;stop-opacity:0.21568628;" offset="1" id="stop3961" />
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient2536">
						<svg:stop style="stop-color:#a40000;stop-opacity:1;" offset="0" id="stop2538" />
						<svg:stop style="stop-color:#ff1717;stop-opacity:1;" offset="1" id="stop2540" />
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient2479">
						<svg:stop style="stop-color:#ffe69b;stop-opacity:1;" offset="0" id="stop2481" />
						<svg:stop style="stop-color:#ffffff;stop-opacity:1;" offset="1" id="stop2483" />
					</svg:linearGradient>
					<!-- removed drop shadow -->
					<!--
					<svg:linearGradient id="linearGradient4126">
						<svg:stop id="stop4128" offset="0" style="stop-color:#000000;stop-opacity:1;" />
						<svg:stop id="stop4130" offset="1" style="stop-color:#000000;stop-opacity:0;" />
					</svg:linearGradient>
					<svg:radialGradient xlink:href="#linearGradient4126" id="radialGradient2169" gradientUnits="userSpaceOnUse" gradientTransform="matrix(1.000000,0.000000,0.000000,0.500000,1.899196e-14,20.00000)" cx="23.857143" cy="40.000000" fx="23.857143" fy="40.000000" r="17.142857" />
					<svg:linearGradient xlink:href="#linearGradient2479" id="linearGradient2485" x1="43.93581" y1="53.835983" x2="20.064686" y2="-8.5626707" gradientUnits="userSpaceOnUse" />
					<svg:linearGradient xlink:href="#linearGradient2536" id="linearGradient2542" x1="36.917976" y1="66.288063" x2="19.071495" y2="5.5410109" gradientUnits="userSpaceOnUse" />
					<svg:linearGradient xlink:href="#linearGradient2536" id="linearGradient3046" gradientUnits="userSpaceOnUse" x1="36.917976" y1="66.288063" x2="19.071495" y2="5.5410109" />
					<svg:linearGradient xlink:href="#linearGradient2479" id="linearGradient3048" gradientUnits="userSpaceOnUse" x1="43.93581" y1="53.835983" x2="20.064686" y2="-8.5626707" />
					<svg:linearGradient xlink:href="#linearGradient2536" id="linearGradient3064" gradientUnits="userSpaceOnUse" x1="36.917976" y1="66.288063" x2="19.071495" y2="5.5410109" />
					<svg:linearGradient xlink:href="#linearGradient2479" id="linearGradient3066" gradientUnits="userSpaceOnUse" x1="43.93581" y1="53.835983" x2="20.064686" y2="-8.5626707" />
					<svg:linearGradient xlink:href="#linearGradient3957" id="linearGradient3963" x1="21.993773" y1="33.955299" x2="20.917078" y2="15.814602" gradientUnits="userSpaceOnUse" />
					<svg:radialGradient xlink:href="#linearGradient4126" id="radialGradient3976" gradientUnits="userSpaceOnUse" gradientTransform="matrix(1,0,0,0.5,1.893048e-14,20)" cx="23.857143" cy="40.000000" fx="23.857143" fy="40.000000" r="17.142857" />
					-->
					<svg:linearGradient xlink:href="#linearGradient2536" id="linearGradient3978" gradientUnits="userSpaceOnUse" x1="36.917976" y1="66.288063" x2="19.071495" y2="5.5410109" />
					<svg:linearGradient xlink:href="#linearGradient2479" id="linearGradient3980" gradientUnits="userSpaceOnUse" x1="43.93581" y1="53.835983" x2="20.064686" y2="-8.5626707" />
					<svg:linearGradient xlink:href="#linearGradient3957" id="linearGradient3982" gradientUnits="userSpaceOnUse" x1="21.993773" y1="33.955299" x2="20.917078" y2="15.814602" />
					<svg:g id="exceptionSign">
						<!-- removed drop shadow -->
						<!--
						<svg:path transform="matrix(1.070555,0,0,0.525,-0.892755,22.5)" d="M 41 40 A 17.142857 8.5714283 0 1 1  6.7142868,40 A 17.142857 8.5714283 0 1 1  41 40 z" id="path6548" style="opacity:0.6;color:#000000;fill:url(#radialGradient3976);visibility:visible;display:block;overflow:visible" />
						-->
						<svg:path transform="matrix(0.920488,0,0,0.920488,2.368532,0.97408)" d="M 46.857143 23.928572 A 23.357143 23.357143 0 1 1  0.1428566,23.928572 A 23.357143 23.357143 0 1 1  46.857143 23.928572 z" id="path1314" style="fill:url(#linearGradient3978);stroke:#b20000;stroke-width:1.08638" />
						<svg:path transform="matrix(0.856093,0,0,0.856093,1.818275,0.197769)" d="M 49.901535 26.635273 A 23.991123 23.991123 0 1 1  1.9192886,26.635273 A 23.991123 23.991123 0 1 1  49.901535 26.635273 z" id="path3560" style="opacity:0.34659089;fill:#cc0000;fill-opacity:0;stroke:url(#linearGradient3980);stroke-width:1.16809607" />
						<svg:rect style="fill:#efefef;fill-opacity:1" id="rect2070" width="27.836435" height="7.1735945" x="10.078821" y="19.164932" transform="matrix(1.005876,0,0,1.115201,-0.138045,-2.372708)" />
						<svg:path transform="matrix(1.002994,0,0,1.002994,-7.185874e-2,1.968356e-2)" id="path3955" d="M 43.370686,21.715486 C 43.370686,32.546102 33.016357,15.449178 24.695948,22.101874 C 16.569626,28.599385 4.0989837,34.292422 4.0989837,23.461806 C 4.0989837,12.377753 12.79438,2.0948032 23.625,2.0948032 C 34.455619,2.0948032 43.370686,10.884868 43.370686,21.715486 z " style="fill:url(#linearGradient3982);fill-opacity:1" />
					</svg:g>
				</svg:defs>
				<svg:metadata id="metadata1311">
					<rdf:RDF>
						<cc:Work rdf:about="">
							<dc:format>image/svg+xml</dc:format>
							<dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
							<dc:creator>
								<cc:Agent>
									<dc:title>Rodney Dawes</dc:title>
								</cc:Agent>
							</dc:creator>
							<dc:contributor>
								<cc:Agent>
									<dc:title>Jakub Steiner, Garrett LeSage</dc:title>
								</cc:Agent>
							</dc:contributor>
							<cc:license rdf:resource="http://creativecommons.org/licenses/by-sa/2.0/" />
							<dc:title>Dialog Error</dc:title>
						</cc:Work>
						<cc:License rdf:about="http://creativecommons.org/licenses/by-sa/2.0/">
							<cc:permits rdf:resource="http://web.resource.org/cc/Reproduction" />
							<cc:permits rdf:resource="http://web.resource.org/cc/Distribution" />
							<cc:requires rdf:resource="http://web.resource.org/cc/Notice" />
							<cc:requires rdf:resource="http://web.resource.org/cc/Attribution" />
							<cc:permits rdf:resource="http://web.resource.org/cc/DerivativeWorks" />
							<cc:requires rdf:resource="http://web.resource.org/cc/ShareAlike" />
						</cc:License>
					</rdf:RDF>
				</svg:metadata>
			</svg:svg>
			<svg:svg viewBox="0 0 48 48" preserveAspectRatio="xMaxYMax meet" width="0" height="0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://web.resource.org/cc/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="svg1800">
				<svg:defs>
					<!-- removed drop shadow -->
					<!--
					<svg:linearGradient id="linearGradient3101">
						<svg:stop style="stop-color:#000000;stop-opacity:1;" offset="0" id="stop3103" />
						<svg:stop style="stop-color:#000000;stop-opacity:0;" offset="1" id="stop3105" />
					</svg:linearGradient>
					<svg:radialGradient xlink:href="#linearGradient3101" id="radialGradient3107" cx="17.3125" cy="25.53125" fx="17.3125" fy="25.53125" r="9.6875" gradientTransform="matrix(1.000000,0.000000,0.000000,0.351613,1.292803e-15,16.55413)" gradientUnits="userSpaceOnUse" />
					-->
					<svg:g id="importantSign">
						<!-- removed drop shadow -->
						<!--
						<svg:path style="opacity:0.40909091;color:#000000;fill:url(#radialGradient3107);fill-opacity:1.0000000;fill-rule:nonzero;stroke:none;stroke-width:1.1053395;stroke-linecap:butt;stroke-linejoin:miter;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:4.0000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000;visibility:visible;display:inline;overflow:visible" d="M 27.000000 25.531250 A 9.6875000 3.4062500 0 1 1	 7.6250000,25.531250 A 9.6875000 3.4062500 0 1 1	27.000000 25.531250 z" transform="matrix(2.182912,0.000000,0.000000,2.182912,-13.50372,-14.35012)" />
						-->
						<svg:path style="opacity:1.0000000;fill:#f57900;fill-opacity:1.0000000;fill-rule:nonzero;stroke:#914900;stroke-width:0.98214942;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.0000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000" d="M 46.138718 23.428040 A 22.008699 21.213203 0 1 1	 2.1213188,23.428040 A 22.008699 21.213203 0 1 1	46.138718 23.428040 z" transform="matrix(0.944630,0.000000,0.000000,0.980053,1.504174,-1.556912)" />
						<svg:path style="opacity:1.0000000;fill:none;fill-opacity:1.0000000;fill-rule:nonzero;stroke:#fcaf3e;stroke-width:0.98214942;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.0000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000" d="M 46.138718 23.428040 A 22.008699 21.213203 0 1 1	2.1213188,23.428040 A 22.008699 21.213203 0 1 1	 46.138718 23.428040 z" transform="matrix(0.914086,0.000000,0.000000,0.948364,2.380576,-0.905815)" />
						<svg:path style="fill:#ffffff;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:4.1224999;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.0000000;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000" d="M 21.464926,10.373268 C 21.336952,10.373268 21.230316,10.547762 21.230316,10.757175 L 22.295085,25.197999 C 22.295085,25.407412 22.401721,25.581906 22.529695,25.581907 C 22.529695,25.581907 23.370516,25.593810 24.063684,25.581907 C 24.292022,25.577986 24.361898,25.602219 24.568998,25.581907 C 25.262166,25.593810 26.102987,25.581907 26.102987,25.581907 C 26.230961,25.581907 26.337597,25.407412 26.337597,25.197999 L 27.402366,10.757175 C 27.402366,10.547762 27.295730,10.402799 27.167755,10.402799 L 24.587044,10.402799 C 24.577532,10.400862 24.578842,10.373268 24.568998,10.373268 L 21.464926,10.373268 z " />
						<svg:path style="opacity:1.0000000;fill:#ffffff;fill-opacity:1;fill-rule:nonzero;stroke:none;stroke-width:4.1224999;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.0000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000" d="M -11.875000 34.062500 A 4.5625000 3.8125000 0 1 1	-21.000000,34.062500 A 4.5625000 3.8125000 0 1 1	-11.875000 34.062500 z" transform="matrix(0.504864,0.000000,0.000000,0.604182,32.65935,9.608845)" />
						<svg:path style="fill:#fffeff;fill-opacity:0.21390374;fill-rule:nonzero;stroke:none;stroke-width:1.0000000;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4.0000000;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000" d="M 43.676426,20.476780 C 43.676426,31.307396 37.624257,16.170581 25.001688,20.863168 C 12.279172,25.592912 4.4350535,31.307396 4.4350535,20.476780 C 4.4350535,9.6461627 13.225120,0.85609769 24.055740,0.85609769 C 34.886359,0.85609769 43.676426,9.6461627 43.676426,20.476780 z " />
					</svg:g>
				</svg:defs>
				<svg:metadata>
					<rdf:RDF>
						<cc:Work rdf:about="">
							<dc:format>image/svg+xml</dc:format>
							<dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
							<cc:license rdf:resource="http://creativecommons.org/licenses/by-sa/2.0/" />
							<dc:title>Emblem Important</dc:title>
							<dc:creator>
								<cc:Agent>
									<dc:title>Jakub Steiner</dc:title>
								</cc:Agent>
							</dc:creator>
							<dc:subject>
								<rdf:Bag>
									<rdf:li>emblem</rdf:li>
									<rdf:li>photos</rdf:li>
									<rdf:li>pictures</rdf:li>
									<rdf:li>raw</rdf:li>
									<rdf:li>jpeg</rdf:li>
								</rdf:Bag>
							</dc:subject>
						</cc:Work>
						<cc:License rdf:about="http://creativecommons.org/licenses/by-sa/2.0/">
							<cc:permits rdf:resource="http://web.resource.org/cc/Reproduction" />
							<cc:permits rdf:resource="http://web.resource.org/cc/Distribution" />
							<cc:requires rdf:resource="http://web.resource.org/cc/Notice" />
							<cc:requires rdf:resource="http://web.resource.org/cc/Attribution" />
							<cc:permits rdf:resource="http://web.resource.org/cc/DerivativeWorks" />
							<cc:requires rdf:resource="http://web.resource.org/cc/ShareAlike" />
						</cc:License>
					</rdf:RDF>
				</svg:metadata>
			</svg:svg>
			<svg:svg viewBox="0 0 48 48" preserveAspectRatio="xMaxYMax meet" width="0" height="0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://web.resource.org/cc/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="svg6361">
				<svg:defs>
					<svg:linearGradient id="linearGradient2256">
						<svg:stop style="stop-color:#ff0202;stop-opacity:1;" offset="0" id="stop2258" />
						<svg:stop style="stop-color:#ff9b9b;stop-opacity:1;" offset="1" id="stop2260" />
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient2248">
						<svg:stop style="stop-color:#ffffff;stop-opacity:0.25;" offset="0" id="stop2250" />
						<svg:stop style="stop-color:#ffffff;stop-opacity:0;" offset="1" id="stop2252" />
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient9647">
						<svg:stop style="stop-color:#ffffff;stop-opacity:1;" offset="0" id="stop9649" />
						<svg:stop style="stop-color:#dbdbdb;stop-opacity:1;" offset="1" id="stop9651" />
					</svg:linearGradient>
					<!-- removed drop shadow -->
					<!--
					<svg:linearGradient id="linearGradient21644">
						<svg:stop style="stop-color:#000000;stop-opacity:1;" offset="0" id="stop21646" />
						<svg:stop style="stop-color:#000000;stop-opacity:0;" offset="1" id="stop21648" />
					</svg:linearGradient>
					<svg:radialGradient xlink:href="#linearGradient21644" id="radialGradient21650" cx="25.125" cy="36.75" fx="25.125" fy="36.75" r="15.75" gradientTransform="matrix(1.000000,0.000000,0.000000,0.595238,-2.300678e-15,14.87500)" gradientUnits="userSpaceOnUse" />
				-->
					<svg:linearGradient id="linearGradient7895">
						<svg:stop style="stop-color:#ffffff;stop-opacity:1;" offset="0" id="stop7897" />
						<svg:stop style="stop-color:#ffffff;stop-opacity:0;" offset="1" id="stop7899" />
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient4981">
						<svg:stop style="stop-color:#cc0000;stop-opacity:1;" offset="0" id="stop4983" />
						<svg:stop style="stop-color:#b30000;stop-opacity:1.0000000;" offset="1.0000000" id="stop4985" />
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient15762">
						<svg:stop id="stop15764" offset="0" style="stop-color:#ffffff;stop-opacity:1;" />
						<svg:stop id="stop15766" offset="1" style="stop-color:#ffffff;stop-opacity:0;" />
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient14236">
						<svg:stop id="stop14238" offset="0.0000000" style="stop-color:#ed4040;stop-opacity:1.0000000;" />
						<svg:stop id="stop14240" offset="1.0000000" style="stop-color:#a40000;stop-opacity:1.0000000;" />
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient11780">
						<svg:stop style="stop-color:#ff8b8b;stop-opacity:1.0000000;" offset="0.0000000" id="stop11782" />
						<svg:stop style="stop-color:#ec1b1b;stop-opacity:1.0000000;" offset="1.0000000" id="stop11784" />
					</svg:linearGradient>
					<svg:linearGradient id="linearGradient11014">
						<svg:stop style="stop-color:#a80000;stop-opacity:1.0000000;" offset="0.0000000" id="stop11016" />
						<svg:stop style="stop-color:#c60000;stop-opacity:1.0000000;" offset="0.0000000" id="stop13245" />
						<svg:stop style="stop-color:#e50000;stop-opacity:1.0000000;" offset="1.0000000" id="stop11018" />
					</svg:linearGradient>
					<svg:linearGradient y2="9.6507530" x2="9.8940229" y1="5.3855424" x1="5.7365270" gradientTransform="matrix(-1.000000,0.000000,0.000000,-1.000000,31.72170,31.29079)" gradientUnits="userSpaceOnUse" id="linearGradient15772" xlink:href="#linearGradient15762" />
					<svg:linearGradient xlink:href="#linearGradient11780" id="linearGradient2057" x1="15.737001" y1="12.503600" x2="53.570126" y2="47.374317" gradientUnits="userSpaceOnUse" gradientTransform="translate(0.000000,-2.000000)" />
					<svg:linearGradient xlink:href="#linearGradient4981" id="linearGradient4987" x1="23.995985" y1="20.105337" x2="41.047836" y2="37.959785" gradientUnits="userSpaceOnUse" gradientTransform="translate(0.000000,-2.000000)" />
					<svg:linearGradient xlink:href="#linearGradient7895" id="linearGradient7901" x1="15.578875" y1="16.285088" x2="32.166405" y2="28.394291" gradientUnits="userSpaceOnUse" />
					<svg:radialGradient xlink:href="#linearGradient9647" id="radialGradient2239" cx="24.30225" cy="33.30225" fx="24.30225" fy="33.30225" r="12.30225" gradientUnits="userSpaceOnUse" gradientTransform="matrix(1.693981,-5.775714e-16,5.775714e-16,1.693981,-16.86529,-25.11111)" />
					<svg:linearGradient xlink:href="#linearGradient4981" id="linearGradient2243" gradientUnits="userSpaceOnUse" x1="23.995985" y1="20.105337" x2="41.047836" y2="37.959785" gradientTransform="matrix(0.988373,0.000000,0.000000,0.988373,0.279002,0.278984)" />
					<svg:radialGradient xlink:href="#linearGradient2248" id="radialGradient2254" cx="16.75" cy="10.666344" fx="16.75" fy="10.666344" r="21.25" gradientTransform="matrix(4.154957,-2.979206e-24,3.255657e-24,3.198723,-52.84553,-23.50921)" gradientUnits="userSpaceOnUse" />
					<svg:linearGradient xlink:href="#linearGradient2256" id="linearGradient2262" x1="21.75" y1="15.80225" x2="24.30225" y2="35.05225" gradientUnits="userSpaceOnUse" gradientTransform="translate(0.000000,-2.000000)" />
					<svg:g id="stopSign">
						<!-- removed drop shadow -->
						<!--
						<svg:path style="opacity:0.63068183;color:#000000;fill:url(#radialGradient21650);fill-opacity:1;fill-rule:evenodd;stroke:none;stroke-width:1;stroke-linecap:round;stroke-linejoin:round;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1;visibility:visible;display:inline;overflow:visible" id="path21642" d="M 40.875 36.75 A 15.75 9.375 0 1 1	 9.375,36.75 A 15.75 9.375 0 1 1	40.875 36.75 z" transform="matrix(1.173803,0.000000,0.000000,0.600000,-5.265866,19.57500)" />
						-->
						<svg:path style="fill:url(#linearGradient4987);fill-opacity:1;fill-rule:evenodd;stroke:#860000;stroke-width:1;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" d="M 15.591006,0.4919213 L 32.676311,0.4919213 L 45.497585,13.586385 L 45.497585,31.48003 L 32.848986,43.496929 L 15.418649,43.496929 L 2.4943857,30.658264 L 2.4943857,13.464078 L 15.591006,0.4919213 z " id="path9480" />
						<svg:path style="opacity:0.81318683;fill:none;fill-opacity:1;fill-rule:evenodd;stroke:url(#linearGradient2057);stroke-width:1.00000024;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-opacity:1" d="M 16.020655,1.5003424 L 32.248563,1.5003424 L 44.496456,13.922717 L 44.496456,31.037001 L 32.638472,42.48783 L 15.870253,42.48783 L 3.5090792,30.208718 L 3.5090792,13.84561 L 16.020655,1.5003424 z " id="path9482" />
						<svg:path style="opacity:0.28977272;fill:url(#radialGradient2254);fill-opacity:1;fill-rule:evenodd;stroke:none;stroke-width:1;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" d="M 15.6875,0.75 L 2.75,13.5625 L 2.75,30.5625 L 5.6875,33.46875 C 22.450041,33.526299 22.164665,20.450067 45.25,21.59375 L 45.25,13.6875 L 32.5625,0.75 L 15.6875,0.75 z " id="path2241" />
						<svg:path style="fill:url(#radialGradient2239);fill-opacity:1;fill-rule:evenodd;stroke:url(#linearGradient2262);stroke-width:0.99999958;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" d="M 16.767175,10.5 L 12.5,14.767175 L 20.035075,22.30225 L 12.5,29.837325 L 16.767175,34.104501 L 24.30225,26.569425 L 31.837325,34.104501 L 36.104501,29.837325 L 28.569425,22.30225 L 36.104501,14.767175 L 31.837325,10.5 L 24.30225,18.035075 L 16.767175,10.5 z " id="path2787" />
					</svg:g>
				</svg:defs>
				<svg:metadata>
					<rdf:RDF>
						<cc:Work rdf:about="">
							<dc:format>image/svg+xml</dc:format>
							<dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
							<dc:title>Stop</dc:title>
							<dc:date>2005-10-16</dc:date>
							<dc:creator>
								<cc:Agent>
									<dc:title>Andreas Nilsson</dc:title>
								</cc:Agent>
							</dc:creator>
							<dc:subject>
								<rdf:Bag>
									<rdf:li>stop</rdf:li>
									<rdf:li>halt</rdf:li>
									<rdf:li>error</rdf:li>
								</rdf:Bag>
							</dc:subject>
							<cc:license rdf:resource="http://creativecommons.org/licenses/by-sa/2.0/" />
							<dc:contributor>
								<cc:Agent>
									<dc:title>Jakub Steiner</dc:title>
								</cc:Agent>
							</dc:contributor>
						</cc:Work>
						<cc:License rdf:about="http://creativecommons.org/licenses/by-sa/2.0/">
							<cc:permits rdf:resource="http://web.resource.org/cc/Reproduction" />
							<cc:permits rdf:resource="http://web.resource.org/cc/Distribution" />
							<cc:requires rdf:resource="http://web.resource.org/cc/Notice" />
							<cc:requires rdf:resource="http://web.resource.org/cc/Attribution" />
							<cc:permits rdf:resource="http://web.resource.org/cc/DerivativeWorks" />
							<cc:requires rdf:resource="http://web.resource.org/cc/ShareAlike" />
						</cc:License>
					</rdf:RDF>
				</svg:metadata>
			</svg:svg>
			<svg:svg viewBox="0 0 48 48" preserveAspectRatio="xMaxYMax meet" width="0" height="0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:cc="http://web.resource.org/cc/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="svg6361">
				<svg:defs>
					<svg:linearGradient id="linearGradient2431">
						<svg:stop style="stop-color:#ffffff;stop-opacity:1;" offset="0" id="stop2433" />
						<svg:stop style="stop-color:#b8b8b8;stop-opacity:1;" offset="1" id="stop2435" />
					</svg:linearGradient>
					<!-- removed drop shadow -->
					<!--
					<svg:linearGradient id="linearGradient21644">
						<svg:stop style="stop-color:#000000;stop-opacity:1;" offset="0" id="stop21646" />
						<svg:stop style="stop-color:#000000;stop-opacity:0;" offset="1" id="stop21648" />
					</svg:linearGradient>
					<svg:radialGradient xlink:href="#linearGradient21644" id="radialGradient21650" cx="25.125" cy="36.75" fx="25.125" fy="36.75" r="15.75" gradientTransform="matrix(1.000000,0.000000,0.000000,0.595238,3.369686e-16,14.87500)" gradientUnits="userSpaceOnUse" />
					-->
					<svg:linearGradient id="linearGradient2933">
						<svg:stop id="stop2935" offset="0" style="stop-color:#9cbcde;stop-opacity:1" />
						<svg:stop id="stop2937" offset="1" style="stop-color:#204a87" />
					</svg:linearGradient>
					<svg:radialGradient xlink:href="#linearGradient2933" id="radialGradient2207" cx="26.544321" cy="28.458725" fx="26.544321" fy="28.458725" r="22.376116" gradientUnits="userSpaceOnUse" gradientTransform="matrix(1.238342,5.954846e-3,-6.507762e-3,1.351272,-6.992513,-9.744842)" />
					<svg:radialGradient xlink:href="#linearGradient2431" id="radialGradient2437" cx="-19.515638" cy="16.855663" fx="-19.515638" fy="16.855663" r="8.7536434" gradientTransform="matrix(4.445991,-8.852599e-16,1.367217e-15,6.8665,67.25071,-104.6679)" gradientUnits="userSpaceOnUse" />
					<svg:g id="helpSign">
						<!-- removed drop shadow -->
						<!--
						<svg:path style="opacity:0.63068181;color:#000000;fill:url(#radialGradient21650);fill-opacity:1.0000000;fill-rule:evenodd;stroke:none;stroke-width:1.0000000;stroke-linecap:round;stroke-linejoin:round;marker:none;marker-start:none;marker-mid:none;marker-end:none;stroke-miterlimit:4.0000000;stroke-dasharray:none;stroke-dashoffset:0.0000000;stroke-opacity:1.0000000;visibility:visible;display:inline;overflow:visible" d="M 40.875000 36.750000 A 15.750000 9.3750000 0 1 1  9.3750000,36.750000 A 15.750000 9.3750000 0 1 1  40.875000 36.750000 z" transform="matrix(1.173803,0.000000,0.000000,0.600000,-5.004403,20.32500)" />
						-->
						<svg:path style="fill:url(#radialGradient2207);fill-opacity:1.0000000;stroke:#204a87" d="M 45.785164 23.825787 A 21.876116 21.876116 0 1 1  2.0329323,23.825787 A 21.876116 21.876116 0 1 1  45.785164 23.825787 z" transform="matrix(0.938442,0.000000,0.000000,0.938680,1.564075,1.633906)" />
						<svg:path transform="matrix(0.855103,0.000000,0.000000,0.855213,3.555288,3.625019)" d="M 45.785164 23.825787 A 21.876116 21.876116 0 1 1  2.0329323,23.825787 A 21.876116 21.876116 0 1 1  45.785164 23.825787 z" style="fill:none;fill-opacity:1.0000000;stroke:#ffffff;stroke-width:3.0307744;stroke-miterlimit:4.0000000;stroke-dasharray:none;stroke-opacity:1.0000000;opacity:0.96022727" />
						<svg:path style="font-size:34.15322876px;font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;text-align:start;line-height:125%;writing-mode:lr-tb;text-anchor:start;fill:url(#radialGradient2437);fill-opacity:1;stroke:#ffffff;stroke-width:1.09947276px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:0.78612713;font-family:Bitstream Vera Sans" d="M -20.25,5.875 C -21.309019,5.8750263 -22.397637,5.9982356 -23.53125,6.21875 C -24.664175,6.4391783 -25.911412,6.7562625 -27.28125,7.21875 C -27.291632,7.21754 -27.302118,7.21754 -27.3125,7.21875 C -27.324563,7.2273788 -27.335121,7.237937 -27.34375,7.25 C -27.355813,7.2586288 -27.366371,7.269187 -27.375,7.28125 C -27.37621,7.2916315 -27.37621,7.3021185 -27.375,7.3125 C -27.37621,7.3228815 -27.37621,7.3333685 -27.375,7.34375 L -27.375,12.5 C -27.37621,12.510382 -27.37621,12.520868 -27.375,12.53125 C -27.37621,12.541632 -27.37621,12.552118 -27.375,12.5625 C -27.366371,12.574563 -27.355813,12.585121 -27.34375,12.59375 C -27.335121,12.605813 -27.324563,12.616371 -27.3125,12.625 C -27.302118,12.62621 -27.291632,12.62621 -27.28125,12.625 C -27.270868,12.62621 -27.260382,12.62621 -27.25,12.625 C -27.239618,12.62621 -27.229132,12.62621 -27.21875,12.625 C -27.208368,12.62621 -27.197882,12.62621 -27.1875,12.625 C -26.045062,11.905957 -24.954148,11.357862 -23.90625,11 C -22.858109,10.631244 -21.863134,10.437521 -20.96875,10.4375 C -20.019532,10.437521 -19.323825,10.648045 -18.8125,11.0625 C -18.303777,11.46459 -18.031262,12.04554 -18.03125,12.78125 C -18.03126,13.261907 -18.175438,13.73266 -18.46875,14.21875 C -18.751741,14.705766 -19.209015,15.249245 -19.84375,15.8125 L -20.9375,16.75 C -22.138959,17.83049 -22.926743,18.741022 -23.3125,19.46875 C -23.695613,20.180196 -23.875005,20.988074 -23.875,21.90625 L -23.875,22.71875 C -23.87621,22.729132 -23.87621,22.739618 -23.875,22.75 C -23.87621,22.760382 -23.87621,22.770868 -23.875,22.78125 C -23.866371,22.793313 -23.855813,22.803871 -23.84375,22.8125 C -23.835121,22.824563 -23.824563,22.835121 -23.8125,22.84375 C -23.802118,22.84496 -23.791632,22.84496 -23.78125,22.84375 C -23.770868,22.84496 -23.760382,22.84496 -23.75,22.84375 L -17.65625,22.84375 C -17.645868,22.84496 -17.635382,22.84496 -17.625,22.84375 C -17.614618,22.84496 -17.604132,22.84496 -17.59375,22.84375 C -17.581687,22.835121 -17.571129,22.824563 -17.5625,22.8125 C -17.550437,22.803871 -17.539879,22.793313 -17.53125,22.78125 C -17.53004,22.770868 -17.53004,22.760382 -17.53125,22.75 C -17.53004,22.739618 -17.53004,22.729132 -17.53125,22.71875 L -17.53125,21.96875 C -17.531261,21.500554 -17.38288,21.075901 -17.15625,20.6875 C -16.933955,20.296216 -16.448177,19.737141 -15.6875,19.0625 L -14.625,18.125 C -13.558412,17.14269 -12.794341,16.240346 -12.34375,15.375 C -11.894481,14.500954 -11.656268,13.50158 -11.65625,12.40625 C -11.656268,10.279985 -12.400019,8.6722224 -13.875,7.5625 C -15.350197,6.4414748 -17.48124,5.8750263 -20.25,5.875 z M -23.8125,25.03125 C -23.824563,25.039879 -23.835121,25.050437 -23.84375,25.0625 C -23.855813,25.071129 -23.866371,25.081687 -23.875,25.09375 C -23.87621,25.104132 -23.87621,25.114618 -23.875,25.125 C -23.87621,25.135382 -23.87621,25.145868 -23.875,25.15625 L -23.875,31 C -23.87621,31.010382 -23.87621,31.020868 -23.875,31.03125 C -23.87621,31.041632 -23.87621,31.052118 -23.875,31.0625 C -23.866371,31.074563 -23.855813,31.085121 -23.84375,31.09375 C -23.835121,31.105813 -23.824563,31.116371 -23.8125,31.125 C -23.802118,31.12621 -23.791632,31.12621 -23.78125,31.125 C -23.770868,31.12621 -23.760382,31.12621 -23.75,31.125 L -17.65625,31.125 C -17.645868,31.12621 -17.635382,31.12621 -17.625,31.125 C -17.614618,31.12621 -17.604132,31.12621 -17.59375,31.125 C -17.581687,31.116371 -17.571129,31.105813 -17.5625,31.09375 C -17.550437,31.085121 -17.539879,31.074563 -17.53125,31.0625 C -17.53004,31.052118 -17.53004,31.041632 -17.53125,31.03125 C -17.53004,31.020868 -17.53004,31.010382 -17.53125,31 L -17.53125,25.15625 C -17.53004,25.145868 -17.53004,25.135382 -17.53125,25.125 C -17.53004,25.114618 -17.53004,25.104132 -17.53125,25.09375 C -17.539879,25.081687 -17.550437,25.071129 -17.5625,25.0625 C -17.571129,25.050437 -17.581687,25.039879 -17.59375,25.03125 C -17.604132,25.03004 -17.614618,25.03004 -17.625,25.03125 C -17.635382,25.03004 -17.645868,25.03004 -17.65625,25.03125 L -23.75,25.03125 C -23.760382,25.03004 -23.770868,25.03004 -23.78125,25.03125 C -23.791632,25.03004 -23.802118,25.03004 -23.8125,25.03125 z " transform="matrix(0.849895,0,0,0.835205,41.72981,8.548327)" />
					</svg:g>
				</svg:defs>
				<svg:metadata>
					<rdf:RDF>
						<cc:Work rdf:about="">
							<dc:format>image/svg+xml</dc:format>
							<dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
							<dc:title>Help Browser</dc:title>
							<dc:date>2005-11-06</dc:date>
							<dc:creator>
								<cc:Agent>
									<dc:title>Tuomas Kuosmanen</dc:title>
								</cc:Agent>
							</dc:creator>
							<dc:subject>
								<rdf:Bag>
									<rdf:li>help</rdf:li>
									<rdf:li>browser</rdf:li>
									<rdf:li>documentation</rdf:li>
									<rdf:li>docs</rdf:li>
									<rdf:li>man</rdf:li>
									<rdf:li>info</rdf:li>
								</rdf:Bag>
							</dc:subject>
							<cc:license rdf:resource="http://creativecommons.org/licenses/by-sa/2.0/" />
							<dc:contributor>
								<cc:Agent>
									<dc:title>Jakub Steiner, Andreas Nilsson</dc:title>
								</cc:Agent>
							</dc:contributor>
							<dc:source>http://tigert.com</dc:source>
						</cc:Work>
						<cc:License rdf:about="http://creativecommons.org/licenses/by-sa/2.0/">
							<cc:permits rdf:resource="http://web.resource.org/cc/Reproduction" />
							<cc:permits rdf:resource="http://web.resource.org/cc/Distribution" />
							<cc:requires rdf:resource="http://web.resource.org/cc/Notice" />
							<cc:requires rdf:resource="http://web.resource.org/cc/Attribution" />
							<cc:permits rdf:resource="http://web.resource.org/cc/DerivativeWorks" />
							<cc:requires rdf:resource="http://web.resource.org/cc/ShareAlike" />
						</cc:License>
					</rdf:RDF>
				</svg:metadata>
			</svg:svg>
<?php endif; ?>
		</div>
<?php if($svg): ?>
		<div style="float:right; margin:-6em -6em 0 0; width:10em; height:10em"><svg:svg viewBox="0 0 48 48" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><svg:use xlink:href="#exceptionSign" /></svg:svg></div>
<?php endif; ?>
		<h1><?php echo get_class($e); ?></h1>
<?php if($e instanceof AgaviException): ?>
		<p id="help"<?php if($svg): ?> class="nice"<?php endif; ?>>
<?php if($svg): ?>
			<div style="position:absolute; top:-1.25em; left:-2em; height:5em; width:5em;"><svg:svg viewBox="3 3 42 42" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><svg:use xlink:href="#helpSign" /></svg:svg></div>
<?php endif; ?>
			This is an internal Agavi exception. Please consult the documentation for assistance with solving this issue.</p>
<?php else: ?>
		<p id="help"<?php if($svg): ?> class="nice"<?php endif; ?>>
<?php if($svg): ?>
			<div style="position:absolute; top:-1.25em; left:-2em; height:5em; width:5em;"><svg:svg viewBox="3 3 42 42" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><svg:use xlink:href="#helpSign" /></svg:svg></div>
<?php endif; ?>
			This is <em>not</em> an Agavi exception, but likely an error that occurred in the application code.</p>
<?php endif; ?>
		<p>An exception of type <strong><?php echo get_class($e); ?></strong> was thrown, but did not get caught during the execution of the request. You will find information provided by the exception along with a stack trace below.</p>
<?php $msg = nl2br(htmlspecialchars($e->getMessage())); ?>
<?php if($msg != ''): ?>
		<p id="message"<?php if($svg): ?> class="nice"<?php endif; ?>>
<?php if($svg): ?>
			<div style="position:absolute; top:-1.25em; left:-2em; height:5em; width:5em;"><svg:svg viewBox="3 0 43 43" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><svg:use xlink:href="#importantSign" /></svg:svg></div>
<?php endif; ?>
			<?php echo $msg; ?>
		</p>
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
foreach($fixedTrace as $trace):
	$i++;
	if(isset($trace['file']) && !isset($highlights[$trace['file']])) {
		$highlights[$trace['file']] = AgaviException::highlightFile($trace['file']);
	}
?>
			<li id="frame<?php echo $i; ?>"<?php if($i != 2): ?> class="hidecode"<?php endif; ?>>at <?php if($i > 1): ?><strong><?php if(isset($trace['class'])): ?><?php echo $trace['class'], htmlspecialchars($trace['type']); ?><?php endif; ?><?php echo $trace['function']; ?><?php if(isset($trace['args'])): ?>(<?php echo AgaviException::buildParamList($trace['args']); ?>)<?php endif; ?></strong><?php else: ?><em>exception origin</em><?php endif; ?><br />in <?php if(isset($trace['file'])): echo preg_replace(array_keys($filepaths), $filepaths, $trace['file']); ?> <a href="#frame<?php echo $i; ?>" class="toggle" title="Toggle source code snippet" onclick="this.parentNode.className = this.parentNode.className == 'hidecode' ? '' : 'hidecode'; return false;">line <?php echo $trace['line']; ?></a><ol start="<?php echo $start = $trace['line'] < 4 ? 1 : $trace['line'] - 3; ?>" style="padding-left:<?php echo strlen($start+6)*0.6+2; ?>em"><?php
$lines = array_slice($highlights[$trace['file']], $start - 1, 7, true);
foreach($lines as $key => &$line) {
	if($key + 1 == $trace['line']): ?><li class="highlight"><?php if($svg): ?><div style="float:left; width:1em; height:1em; margin-left:-1.35em; background-color:#FFF;"><svg:svg viewBox="2 1 45 43" preserveAspectRatio="xMaxYMax meet" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><svg:use xlink:href="#stopSign" /></svg:svg></div><?php endif; else: ?><li><?php endif; ?><code><?php
	echo $line;
?></code></li>
<?php } ?></ol><?php else: // no info about origin file ?><em>unknown</em><?php endif; ?></li>
<?php endforeach; ?>
		</ol>
		<h3>Version Information</h3>
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
	</body>
</html>