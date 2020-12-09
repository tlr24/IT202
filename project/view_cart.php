<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();
$total = 0;

if(isset($_POST["delete_all"])){
    $stmt = $db->prepare("DELETE FROM Cart where user_id = :uid");
    $r = $stmt->execute([":uid"=>get_user_id()]);
    if($r){
        flash("Deleted all items from cart", "success");
    }
}
if(isset($_POST["delete"])){
    $stmt = $db->prepare("DELETE FROM Cart where id = :id AND user_id = :uid");
    $r = $stmt->execute([":id"=>$_POST["cartId"], ":uid"=>get_user_id()]);
    if($r){
        flash("Deleted item from cart", "success");
    }
}
if(isset($_POST["update"])){
    if ($_POST["quantity"] >= '0') {
        if ($_POST["quantity"] == '0') {
            $stmt = $db->prepare("DELETE FROM Cart where id = :id AND user_id = :uid");
            $r = $stmt->execute([":id"=>$_POST["cartId"], ":uid"=>get_user_id()]);
        }
        else {
            $stmt = $db->prepare("UPDATE Cart set quantity = :q where id = :id AND user_id = :uid");
            $r = $stmt->execute([":id"=>$_POST["cartId"], ":q"=>$_POST["quantity"], ":uid"=>get_user_id()]);
        }
        if($r){
            flash("Updated quantity", "success");
        }
    }
    else {
        flash("Invalid quantity");
    }

}


$stmt = $db->prepare("SELECT c.id, p.name, c.price, c.quantity, (c.price * c.quantity) as sub, c.product_id from Cart as c JOIN Products as p on c.product_id = p.id where c.user_id = :id");
$stmt->execute([":id"=>get_user_id()]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="container-fluid">
    <h1>Cart</h1>
    <div class="list-group">
<?php if($results && count($results) > 0):?>
<div>
    <input type="submit" class="btn btn-danger" name="delete_all" value="Delete All Items" form="form"/>
    <input type="submit" class="btn btn-danger" name="checkout" value="Checkout" onclick="document.location.href='checkout.php'"/>
</div>
    <?php foreach($results as $r):?>
        <div class="list-group-item">
            <form method="POST" id="form">
                <div class="row">
                    <div class="col">
                        <b><?php echo $r["name"];?></b>
                    </div>
                    <div class="col">
                        $<?php echo number_format($r["price"], 2);?>
                        <button type="button" onClick="document.location.href='view_product.php?id=<?php safer_echo($r['product_id']); ?>'">View</button>
                    </div>
                    <div class="col">
                        <b>Quantity:</b>
                        <input type="number" min="0" name="quantity" step="1" value="<?php echo $r["quantity"];?>"/>
                        <input type="hidden" name="cartId" value="<?php echo $r["id"];?>"/>
                    </div>
                    <div class="col">
                        <b>Subtotal: </b>
                        $<?php echo number_format($r["sub"], 2);?>
                        <?php $total += floatval($r["sub"]);?>
                    </div>
                    <div class="col">
                        <input type="hidden" name="cartId" value="<?php echo $r["id"];?>"/>
                        <input type="submit" class="btn btn-success" name="update" value="Update"/>
                        <input type="submit" class="btn btn-danger" name="delete" value="Delete"/>

            </form>
        </div>
        </div>
        </div>
    <?php endforeach;?>
    <h2><b>Total: $</b><?php echo number_format($total, 2);?></h2>
<?php else:?>
    <div class="list-group-item">
        No items in cart
    </div>
<?php endif;?>
    </div>
    </div>
<?php require(__DIR__ . "/partials/flash.php");