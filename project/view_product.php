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
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>

<?php require(__DIR__ . "/partials/flash.php");
