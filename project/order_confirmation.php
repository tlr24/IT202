<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
// getting the order id
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT o.total_price,o.address,o.payment_method,o.user_id,oi.product_id, oi.quantity, oi.unit_price, (oi.quantity * oi.unit_price) as sub FROM Orders as o JOIN OrderItems as oi on oi.order_id = o.id where o.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>


<?php if (isset($result) && !empty($result)): ?>
    <h1>Order Confirmation</h1>
    <p><b>User: </b><?php safer_echo(get_username($result["0"]["user_id"])); ?></p>
    <p><b>Address: </b><?php safer_echo($result["0"]["address"]); ?></p>
    <p><b>Payment Method: </b><?php safer_echo($result["0"]["payment_method"]); ?></p>
    <h3>Purchased Items:</h3>
    <?php foreach($result as $r): ?>
    <div class="card">
        <div class="card-title">
            <h4><?php safer_echo(getProductName($r["product_id"])); ?></h4>
        </div>
        <div class="card-body">
            <div>
                <div><b>Price: </b>$<?php safer_echo($r["unit_price"]); ?></div>
                <div><b>Quantity: </b><?php safer_echo($r["quantity"]); ?></div>
                <div><b>Subtotal: </b>$<?php safer_echo($r["sub"]); ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<h2>Total: $<?php safer_echo($result["0"]["total_price"]); ?></h2>

<?php require(__DIR__ . "/partials/flash.php");
