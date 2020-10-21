<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
}
?>
<title>Home</title>
<h1><img src="sparkles.png" alt="Sparkles" width="30" height="30">Shop Simple<img src="sparkles.png" alt="Sparkles" width="30" height="30"></h1>
<p>Welcome <?php echo $email; ?></p>
<?php require(__DIR__ . "/partials/flash.php");
