<?php

/**
 * ===================================================================
 * ================ Arguments parsing + constants ====================
 * ===================================================================
 */

if (empty($argv[1]) || !file_exists($argv[1])) {
	throw new Exception("Path to a readable file is required to be entered as the first argument.");
}

if (!isset($argv[2]) || !ctype_digit($argv[2])) {
	throw new Exception("Line number (a positive integer) is expected as the second argument.");
}

$input_file_path = $argv[1];
$index_file_path = __DIR__ . '/' .basename($input_file_path) . ".idx";
$line_index = $argv[2]; // index of the wanted line in the input file

// max 1 bilion lines, max 1000 chars per line (+ newline char = 1001)
// 1 bilion lines = 10 digits (9 zeroes)
// 1001 chars per line = 4 digits
// 10^9 * 1001 = 1 001 000 000 000 = 13 digits needed for index number
const INDEX_PADDING = 13;

/**
 * ===================================================================
 * ========================== FUNCTIONS ==============================
 * ===================================================================
 */


/**
 * @param resource $file_handle
 */
function get_all_lines($file_handle): Generator {
	while (!feof($file_handle)) {
		yield fgets($file_handle);
	}
}

/**
 * @param resource $file_handle
 *
 * @return string - left padded pointer position returned as a number with newline
 */
function get_padded_pointer_position($file_handle): string {
	return str_pad(ftell($file_handle), INDEX_PADDING, ' ') . "\n";
}

/**
 * @param string $index_file_path
 * @param int $line_index
 *
 * @return int|bool -
 */
function get_byte_index(string $index_file_path, int $line_index): int|bool {
	$index_file_handle = fopen($index_file_path, "r");
	fseek($index_file_handle, $line_index * (INDEX_PADDING+1));
	$index = fread($index_file_handle, INDEX_PADDING);
	fclose($index_file_handle);
	return $index === false ? false : (int) $index;
}

/**
 * Generates an index file with byte positions of the lines in the input file.
 * If we want to fetch line with number $x from the input file, we can find
 * the starting byte position of the line on the corresponding line in the index file.
 */
function create_index_file(string $input_file_path, string $index_file_path): void {
	fwrite(STDERR, "Writing index to '${index_file_path}'... ");
	$input_file_handle = fopen($input_file_path, "r");
	$index_file_handle = fopen($index_file_path, "w");
	fwrite($index_file_handle, get_padded_pointer_position($input_file_handle));
	foreach (get_all_lines($input_file_handle) as $line) {
		if ($line === false) {
			break;
		}
		fwrite($index_file_handle, get_padded_pointer_position($input_file_handle));
	}
	fclose($input_file_handle);
	fclose($index_file_handle);
	fwrite(STDERR, "done.\n");
}

/**
 * @param int $byte_index - index of the byte in the file where the reading starts
 *
 * @return bool|string - returns a string starting at $byte_index position and ending at first newline found (newline character included in the returned string)
 */
function get_line_using_byte_index(string $input_file_path, int $byte_index): bool|string {
	$input_file_handle = fopen($input_file_path, "r");
	fseek($input_file_handle, $byte_index);
	$line = fgets($input_file_handle);
	fclose($input_file_handle);
	return $line;
}

/**
 * ===================================================================
 * =========================    MAIN    ==============================
 * ===================================================================
 */

// if index file doesn't exist - generate it
if (!file_exists($index_file_path)) {
	create_index_file($input_file_path, $index_file_path);
}
// TODO else  possible option for checking the hash of the file to see if we have valid index file (would slow down search)

$byte_index = get_byte_index($index_file_path, (int) $line_index);
if ($byte_index === false) {
	error_log("Out of range.");
	exit(1);
}
$line = get_line_using_byte_index($input_file_path, $byte_index);
echo $line;

