<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<title>Register</title>
<h1>Register</h1>
<?php
if (isset($_POST["register"])) {
    $email = null;
    $password = null;
    $confirm = null;
    $username = null;
    $first_name = null;
    $last_name = null;
    if (isset($_POST["email"])) {
        $email = $_POST["email"];
    }
    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }
    if (isset($_POST["confirm"])) {
        $confirm = $_POST["confirm"];
    }
    if (isset($_POST["username"])) {
        $username = $_POST["username"];
    }
    if (isset($_POST["first"])) {
        $first_name = $_POST["first"];
    }
    if (isset($_POST["last"])) {
        $last_name = $_POST["last"];
    }
    $isValid = true;
    //check if passwords match on the server side
    if ($password == $confirm) {
        //echo "Passwords match <br>";
    }
    else {
        flash("Passwords don't match");
        $isValid = false;
    }
    if (!isset($email) || !isset($username) || !isset($password) || !isset($confirm) || !isset($first_name) || !isset($last_name)) {
        $isValid = false;
    }
    if (!strpos($email, "@")) {
        $isValid = false;
        flash("Invalid email");
    }
    //TODO other validation as desired, remember this is the last line of defense
    if ($isValid) {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $db = getDB();
        if (isset($db)) {
            //here we'll use placeholders to let PDO map and sanitize our data
            $stmt = $db->prepare("INSERT INTO Users(email, username, first_name, last_name, password) VALUES(:email, :username, :first, :last, :password)");
            //here's the data map for the parameter to data
            $params = array(":email" => $email, ":username" => $username, ":first" => $first_name, ":last" => $last_name, ":password" => $hash);
            $r = $stmt->execute($params);
            //let's just see what's returned
            //echo "db returned: " . var_export($r, true);
            $e = $stmt->errorInfo();
            if ($e[0] == "00000") {
                flash("Welcome! Successfully registered. Please login.");
            }
            else {
                if ($e[0] == "23000") {//code for duplicate entry
                    flash("Username or email already exists.");
                }
                else {
                    //echo "uh oh something went wrong: " . var_export($e, true);
                    flash("An error occured, please try again");
                }
            }
        }
    }
    else {
        flash("There was a validation issue");
    }
}
//safety measure to prevent php warnings
if (!isset($email)) {
    $email = "";
}
if (!isset($username)) {
    $username = "";
}
if (!isset($first_name)) {
    $first_name = "";
}
if (!isset($last_name)) {
    $last_name = "";
}
?>
<form method="POST">
  <p>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required value="<?php safer_echo($email); ?>"/>
  </p>
  <p>
    <label for="user">Username:</label>
    <input type="text" id="user" name="username" required maxlength="60" value="<?php safer_echo($username); ?>"/>
  </p>
    <p>
        <label for="first">First Name:</label>
        <input type="text" id="first" name="first" required maxlength="30" value="<?php safer_echo($first_name); ?>"/>
    </p>
    <p>
        <label for="last">Last Name:</label>
        <input type="text" id="last" name="last" required maxlength="30" value="<?php safer_echo($last_name); ?>"/>
    </p>
  <p>
    <label for="p1">Password:</label>
    <input type="password" id="p1" name="password" maxlength="60" required/>
  </p>
  <p>
    <label for="p2">Confirm Password:</label>
    <input type="password" id="p2" name="confirm" required/>
  </p>
    <input type="submit" name="register" value="Register"/>
</form>
<?php require(__DIR__ . "/partials/flash.php");
