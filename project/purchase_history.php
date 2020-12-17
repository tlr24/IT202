<?php require_once(__DIR__ . "/partials/nav.php"); ?>


<h1>Purchase History</h1>
<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();
$categories = getCategories();
$user_query = "SELECT o.id, o.user_id, o.created, oi.product_id, oi.quantity, oi.unit_price, (oi.unit_price * oi.quantity) as sub FROM Orders as o JOIN OrderItems as oi on oi.order_id = o.id LEFT JOIN Products as p on p.id = oi.product_id WHERE o.user_id = :id";
$admin_query = "SELECT o.id, o.user_id, o.created, oi.product_id, oi.quantity, oi.unit_price, (oi.unit_price * oi.quantity) as sub FROM Orders as o JOIN OrderItems as oi on oi.order_id = o.id LEFT JOIN Products as p on p.id = oi.product_id";
$limit_query = " LIMIT 10";
$stmt = $db->prepare(has_role("Admin")?$admin_query.$limit_query:$user_query.$limit_query);
$stmt->execute([":id"=>get_user_id()]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (isset($_POST["search"])) {
    $category = null;
    $start = null;
    $end = null;
    $isValid = true;
    if (!isset($_POST["category"])) {
        $isValid = false;
    }
    // if one of the dates are entered but the other isn't, it's invalid
    if ((!isset($_POST["start"]) && isset($_POST["end"])) || (isset($_POST["start"]) && !isset($_POST["end"]))) {
        flash("Please enter start and end date");
        $isValid = false;
    }
    else {
        $category = $_POST["category"];
        $start = $_POST["start"];
        $end = $_POST["end"];
    }

    if ($isValid) {
        if ($start && $end) {
            $between_query = " oi.created BETWEEN DATE('". $start. "') AND DATE('". $end . "') ";
            if ($category == "") {
                $stmt = $db->prepare(has_role("Admin")?$admin_query." WHERE ".$between_query." LIMIT 10":$user_query." AND ".$between_query." LIMIT 10");
                $params = has_role("Admin")?[]:[":id"=>get_user_id()];
            }
            else {
                $stmt = $db->prepare(has_role("Admin")?$admin_query." WHERE category=:category AND " . $between_query."LIMIT 10":$user_query." AND category=:category AND ".$between_query. "LIMIT 10");
                $params = has_role("Admin")?[":category"=>$category]:[":id"=>get_user_id(), ":category"=>$category];
            }
            $stmt->execute($params);
        }
        else {
            if ($category == "") {
                $stmt = $db->prepare(has_role("Admin")?$admin_query." LIMIT 10":$user_query." LIMIT 10");
                $params = has_role("Admin")?[]:[":id"=>get_user_id()];
            }
            else {
                $stmt = $db->prepare(has_role("Admin")?$admin_query." WHERE category=:category LIMIT 10":$user_query." AND category=:category LIMIT 10");
                $params = has_role("Admin")?[":category"=>$category]:[":id"=>get_user_id(), ":category"=>$category];
            }
            $stmt->execute($params);
        }
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


?>

<form method="POST">
    <label>Category:</label>
    <select name="category" value="" >
        <option value="">None</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php safer_echo($cat["category"]); ?>"><?php safer_echo($cat["category"]); ?></option>
        <?php endforeach; ?>
    </select>
    <div>
        <label>Date Range:</label>
        <input type="date" name="start">
        <input type="date" name="end">
    </div>
    <input type="submit" value="Search" name="search"/>
</form>

<div class="container-fluid">
    <div class="list-group">
    <?php if(isset($result) && !empty($result)): ?>
        <?php foreach($result as $r): ?>
            <?php $user_id = $r["user_id"]; ?>
            <?php $profile_link = "profile.php?id=" . $user_id;?>
            <div class="list-group-item">
                <h3><b><?php safer_echo(getProductName($r["product_id"])); ?></b></h3>
                <?php if(has_role("Admin")):?><div><b>User: </b><a href=<?php echo $profile_link?>><?php echo get_username_from_id($r["user_id"]);?></a></div><?php endif; ?>
                <button type="button" onClick="document.location.href='view_product.php?id=<?php safer_echo($r["product_id"]); ?>'">View</button>
                <div><b>Date: </b><?php safer_echo($r["created"]); ?></div>
                <div><b>Order: </b>#<?php safer_echo($r["id"]); ?></div>
                <div><b>Price: </b>$<?php safer_echo($r["unit_price"]); ?></div>
                <div><b>Quantity: </b><?php safer_echo($r["quantity"]); ?></div>
                <div><b>Subtotal: </b>$<?php safer_echo($r["sub"]); ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No purchases yet.</p>
    <?php endif; ?>
    </div>
</div>
