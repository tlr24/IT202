<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
    //get products for dropdown
    $db = getDB();
    $stmt = $db->prepare("SELECT id,name,price from Products LIMIT 10");
    $r = $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php
function OnSelectionChange() {
    echo("OK IT WORKS");
}
?>
    <h3>Create Cart</h3>
    <form method="POST">
        <label>Product</label>
        <select name="product" value="<?php echo $r["name"];?>" >
            <?php foreach ($products as $item): ?>
                <option value="<?php safer_echo($item["id"]); ?>"><?php safer_echo($item["name"]); ?> ($<?php safer_echo($item["price"]);?>)</option>
            <?php endforeach; ?>
        </select>
        <label>Quantity</label>
        <input type="number" min="1" name="quantity"/>
        <input type="submit" name="save" value="Create"/>
    </form>
<?php
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $name = $_POST["product"];
    $quantity = $_POST["quantity"];
    $price = getPrice($name);
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Cart (product_id, quantity, price, user_id) VALUES(:name, :quantity, :price,:user)");
    $r = $stmt->execute([
        ":name" => $name,
        ":quantity" => $quantity,
        ":price" => $price,
        ":user" => $user
    ]);
    if ($r) {
        flash("Created successfully with id: " . $db->lastInsertId());
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php require(__DIR__ . "/partials/flash.php");
