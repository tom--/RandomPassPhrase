<?php

$nPhrases = isset($_GET['np']) && preg_match('%^[01]?\d$%', $_GET['np'])
	? max($_GET['np'], 1)
	: 1;
$length = isset($_GET['nw']) && preg_match('%^\d$%', $_GET['nw'])
	? max($_GET['nw'], 1)
	: 4;
$maxWordLen = isset($_GET['wl']) && preg_match('%^\d{1,2}$%', $_GET['wl'])
	? max($_GET['wl'], 5)
	: 10;
$nSpecials = isset($_GET['ns']) && preg_match('%^\d$%', $_GET['ns'])
	? $_GET['ns']
	: 1;
$nDigits = isset($_GET['nd']) && preg_match('%^\d$%', $_GET['nd'])
	? $_GET['nd']
	: 1;
$format = isset($_GET['fm']) && preg_match('%^(html|text|json)$%i', $_GET['fm'])
	? $_GET['fm']
	: 'html';
require './Randomness.php';
$phrases = array();
for ($i = 0; $i < $nPhrases; ++$i)
	$phrases[] = Randomness::randomPassPhrase($length, $maxWordLen, $nSpecials, $nDigits);

if (!$phrases) {
	$protocol =
		isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : (
		isset($_ENV['SERVER_PROTOCOL']) ? $_ENV['SERVER_PROTOCOL'] : (
		'HTTP/1.0'
		));
	header($protocol . ' 500 Internal Server Error');
	exit;
}

if (strtolower($format) === 'json') {
	echo json_encode($phrases);
	exit;
}

if (strtolower($format) === 'text') {
	echo implode("\n", $phrases);
	exit;
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<title>Pandom PassPhrase</title>
	<style type="text/css" media="all">
		body {
			font-family: "Segoe UI", Calibri, Tahoma, "Arial Narrow", "Lucida Grande", "DejaVu Sans Condensed", "Helvetica Narrow", Verdana, Arial, sans-serif
		}

		p.big {
			font-size: 2em;
			text-align: center;
			margin-top: 3em;
		}

		p.small {
			margin-top: 8em;
		}
		.api li {
			list-style-type: none;
		}
		.api span {
			font-family: Consolas, Monaco, Menlo, "Lucida Console", "Andale Mono", Courier, "Courier New", monospace;
		}
		.api span span {
			font-weight: bold;
		}
	</style>

</head>
<body>

<p class="big"><?php echo implode("<br>\n", $phrases); ?></p>

<p class="small">Complete code for this web site is on <a
	href="https://github.com/tom--/RandomPassPhrase">GitHub</a></p>

<p>Web service API:</p>
<ul class="api">
	<li><span>@param int <span>np</span> </span> Number of phrases to generate (1..19)</li>
	<li><span>@param int <span>nw</span> </span> Number of words in the phrase (1..9)</li>
	<li><span>@param int <span>wl</span> </span> Max number of ascii characters per word (5..99 )</li>
	<li><span>@param int <span>ns</span> </span> Number of non-alphanumeric ascii chars to insert
		(0..9)
	</li>
	<li><span>@param int <span>nd</span> </span> Number of digits to append (0..9)</li>
	<li><span>@param string <span>fm</span> </span> Response format (html|text|json)</li>
</ul>
</body>
</html>