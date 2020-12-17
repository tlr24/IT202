<?php require_once(__DIR__ . "/partials/nav.php"); ?>


<h1>Purchase History</h1>
<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();
$per_page = 10;
$categories = getCategories();
$user_query = "SELECT o.id, o.user_id, o.created, oi.product_id, oi.quantity, oi.unit_price, (oi.unit_price * oi.quantity) as sub FROM Orders as o JOIN OrderItems as oi on oi.order_id = o.id LEFT JOIN Products as p on p.id = oi.product_id WHERE o.user_id = :id";
$admin_query = "SELECT o.id, o.user_id, o.created, oi.product_id, oi.quantity, oi.unit_price, (oi.unit_price * oi.quantity) as sub FROM Orders as o JOIN OrderItems as oi on oi.order_id = o.id LEFT JOIN Products as p on p.id = oi.product_id";
$limit_query = " LIMIT :offset, :count";
$pag_query = (has_role("Admin"))?"SELECT count(*) as total from OrdersItems ":"SELECT count(*) as total from OrderItems as oi JOIN Orders as o WHERE user_id = :id AND o.id = oi.order_id ";
$params = (has_role("Admin"))?[]:[":id"=>get_user_id()];
$stmt = $db->prepare(has_role("Admin")?$admin_query.$limit_query:$user_query.$limit_query);
$stmt->execute([":id"=>get_user_id()]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (isset($_POST["search"])) {
    $category = null;
    $start = null;
    $end = null;
    $isValid = true;
    // if one of the dates are entered but the other isn't, it's invalid
    if (($_POST["start"]=="" && $_POST["end"]!="") || ($_POST["start"]!="" && $_POST["end"]=="")) {
        //flash("Please enter start and end date");
        $isValid = false;
    }
    else {
        $category = $_POST["category"];
        $start = $_POST["start"];
        $end = $_POST["end"];
    }

    if ($isValid) {
        if ($start && $end) {
            $between_query = " oi.created BETWEEN DATE('". $start. "') AND DATE('". $end . "')";
            if ($category == "") {
                $stmt = $db->prepare(has_role("Admin")?$admin_query." WHERE ".$between_query.$limit_query:$user_query." AND ".$between_query.$limit_query);
                $params = has_role("Admin")?[]:[":id"=>get_user_id()];
                $pag_query .= $between_query;
            }
            else {
                $stmt = $db->prepare(has_role("Admin")?$admin_query." WHERE category=:category AND " . $between_query.$limit_query:$user_query." AND category=:category AND ".$between_query.$limit_query);
                $params = has_role("Admin")?[":category"=>$category]:[":id"=>get_user_id(), ":category"=>$category];
                $stmt->bindValue(":category", $category);
                $pag_query .= " category=:category AND ".$between_query;
            }
            if (!has_role("Admin")) {
                $stmt->bindValue(":id", get_user_id());
            }
            paginate($pag_query, $params, $per_page);
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->execute($params);
        }
        else {
            if ($category == "") {
                $stmt = $db->prepare(has_role("Admin")?$admin_query." LIMIT 10":$user_query." LIMIT 10");
                $params = has_role("Admin")?[]:[":id"=>get_user_id()];
            }
            else {
                $stmt = $db->prepare(has_role("Admin")?$admin_query." WHERE category=:category ".$limit_query:$user_query." AND category=:category ".$limit_query);
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
        <label>Start:</label>
        <input type="date" name="start">
        <label>End:</label>
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
