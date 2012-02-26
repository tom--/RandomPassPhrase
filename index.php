<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<title>Pandom PassPhrase</title>
	<style type="text/css" media="all">
		p {
			font-family: "Segoe UI", Calibri, Tahoma, "Arial Narrow", "Lucida Grande", "DejaVu Sans Condensed", "Helvetica Narrow", Verdana, Arial, sans-serif
			}
		p.big {
			font-size: 3em;
			text-align: center;
			margin-top: 3em;
			}
		p.small {
			margin-top: 8em;
			}
	</style>

</head>
<body>
<?php require './Randomness.php'; ?>
<p class="big"><?php echo Randomness::randomPassPhrase() . PHP_EOL; ?></p>
<p class="small">Complete code for web site is on <a href="https://github.com/tom--/RandomPassPhrase">GitHub</a></p>
</body>
</html>