<?php

$nPhrases = isset($_GET['np']) && preg_match('%^[01]?\d$%', $_GET['np'])
	? max($_GET['np'], 1)
	: 1;
$nWords = isset($_GET['nw']) && preg_match('%^\d$%', $_GET['nw'])
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
$minPhraseLen = isset($_GET['pl']) && preg_match('%^\d{1,2}$%', $_GET['pl'])
	? max($_GET['pl'], 6)
	: 14;
$format = isset($_GET['fm']) && preg_match('%^(html|text|json)$%i', $_GET['fm'])
	? $_GET['fm']
	: 'html';
require './Randomness.php';
$phrases = array();
for ($i = 0; $i < $nPhrases; ++$i)
	$phrases[] = Randomness::randomPassPhrase(
		$nWords, $maxWordLen, $nSpecials, $nDigits, $minPhraseLen, true
	);

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

		li {
			list-style-type: none;
		}

		span {
			font-family: Consolas, Monaco, Menlo, "Lucida Console", "Andale Mono", Courier, "Courier New", monospace;
		}

		span span {
			font-weight: bold;
		}
	</style>

</head>
<body>

<p class="big"><?php echo implode("<br>\n", $phrases); ?></p>

<p class="small">Complete code for this web site is on <a
	href="https://github.com/tom--/RandomPassPhrase">GitHub</a></p>

<p>Web service API <span> (range) [default]</span></p>
<ul>
	<li><span>@param int <span>np</span> </span> Number of phrases to generate
		<span>(1..19) [1]</span></li>
	<li><span>@param int <span>nw</span> </span> Number of words in the phrase
		<span>(1..9) [4]</span></li>
	<li><span>@param int <span>wl</span> </span> Maximum number of ascii characters per word <span>(5..99) [10]</span>
	</li>
	<li><span>@param int <span>ns</span> </span> Number of non-alphanumeric ascii chars to insert
		<span>(0..9) [1]</span></li>
	<li><span>@param int <span>nd</span> </span> Number of digits to append <span>(0..9) [1]</span>
	</li>
	<li><span>@param int <span>pl</span> </span> Minimum number of ascii characters in phrase <span>(6..99) [14]</span>
	</li>
	<li><span>@param string <span>fm</span> </span> Response format
		<span>(html|text|json) [html]</span></li>
</ul>
</body>
</html>