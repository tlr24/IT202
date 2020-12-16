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
// pulling all of the reviews
$per_page = 10;

$db = getDB();
$query = "SELECT count(*) as total from Ratings as r LEFT JOIN Products as p on p.id = r.product_id where r.product_id = :id";
$params = [":id"=>$id];
paginate($query, $params, $per_page);
//$stmt = $db->prepare("SELECT u.username, r.user_id, r.rating, r.comment FROM Ratings as r JOIN Users as u on u.id = r.user_id WHERE product_id = :pid LIMIT 10");
//$r = $stmt->execute(["pid" => $id]);
//$product_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db->prepare("SELECT u.username, r.user_id, r.rating, r.comment FROM Ratings as r JOIN Users as u on u.id = r.user_id WHERE product_id = :pid LIMIT :offset, :count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":pid", $id);
$stmt->execute();
$e = $stmt->errorInfo();
if($e[0] != "00000"){
    flash(var_export($e, true), "alert");
}
$product_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT ROUND(AVG(rating), 2) as average FROM Ratings WHERE product_id = :pid");
$r = $stmt->execute(["pid" => $id]);
$result1 = $stmt->fetch(PDO::FETCH_ASSOC);
$average_rating = $result1["average"];

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
        </div>
    </div>
    <div>
        <h2>Reviews</h2>
        <?php if (isset($product_ratings) && !empty($product_ratings)): ?>
            <p><b><?php echo $average_rating; ?> out of 5 stars</b></p>
            <div class="list-group">
            <?php foreach ($product_ratings as $rate): ?>
                <div class="list-group-item">
                    <div class="card">
                        <div class="card-title">
                            <p><b>User: </b><?php safer_echo($rate["username"]); ?></p>
                        </div>
                        <div class="card-body">
                            <div><b>Rating: </b><?php safer_echo($rate["rating"]); ?> stars</div>
                            <div><b>Comment: </b><?php safer_echo($rate["comment"]); ?></div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
            </div>
        </div>
        <?php include(__DIR__."/partials/pagination.php");?>
        </div>
        <?php else: ?>
            <p>No reviews yet.</p>
        <?php endif; ?>
    </div>
    <div>
        <form method="POST">
            <h2>Write a customer review</h2>
            <select name="rating" >
                <option value="1">1 star</option>
                <option value="2">2 stars</option>
                <option value="3">3 stars</option>
                <option value="4">4 stars</option>
                <option value="5">5 stars</option>
            </select>
            <input name="comment" value="" maxlength="120" placeholder ="Enter product review..."/>
            <input type="submit" value="Submit" name="rate"/>
        </form>
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
    if ($rating != "1" && $rating != "2" && $rating != "3" && $rating != "4" && $rating != "5") {
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
        $stmt = $db->prepare("INSERT into Ratings (product_id, user_id, rating, comment) VALUES (:pid, :uid, :rating, :comment) ON DUPLICATE KEY UPDATE rating = :rating, comment = :comment");
        $r = $stmt->execute([":pid" => $_GET["id"], ":uid" => get_user_id(), ":rating" => $rating, ":comment" => $comment]);
        if ($r) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
            flash("There was a problem submitting review");
        }
    }

}
?>

<?php require(__DIR__ . "/partials/flash.php");
