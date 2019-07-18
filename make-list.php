<?php
$srcUrl = "https://www.swindon.gov.uk/info/20015/bins_rubbish_and_recycling/138/recycling_guide/6";
$outputFile = "recycling.json";

$html = file( $srcUrl );
$materials = [];
$binsFound = [];

function getColour($bin) {
	if (strpos($bin, 'Black wheelie bin') !== false) {
		return 'black';
	}
	if (0 === strpos($bin, 'Recycling box')) {
		return 'orange';
	}
	if (0 === strpos($bin, 'Textiles collection')) {
		return 'orange';
	}
	if (0 === strpos($bin, 'Garden waste')) {
		return 'green';
	}
	if (0 === strpos($bin, 'Plastics collection')) {
		return 'white';
	}
	if (strpos($bin, 'HWRC') !== false) {
		return 'grey';
	}
}

function recyclable($bin) {
	return
		(0 === strpos($bin, 'Recycling box')) or
		(0 === strpos($bin, 'Garden waste')) or
		(0 === strpos($bin, 'Plastics collection')) or
		(0 === strpos($bin, 'Textiles collection')) or 
		(0 === strpos($bin, 'Special seasonal waste collection')) or
		(strpos($bin, 'HWRC') !== false);
}

foreach ($html as $line) {
	$item = "";
	$bin = "";
	$note = "";

	if (preg_match('/\s*<li>.+ &ndash; [^<]+<\/li>/', $line)) {
		$line = str_replace('&ndash;', '-', $line);
	}
	$line = str_replace('&nbsp;', ' ', $line);
	if (preg_match('/\s*<li>(.+) [-] (.+)<\/li>/', $line, $matches)) {
		$item = trim($matches[1]);
		$bin = trim($matches[2]);

		// Extract notes
		if (preg_match('/([^\(]+)\(([^\)]+)\)/', $bin, $noteMatches)) {
			echo "Found bin with note: " . $bin . "\n";
			$bin = trim($noteMatches[1]);
			$note = trim($noteMatches[2]);
			echo "Bin is: " . $bin . ". Note is: " . $note . "\n";
		}
		echo 'Found item: "' . $item . '" that goes in bin: ' . $bin . "\n\n";
		$binsFound[$bin] = isset($binsFound[$bin]) ? $binsFound[$bin] + 1 : 1;

		// Set colours
		$colour = getColour($bin);

		// Set material value
		$material = new stdClass();
		$material->name = $item;
		$material->bin = $bin;
		$material->recycle = recyclable($bin);
		$material->colour = getColour($bin);
		$material->notes = $note;
		$materials[] = $material;
	}
}

echo "\n\n";

foreach ($binsFound as $bin => $count) {
	echo 'Bin ' . $bin . ' has ' . $count . ' items' . "\n";
}

echo "\n\n" . count($binsFound) . " bins found\n\n";

echo json_encode($materials);
