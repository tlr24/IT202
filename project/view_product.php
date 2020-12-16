<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>

<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Products.id,name,quantity,price,description,category,visibility,user_id, Users.username FROM Products as Products JOIN Users on Products.user_id = Users.id where Products.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>

<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <h1><?php safer_echo($result["name"]); ?></h1>
        </div>
        <div class="card-body">
            <div>
                <p>Information</p>
                <div><b>Price: $</b><?php safer_echo($result["price"]); ?></div>
                <div><b>Description: </b><?php safer_echo($result["description"]); ?></div>
                <div><b>Category: </b><?php $cat = ($result["category"] == "")?"None":$result["category"]; safer_echo($cat);?></div>
                <?php if (has_role("Admin")): ?>
                    <div><b>Quantity: </b><?php safer_echo($result["quantity"]); ?></div>
                    <div><b>Owned by: </b><?php safer_echo($result["username"]); ?></div>
                    <div><b>Visible: </b><?php $vis = ($result["visibility"] == "0")?"no":"yes"; safer_echo($vis);?></div>
                <?php endif; ?>
            </div>
            <div>
                <p>Ratings</p>
                <p><b>_ out of 5 stars</b></p>
            </div>
            <div>
                <form method="POST">
                    <p>Write a customer review</p>
                    <select name="rating" >
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                    <input name="comment" value="" maxlength="120"/>
                    <input type="submit" value="Submit" name="rate"/>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>


<?php
if (isset($_POST["rate"])) { // if ratings is filled out
    $db = getDB();
    $rating = null;
    $comment = null;
    if (isset($_POST["rating"])) {
        $rating = $_POST["rating"];
    }
    if (isset($_POST["comment"])) {
        $comment = $_POST["comment"];
    }
    $isValid = true;

    // Rating validation
    if (!$rating || !$comment) {
        $isValid = false;
        flash("Please finish your review");
    }
    if ($rating == "" || ($rating != "1" && $rating != "2" && $rating != "3" && $rating != "4" && $rating != "5")) {
        $isValid = false;
        flash("Please include a rating");
    }
    if (strlen($comment) >= 120) {
        $isValid = false;
        flash("Comment maximum is 120 characters");
    }
    $valid_stmt = $db->prepare("SELECT count(o.id) as amount from Orders as o JOIN OrderItems as oi on oi.order_id = o.id where o.user_id = :uid AND oi.product_id = :pid LIMIT 10");
    $r1 = $valid_stmt->execute([":pid" => $_GET["id"], ":uid" => get_user_id()]);
    if ($r1) {
        $result = $valid_stmt->fetch(PDO::FETCH_ASSOC);
        $amount_bought = $result["amount"];
        if ($amount_bought == "0") {
            $isValid = false;
            flash("You haven't purchased this item");
        }
    }
    else {
        $isValid = false;
    }

    if ($isValid) {
        $stmt = $db->prepare("INSERT into Ratings (product_id, user_id, rating, comment) VALUES (:pid, :uid, :rating, :comment)");
        $r = $stmt->execute([":pid" => $_GET["id"], ":uid" => get_user_id(), ":rating" => $rating, ":comment" => $comment]);
        if ($r) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
            flash("There was a problem submitting review");
            flash(var_dump($stmt->errorInfo()));
        }
    }

}
?>

<?php require(__DIR__ . "/partials/flash.php");
