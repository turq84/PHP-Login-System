# PHP-Login-System
PHP login system that takes user information from the users_table and verifies user input, allowing them to proceed past the login page. Prepared statements were used to prevent SQL injection. A continuation of the PHP registration system.
The login_parse.php file will create sessions and cookies for the logged in user.
The check_user_status.php file will see if the user is logged in, referring to their session/cookies, and if not, they can continue to login. If they are logged in, they will automatically proceed to the main page (past the login).
Bootstrap was used.

