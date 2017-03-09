<?php
$output = '';

//Next let's initialize a variable for our filename prefix (optional).
$filename_prefix = 'schedule';

if (isset($csv_header)) {
	$output .= $csv_header;
	$output .= "\n";
}

if (isset($results)) {
    $format = $results;
	$output .= $format;
}
//Now we're ready to create a file. This method generates a filename based on the current date & time.
$filename = $filename_prefix."_".date("Y-m-d_H-i-s-A",time());

//Generate the CSV file header
header("Content-type: application/octet-stream");
header("Content-Encoding: UTF-8");
header("Content-type: text/csv; charset=UTF-8");
header("Content-disposition: csv" . date("Y-m-d") . ".csv");
header("Content-disposition:attachment; filename=".$filename.".csv");
echo "\xEF\xBB\xBF"; // UTF-8 BOM
//Print the contents of out to the generated file.
print_r($output);

//Exit the script
exit;
?>
