<?php
date_default_timezone_set("UTC");
require "config.php";

/* ________________________________

		   CALCULATE PAYOUT
   ________________________________ */

// sendFunds function
	function sendFunds($host, $secret, $secret2, $amount, $toAddress){

		sleep(2);
		ob_start();
		if(!empty($secret2)){
			$sendFunds = passthru("curl -s -k -H 'Content-Type: application/json' -X PUT -d '{\"secret\":\"$secret\",\"secondSecret\":\"$secret2\",\"amount\":".$amount.",\"recipientId\":\"$toAddress\"}' $host/api/transactions");
		}else{
			$sendFunds = passthru("curl -s -k -H 'Content-Type: application/json' -X PUT -d '{\"secret\":\"$secret\",\"amount\":".$amount.",\"recipientId\":\"$toAddress\"}' $host/api/transactions");
		}
		$sendFunds = ob_get_contents();
		ob_end_clean();

		return $sendFunds;
	}


// Database initialisation
    $db = new SQLite3($database) or die("[ SQLITE3 ] Unable to open database");
    $db->exec("CREATE TABLE IF NOT EXISTS $table (
                    id INTEGER PRIMARY KEY,  
                    date TEXT NULL,
                    forged TEXT NULL,
                    fees TEXT NULL,
                    rewards TEXT NULL, 
                    balance TEXT NULL)");
    $db->exec("CREATE TABLE IF NOT EXISTS $table2 (
                    id INTEGER PRIMARY KEY, 
                    date TEXT NULL, 
                    username TEXT NULL, 
                    address TEXT NULL,
                    share TEXT NULL,
                    amount TEXT NULL, 
                    paid TEXT NULL, 
                    transid TEXT NULL)");

	// Check if a row exists, if not add it
	    $check_exists = $db->query("SELECT count(*) as count FROM $table");
	    $check_exists = $check_exists->fetchArray();
	    $check_exists = $check_exists['count'];

// Check current rewards
	ob_start();
	$getForged 		= passthru("curl -s -k -X GET '$apiHost/api/delegates/forging/getForgedByAccount?generatorPublicKey=$publicKey'");
	$getForged 		= ob_get_contents();
	$getForged 		= json_decode($getForged, true);
	ob_end_clean();

	if($check_exists < 1){
	    $insert = "INSERT INTO $table (date, forged, fees, rewards, balance) VALUES ('".date('Y-m-d', strtotime('-1 day'))."','".$getForged['forged']."','".$getForged['fees']."','".$getForged['rewards']."','0')";
            $insert = $db->exec($insert) or die("[ SQLITE3 ] FAILED TO ADD AN EMPTY ROW!");
        }

// Let's first determine if this script hit his daily max (1)
	$determine = $db->query("SELECT count(*) as determine FROM $table WHERE date='$date' AND balance!='0'");
	$determine = $determine->fetchArray();
	$determine = $determine['determine'];

	if($determine > 0){
		exit("You may only execute this script once a day!\n");
	}

// Check previous rewards
    $prevRewards 	= $db->query("SELECT rewards FROM $table ORDER BY id DESC LIMIT 1");
    $prevRewards 	= $prevRewards->fetchArray();
    $prevRewards 	= $prevRewards['rewards'];


	echo "\n";
	
	// Insert current rewards into database
		if($getForged['success'] != "true" && $getForged['forged'] == "0"){
			echo $date." This delegate did not forge (yet) or there's an error in the API call...\n";
		}else{
			$insert = "INSERT INTO $table (date, forged, fees, rewards) VALUES ('$date', '".$getForged['forged']."', '".$getForged['fees']."', '".$getForged['rewards']."')";
	        $insert = $db->exec($insert) or die("[ SQLITE3 ] FAILED TO ADD NEW STATS!");

			echo "   Forged: ".number_format(($getForged['forged'] / 100000000), 2,",",".")." ".$coin."\n";
			echo "   Fees: ".number_format(($getForged['fees'] / 100000000), 2,",",".")." ".$coin."\n";
			echo "   Rewards: ".number_format(($getForged['rewards'] / 100000000), 2,",",".")." ".$coin."\n";
		}


// Calculate total payout
	$totalPayout = ($getForged['rewards'] - $prevRewards);


// If $totalPayout < 1, exit
	if($totalPayout < 1){
		exit("totalPayout ($totalPayout) not high enough to continue..\n");
	}


// Check who voted our wallet
	ob_start();
	$getVoters 		= passthru("curl -s -k -X GET '$apiHost/api/delegates/voters?publicKey=$publicKey'");
	$getVoters 		= ob_get_contents();
	ob_end_clean();


// And add their balance to the grand total
	$balance = 0;
	foreach(json_decode($getVoters, true)['accounts'] as $voter){
		$balance = ($balance + $voter['balance']);
		// echo "______________\n";
		// echo "Username: ".$voter['username']."\n";
		// echo "Address: ".$voter['address']."\n";
		// echo "Public key: ".$voter['publicKey']."\n";
		// echo "Balance: ".$voter['balance']."\n";
	}

	// Update stats table with total balance
		$updateBalance = "UPDATE $table SET balance='$balance' WHERE date='$date'";
	    $updateBalance = $db->exec($updateBalance) or die("[ SQLITE3 ] FAILED TO UPDATE BALANCE!");


	// Echo total balance
		echo "   Total balance: $balance (".number_format(($balance / 100000000), 2,",",".")." ".$coin.")\n";


// Calculate % per voter
	$rewards = ($totalPayout * $whatToPay) / 100;
	echo "   To divide: ".$rewards." (".number_format(($rewards / 1000000), 2,",",".")." ".$coin.")\n";

	foreach(json_decode($getVoters, true)['accounts'] as $voter){
		echo "______________\n";
		
		if(!empty($voter['username'])){
			echo "Username: ".$voter['username']."\n";
		}else{
			echo "Address: ".$voter['address']."\n";
		}
		
		$share = ($voter['balance'] / $balance) * 100;
		echo "Share: ".number_format($share, 2,",",".")."%\n";

		$payout = ($rewards * $share);
		$payout = round($payout);
		// echo "Ark: ".$payout."\n";
		echo $coin.": ".number_format(($payout / 100000000), 4,",",".")."\n";

		// Add the voters payout to the database to keep a history
			$insert = "INSERT INTO $table2 (date, username, address, share, amount) VALUES ('$date', '".$voter['username']."', '".$voter['address']."', '$share', '$payout')";
	        $insert = $db->exec($insert) or die("[ SQLITE3 ] FAILED TO ADD VOTER!");

	    // Check $userBalance
	        $userBalance 	= $db->query("SELECT sum(amount) as userBalance FROM $table2 WHERE address='".$voter['address']."' and paid IS NULL");
    		$userBalance 	= $userBalance->fetchArray();
    		$userBalance 	= $userBalance['userBalance'];

	    // If $userBalance >= $minimum, send the funds to the voter and set paid=true to each row which contains $address
	        if($userBalance >= $minimum && !in_array($voter['address'], $blacklist)){
	        	$sendTrans = sendFunds($apiHost, $secret, $secret2, $userBalance, $voter['address']);
	        	// $sendTrans = '{"success": true,"transactionId": "1234567890"}';
	        	$sendTrans = json_decode($sendTrans, true);

	        	if(!empty($sendTrans) && $sendTrans['success'] == "true"){
			    	// Save the transactionId in the same row as the payout
		        		$updateTransId = "UPDATE $table2 SET transid='".$sendTrans['transactionId']."' WHERE date='$date' AND address='".$voter['address']."'";
		    			$updateTransId = $db->exec($updateTransId) or die("[ SQLITE3 ] FAILED TO UPDATE TRANSACTION ID!");
		    		// Set all paid=true where row contains $address
		    			$setPaid = "UPDATE $table2 SET paid='true' WHERE address='".$voter['address']."'";
		    			$setPaid = $db->exec($setPaid) or die("[ SQLITE3 ] FAILED TO SET PAID STATUS!");
			    
		    			echo "Voter has been paid!\n";
			    }else{
			    	echo var_dump($sendTrans)."\n";
			    }
	        }else{
	        	echo "Not enough userBalance to send funds!\n";
	        }
	        
	}
	
