<?php
include_once("check_login_status.php");  //Checks to see if user is logged in. If yes, they continue into the website.

if($user_ok == true){
	header("location: main_page.php?id=".$_SESSION["userid"]);
    exit();
}
//change ajax post url
//add timer javascript
//add token to ajax post
$salt = "W72vwKYasSa40832";
$timestamp = time();
$tk = str_shuffle(md5(uniqid().md5($salt)));

//Depending on your hashing method, you may want to filter certain characters on $tk to avoid ajax issues... like + characters
$tk = preg_replace('#[^a-z0-9.-]#i','',$tk);
$ses_array = array("tm" => $timestamp, "tk" => $tk);
if(!isset($_SESSION['login'])){
	$_SESSION['login'] = $ses_array;
}else{
	unset($_SESSION['login']);
	$_SESSION['login'] = $ses_array;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" http-equiv="encoding">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Developed By M Abdur Rokib Promy">
    <meta name="author" content="cosmic">
    <meta name="keywords" content="Bootstrap 3, Template, Theme, Responsive, Corporate, Business">

	<!--Bootstrap -->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <title>
      Login 
    </title>
	
	<script src="js/main.js"></script>
	<script src="js/ajax.js"></script>
	<script>
		var startTime =new Date().valueOf();
		function emptyElement(x){
			_(x).innerHTML = "";
		}
		function login(){			
			//Make sure time has not expired
			var postTime = new Date().valueOf();
			var totalTime = Math.ceil((postTime - startTime)/1000);
			
			//If 5 mins has passed, make the user refresh.
			//Shave off a few seconds for time lost in the page load 300 -> 295
			if(totalTime > 295){
				_("loginbtn").style.display = "none";
				_("email").style.display = "none";
				_("password").style.display = "none";
				_("status").innerHTML = '<strong style="color:#F00;">You have timed out, please refesh this page.</strong>';
				return false;
			}		
			var e = _("email").value;
			var p = _("password").value;
			if(e == "" || p == ""){
				_("status").innerHTML = "Fill out all of the form data";
			} else {
				_("loginbtn").style.display = "none";
				_("status").innerHTML = 'please wait ...';
				var ajax = ajaxObj("POST", "login_parse.php");
				ajax.onreadystatechange = function() {
					if(ajaxReturn(ajax) == true) {
						if(ajax.responseText == "login_failed"){
							_("status").innerHTML = "Login unsuccessful, please try again.";
							_("loginbtn").style.display = "block";
						} else {
							window.location.href = "main_page.php?id="+ajax.responseText;  //Login is good, proceed to main page.
						}
					}
				}
				ajax.send("e="+e+"&p="+p+"&t=<?php echo $_SESSION['login']['tk']; ?>");
			}
		}
	</script>
</head>

<body>
	<div class="container">	
		<div class="col-md-5">       
			<form action="login.php" onsubmit="return false;">
				<h2>Login</h2>
				<div class="login-wrap">
					<input id="email" type="text" class="form-control" placeholder="Email" onfocus="emptyElement('status')" maxlength="88">
					<input id="password" type="password" class="form-control" placeholder="Password" onfocus="emptyElement('status')" maxlength="100">
					<button id="loginbtn" onclick="login()" class="btn btn-lg btn-login btn-block">Sign in</button>
					<p id="status"></p>              
				</div>             
			</form>
		</div>
	</div>
</body>
</html>