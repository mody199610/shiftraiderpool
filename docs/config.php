<?php
//config.php
date_default_timezone_set("UTC");
$date = date("Y-m-d");

$coin       	        = "SHIFT";
$address		= "13651129144491004048S";
$publicKey 		= "";
$secret			= "";
$secret2 		= "";
$apiHost 		= "http://wallet.shiftnrg.nl:9305";
$whatToPay		= 0.50; // Percentage
$minimum		= "100000000"; // 1 unit

$database 		= "payouts.sqlite3";
$table 			= "stats";
$table2			= "voters";
$index 			= "uniqueCol1";
$indexColumn 	        = "address";
// Put addresses in this blacklist to stop paying them
$blacklist = array(
        "716889826061992156S","18323570191592363566S","16442601914030040568S" // OAKIE22,DAFRICASH,KIASHAAN
);
