<?php
session_start();
// AJAX CALLS THIS LOGIN CODE TO EXECUTE
if(isset($_POST["e"])){
	// START Expansion
	// Get user ip address
	$ip = preg_replace('#[^0-9.]#', '', getenv('REMOTE_ADDR'));
	
	// Get referer from header
	$refer = preg_replace('#[^a-z0-9 -._]#i', '.', getenv('HTTP_REFERER'));	
	
	// Set variable for possible logging
	$csrf = "";
	
	// Check for login session	
	if(isset($_SESSION['login']) && isset($_SESSION['login']['tm']) && isset($_SESSION['login']['tk']) && isset($_POST['t'])){
		// Sanitize everything now
		$sTimestamp = preg_replace('#[^0-9]#', '', $_SESSION['login']['tm']);
		$sToken = preg_replace('#[^a-z0-9.-]#i', '', $_SESSION['login']['tk']);
		$fToken = preg_replace('#[^a-z0-9.-]#i', '', $_POST['t']);
		
		// Make sure we have values after sanitizing
		if($sTimestamp != "" && $sToken != "" && $fToken != ""){
			// Check if session and post token match
			if($fToken !== $sToken){
				$csrf .= "Form token and session token do not match|";
			}
			// Do 5 minute check
			$elapsed = time() - $sTimestamp;
			if($elapsed > 300){
				$csrf .= "Expired session|";
			}
			// add more checks here if needed			
		} else {
			$csrf .= "A critical session or form token post was empty after sanitization|";
		}	
	} else {
		// Something fishy is going on .. our session is not set
		$csrf .= "A critical session or form token post was not set|";		
	}
	// CONNECT TO THE DATABASE
	include_once("db.php");	//Database connection file
	// Check our errors here
	if($csrf !== ""){
		// At least one of our tests above was failed
		// Sanitize the e & p posts for logging
		$e = mysqli_real_escape_string($db, $_POST['e']);
		$p = mysqli_real_escape_string($db, $_POST['p']);
		
		// Time to log this
		$sql = "INSERT INTO logging (dt, ip, referer, issues, epost, ppost) VALUES(now(),?,?,?,?,?)";  //Store into "loggin" database table
		$stmt = $db->stmt_init();
		$stmt = $db->prepare($sql);
		$stmt->bind_param("sssss", $ip, $refer, $csrf, $e, $p);
		$stmt->execute();
		$stmt->close();
		
		// Unset 
		if(isset($_SESSION['login'])){
			unset($_SESSION['login']);
		}
		// Throttle back the attack
		sleep(3);
		// Return generic login_failed and exit script
		echo "login_failed";
        exit();
	}
	
	// Move ip grabber to top of this script
	// Change database connection
	// Move database connection into this script
	// Add session unset in existing form processing if they log in	
	// END Expansion
	// GATHER THE POSTED DATA INTO LOCAL VARIABLES AND SANITIZE
	$e = mysqli_real_escape_string($db, $_POST['e']);
	$p = $_POST['p'];
	
	// FORM DATA ERROR HANDLING
	if($e == "" || $p == ""){
		echo "login_failed";
        exit();
	} else {
	// END FORM DATA ERROR HANDLING
	$sql = "SELECT id, username, password FROM users_table WHERE email = ? AND activated = '1' LIMIT 1";
	$stmt = $db->stmt_init();
	$stmt = $db->prepare($sql);	
    $stmt->bind_param("s", $e);   
    $stmt->execute();    
    $stmt->bind_result($db_id, $db_username, $db_pass_str);    
    $query = $stmt->fetch();   
    $stmt->close();
		//if($p != $db_pass_str){
		if(password_verify($p, $db_pass_str)){
			// CREATE THEIR SESSIONS AND COOKIES
			$_SESSION['userid'] = $db_id;
			$_SESSION['username'] = $db_username;
			setcookie("id", $db_id, strtotime( '+30 days' ), "/", "", "", TRUE);
			setcookie("user", $db_username, strtotime( '+30 days' ), "/", "", "", TRUE);
			
			// UPDATE THEIR "IP" AND "LASTLOGIN" FIELDS
			$sql = "UPDATE users_table SET ip = ?, lastlogin=now() WHERE username = ? LIMIT 1";
			$stmt = $db->stmt_init();
			$stmt = $db->prepare($sql);
			$stmt->bind_param("ss", $ip, $db_username);
			$stmt->execute();
			$stmt->close();
			echo $db_id;
		    exit();
		} else {			
			echo "login_failed";
            exit();			
		}
	}
	exit();
}
?>