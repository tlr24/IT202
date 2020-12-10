<?php require_once(__DIR__ . "/partials/nav.php"); ?>


<h1>Purchase History</h1>
<?php
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();
$user_query = "SELECT o.user_id, o.created, oi.product_id, oi.quantity, oi.unit_price, (oi.unit_price * oi.quantity) as sub FROM Orders as o JOIN OrderItems as oi on oi.order_id = o.id where o.user_id = :id LIMIT 10";
$admin_query = "SELECT o.user_id, o.created, oi.product_id, oi.quantity, oi.unit_price, (oi.unit_price * oi.quantity) as sub FROM Orders as o JOIN OrderItems as oi on oi.order_id = o.id where o.user_id = :id LIMIT 10";
$stmt = $db->prepare($user_query);
$stmt->execute([":id"=>get_user_id()]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
//$lookup_query = (has_role("Admin"))?"SELECT id,name,quantity,price,description,visibility,user_id from Products WHERE ":;
?>

<div class="container-fluid">
    <div class="list-group">
    <?php if(isset($result) && !empty($result)): ?>
        <?php foreach($result as $r): ?>
            <div class="list-group-item">
                <h3><b><?php safer_echo(getProductName($r["product_id"])); ?></b></h3>
                <div><b>Date: </b><?php safer_echo($r["created"]); ?></div>
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
