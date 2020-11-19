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
    $stmt = $db->prepare("SELECT Products.id,name,quantity,price,description, user_id, Users.username FROM Products as Products JOIN Users on Products.user_id = Users.id where Products.id = :id");
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
                <div><b>Quantity: </b><?php safer_echo($result["quantity"]); ?></div>
                <div><b>Price: $</b><?php safer_echo($result["price"]); ?></div>
                <div><b>Description: </b><?php safer_echo($result["description"]); ?></div>
                <?php if (has_role("Admin")): ?>
                <div>Owned by: <?php safer_echo($result["username"]); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
