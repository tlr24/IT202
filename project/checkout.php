<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();
$total = 0;


$stmt = $db->prepare("SELECT c.id, p.name, c.price, c.quantity, p.quantity as quan, (c.price * c.quantity) as sub, c.product_id from Cart as c JOIN Products as p on c.product_id = p.id where c.user_id = :id");
$stmt->execute([":id"=>get_user_id()]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="container-fluid">
    <h1>Checkout</h1>
<?php if($results && count($results) > 0):?>
    <h3>Order Information</h3>
    <form method="POST" form="form">
        <b>Address:</b>
        <p>
            <input name="street" placeholder="Street Address" form="form" value="<?php isset($_POST["street"])?safer_echo($_POST["street"]):"";?>">
            <input name="city" placeholder="City" form="form" value="<?php isset($_POST["city"])?safer_echo($_POST["city"]):"";?>">
            <input name="state" placeholder="State" form="form" value="<?php isset($_POST["state"])?safer_echo($_POST["state"]):"";?>">
            <input type="number" name="zipcode" placeholder="Zip Code" form="form" value="<?php isset($_POST["zipcode"])?safer_echo($_POST["zipcode"]):"";?>">
        </p>
        <b>Payment Method:</b>
        <select name="method" form="form">
            <option value="Mastercard">Mastercard</option>
            <option value="Visa">Visa</option>
            <option value="Discover">Discover</option>
        </select>
        <b>Pay Amount:</b>
        <input type="number" min="0" name="payment" step="0.01" value="0" form="form"/>
    </form>
    <h3>Order Summary</h3>
    <div class="list-group">
<?php endif; ?>
<?php if($results && count($results) > 0):?>
    <?php foreach($results as $r):?>
        <div class="list-group-item">
            <form method="POST" id="form">
                <div class="row">
                    <div class="col">
                        <b><?php echo $r["name"];?></b>
                    </div>
                    <div class="col">
                        $<?php echo number_format($r["price"], 2);?>
                        <button type="button" onClick="document.location.href='view_cart.php'">Update</button>
                    </div>
                    <div class="col">
                        <b>Quantity:</b>
                        <?php echo $r["quantity"];?>
                    </div>
                    <div class="col">
                        <b>Subtotal: </b>
                        $<?php echo number_format($r["sub"], 2);?>
                        <?php $total += floatval($r["sub"]);?>
                    </div>

            </form>
        </div>
        </div>
        </div>
    <?php endforeach;?>
    <h3><b>Total: $</b><?php echo number_format($total, 2);?></h3>
<?php else:?>
    <div class="list-group-item">
        No items in cart
    </div>
<?php endif;?>
    </div>
    </div>
    <input type="submit" class="btn btn-success" name="purchase" value="Submit Order" form="form"/>

<?php
if(isset($_POST["purchase"])){
    $street = null;
    $city = null;
    $state = null;
    $zipcode = null;
    if (isset($_POST["street"])) {
        $street = $_POST["street"];
    }
    if (isset($_POST["city"])) {
        $city = $_POST["city"];
    }
    if (isset($_POST["state"])) {
        $state = $_POST["state"];
    }
    if (isset($_POST["zipcode"])) {
        $zipcode = $_POST["zipcode"];
    }
    if (isset($_POST["method"])) {
        $method = $_POST["method"];
    }
    if (isset($_POST["payment"])) {
        $payment = $_POST["payment"];
    }
    $isValid = true;

    // Address validation
    if (!isset($street) || !isset($city) || !isset($state) || !isset($zipcode) || !isset($payment)) {
        $isValid = false;
        flash("Please all required information");
    }
    if (strlen($state) != 2) {
        $isValid = false;
        flash("Please enter a valid state (example format: NJ)");
    }
    if (!is_numeric($zipcode)) {
        $isValid = false;
        flash("Please enter a valid zipcode");
    }
    if (!is_numeric($payment) || $payment < $total) {
        $isValid = false;
        flash("Please enter a valid payment amount");
    }

    // once the validation is done
    if ($isValid) {
        $canPurchase = true;
        // check for product availability
        if ($results && count($results) > 0) {
            foreach ($results as $r) {
                if ($r["quan"] == 0) {
                    flash($r["name"] . " is currently out of stock. Please update the cart and try again.");
                    $canPurchase = false;
                }
                else if ($r["quantity"] > $r["quan"]) {
                    flash("Only " . $r["quan"] . " left of " . $r["name"] . ". Please update the quantity and try again.");
                    $canPurchase = false;
                }
                else {
                    //flash("Can purchase " . $r["name"]);
                }
            }
        }
        if ($canPurchase) {
            // place order in Orders table
            $full_address = $street . " " . $city . ", " . $state . " " . $zipcode;
            $stmt1 = $db->prepare("INSERT into Orders (total_price, address, payment_method, user_id) VALUES (:total_price, :address, :payment_method, :id)");
            $stmt1->execute([":total_price"=>$total,
                ":id"=>get_user_id(),
                ":address"=>$full_address,
                ":payment_method"=>$method
            ]);
            // place order items in OrderItems table
            $order_id = $db->lastInsertId();
            foreach ($results as $r) {
                $stmt2 = $db->prepare("INSERT into OrderItems (unit_price, quantity, order_id, product_id) VALUES (:unit_price, :quantity, :order_id, :product_id)");
                $stmt2->execute([":unit_price"=>$r["price"],
                    ":order_id"=>$order_id,
                    ":product_id"=>$r["product_id"],
                    ":quantity"=>$r["quantity"]
                ]);
                // update item quantity from Products table
                $stmt3 = $db->prepare("UPDATE Products set quantity = :quantity where id = :product_id");
                $stmt3->execute([":quantity"=>($r["quan"]-$r["quantity"]),
                    ":product_id"=>$r["product_id"]
                ]);
                if($r){
                    //flash("Successfully updated quantity");
                }
                else{
                    $e = $stmt3->errorInfo();
                    flash("Error purchasing products");
                }
            }
            // clear out the user's cart
            $stmt4 = $db->prepare("DELETE FROM Cart where user_id = :uid");
            $result = $stmt4->execute([":uid"=>get_user_id()]);
            if($result){
                //flash("Deleted all items from cart", "success");
            }
            flash("Successfully purchased");
            die(header("Location: order_confirmation.php?id=".$order_id));
        }

    }
}
?>

<?php require(__DIR__ . "/partials/flash.php");