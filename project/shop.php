<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//$balance = getBalance();
$cost = 0;
?>
<?php
$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (empty($query)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id,name,quantity,price,description,user_id from Products WHERE name like :q LIMIT 10");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}
?>

    <script>
        //php will exec first so just the value will be visible on js side
        let balance = <?php echo $balance;?>;
        let cost = <?php echo $cost;?>;

        function cart() {
            //todo client side balance check
            if (cost > balance) {
                alert("You can't afford this right now");
                return;
            }
            //https://www.w3schools.com/xml/ajax_xmlhttprequest_send.asp
            let xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    let json = JSON.parse(this.responseText);
                    if (json) {
                        if (json.status == 200) {
                            alert("Successfully added " + json.cart.name + " to cart");
                            location.reload();
                        } else {
                            alert(json.error);
                        }
                    }
                }
            };
            xhttp.open("POST", "<?php echo getURL("api/addToCart.php");?>", true);
            //this is required for post ajax calls to submit it as a form
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            //map any key/value data similar to query params
            xhttp.send(`id=${id}&qt=0`);

        }
    </script>
<title>Shop</title>
<h1>Shop</h1>
    <div class="results"></div>
<?php if (count($results) > 0): ?>
    <div class="list-group">
        <?php foreach ($results as $r): ?>
            <div class="list-group-item">
                <div class="card">
                    <div class="card-title">
                        <div><b><?php safer_echo($r["name"]); ?></b></div>
                        <div>Price:<div>$<?php safer_echo($r["price"]); ?></div></div>

                    </div>
                    <div>Quantity:</div>
                    <div class="quantity">
                        <input type="number" min="1" max="<?php safer_echo($r["quantity"])?>" step="1" name="quantity" value="1"/>
                    </div>
                    <div class="card-body">
                        <button type="button" onclick="addToCart(<?php $r ?>)" class="btn btn-primary btn-lg">Add to Cart
                        </button>
                    </div>
                </div>
                <div>
                    <?php if (has_role("Admin")): ?>
                    <button type="button" onClick="document.location.href='test/test_edit_products.php?id=<?php safer_echo($r['id']); ?>'">Edit</button>
                    <?php endif; ?>
                    <button type="button" onClick="document.location.href='view_product.php?id=<?php safer_echo($r['id']); ?>'">View</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No results</p>
<?php endif; ?>
    </div>

<script>
    function addToCart(<?php $r ?>) {
        <?php
            if (is_logged_in()) {
                //TODO add proper validation/checks
                $name = $r["id"];
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
                    flash("Successfully added to cart: " . $db->lastInsertId());
                }
                else {
                    $e = $stmt->errorInfo();
                    flash("Error adding to cart: " . var_export($e, true));
                }
            }
            else {
                flash("Please login to add to cart.");
            }
        ?>
    }
</script>

<?php require(__DIR__ . "/partials/flash.php");