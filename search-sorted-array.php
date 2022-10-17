<?php

function search_in_array(array $A, $x): int {
	$left = 0;
	$right = count($A) - 1;

	while ($left <= $right) {
		$middle = intdiv($left + $right, 2);
		//echo "$left $right .. middle $middle\n";
		if ($left == $right) {
			if ($A[$left] < $x) {
				return -1; // all values in the array are smaller than $x... $A[$j] needs to be >= $x
			} else {
				return $left;
			}
		}
		if ($A[$middle] < $x) {
			$left = $middle + 1;
		} else {
			$right = $middle;
		}
	}
	throw new Exception("This should never be reached.");
}

$A = [/*0*/1,2,3,4,5,/*5*/5,/*6*/6,/*7*/6,/*8*/8,9,10,15,20,/*13*/25];
if (!isset($argv[1]) || !is_numeric($argv[1])) {
	throw new Exception("Target integer expected as first argument.");
}
$x = $argv[1];
// if value $x isn't in the array, then $A[$j] needs to be the first larger value than $x
echo search_in_array($A, $x) . "\n";


