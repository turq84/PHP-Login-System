<?php
session_start();
include_once("db.php");

// Files that inculde this file at the very top would NOT require 
// connection to database or session_start(), be careful.
// Initialize some vars
$user_ok = false;
$log_id = "";
$log_username = "";

// User Verify function
function evalLoggedUser($db,$id,$u){
	$sql = "SELECT ip FROM users_table WHERE id=? AND username=? AND activated='1' LIMIT 1";
	$stmt = $db->stmt_init();
	$stmt = $db->prepare($sql);
	$stmt->bind_param("is", $id, $u);
	$stmt->execute();
	$stmt->store_result();
	$numrows = $stmt->num_rows;
	$stmt->close();
	if($numrows > 0){
		return true;
	}
}
if(isset($_SESSION["userid"]) && isset($_SESSION["username"])) {
	$log_id = preg_replace('#[^0-9]#', '', $_SESSION['userid']);
	$log_username = preg_replace('#[^a-z0-9]#i', '', $_SESSION['username']);
	
	// Verify the user
	$user_ok = evalLoggedUser($db,$log_id,$log_username);
} else if(isset($_COOKIE["id"]) && isset($_COOKIE["user"])){
	$_SESSION['userid'] = preg_replace('#[^0-9]#', '', $_COOKIE['id']);
    $_SESSION['username'] = preg_replace('#[^a-z0-9]#i', '', $_COOKIE['user']);
	$log_id = $_SESSION['userid'];
	$log_username = $_SESSION['username'];
	
	// Verify the user
	$user_ok = evalLoggedUser($db,$log_id,$log_username);
	if($user_ok == true){
		// Update their lastlogin datetime field
		$sql = "UPDATE users_table SET lastlogin = now() WHERE id = ? LIMIT 1";
		$stmt = $db->stmt_init();
		$stmt = $db->prepare($sql);
		$stmt->bind_param("s", $log_id);
		$stmt->execute();
		$stmt->close();
	}
}
?>