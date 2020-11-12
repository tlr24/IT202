<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//saving
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $product = $_POST["product"];
    if ($product <= 0) {
        $product = null;
    }
    $quantity = $_POST["quantity"];
    $price = getPrice($product);
    $user = get_user_id();
    $db = getDB();
    if (isset($id)) {
        $stmt = $db->prepare("UPDATE Cart set product_id=:product, user_id=:user, quantity=:quantity, price=:price where id=:id");
        $r = $stmt->execute([
            ":product" => $product,
            ":user" => $user,
            ":quantity" => $quantity,
            ":price" => $price,
            ":id" => $id
        ]);
        if ($r) {
            flash("Updated successfully with id: " . $id);
        }
        else {
            $e = $stmt->errorInfo();
            flash("Error updating: " . var_export($e, true));
        }
    }
    else {
        flash("ID isn't set, we need an ID in order to update");
    }
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Cart where id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
//get products for dropdown
$db = getDB();
$stmt = $db->prepare("SELECT id,name,price from Products LIMIT 10");
$r = $stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <h3>Edit Cart</h3>
    <form method="POST">
        <p>
            <label>Product</label>
            <select name="product" value="<?php echo $result["id"];?>" >
                <?php foreach ($products as $item): ?>
        <p>($item["id"] == $result["product_id"])</p>
                    <option value="<?php safer_echo($item["id"]); ?>" <?php echo ($item["id"] == $result["product_id"]) ? 'selected="selected"' : 'selected=""';?>>
                        <?php safer_echo($item["name"]); ?> ($<?php safer_echo($item["price"]);?>)</option>
                <?php endforeach; ?>
            </select>
            <label>Quantity</label>
            <input type="number" min="1" name="quantity" value="<?php echo $result["quantity"]; ?>"/>
        </p>

        <input type="submit" name="save" value="Update"/>
    </form>


<?php require(__DIR__ . "/partials/flash.php");
