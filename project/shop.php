<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<h1>Shop</h1>
<?php
$query = "";
$results = [];
$categories = getCategories();
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
$lookup_query = (has_role("Admin"))?"SELECT id,name,quantity,price,description,visibility,user_id from Products WHERE ":"SELECT id,name,quantity,price,description,visibility,user_id from Products WHERE visibility = '1' AND quantity > '0' AND ";
$asc_query = "ORDER BY price ASC LIMIT 10";
$desc_query = "ORDER BY price DESC LIMIT 10";
if (empty($query)) { // show all products initially
    $db = getDB();
    if (isset($_POST["category"])) {
        $curr_category = $_POST["category"];
        if ($curr_category != "") { // if the query is empty but a category is chosen
            switch ($_POST["sort"]) {
                case "asc":
                    $stmt = $db->prepare($lookup_query . "category=:category " . $asc_query);
                    break;
                case "desc":
                    $stmt = $db->prepare($lookup_query . "category=:category " . $desc_query);
                    break;
                default:
                    $stmt = $db->prepare($lookup_query . "category=:category ");
                    break;
            }
            $r = $stmt->execute([":category" => $curr_category]);
        }
        else { // if the query is empty and category is not chosen
            switch ($_POST["sort"]) {
                case "asc":
                    $stmt = $db->prepare($lookup_query . "name like :q " . $asc_query);
                    break;
                case "desc":
                    $stmt = $db->prepare($lookup_query . "name like :q " . $desc_query);
                    break;
                default:
                    $stmt = $db->prepare($lookup_query . "name like :q ");
                    break;
            }
            $r = $stmt->execute([":q" => "%$query%"]);
        }
    }
    else { // if query is empty and category isn't set
        if (isset($_POST["sort"])) { // if price sort is set
            switch ($_POST["sort"]) {
                case "asc":
                    $stmt = $db->prepare($lookup_query . "name like :q " . $asc_query);
                    break;
                case "desc":
                    $stmt = $db->prepare($lookup_query . "name like :q " . $desc_query);
                    break;
                default:
                    $stmt = $db->prepare($lookup_query . "name like :q ");
                    break;
            }
        } // if price sort isn't set
        else {
            $stmt = $db->prepare($lookup_query . "name like :q ");
        }
        $r = $stmt->execute([":q" => "%$query%"]);
    }

    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}
else if (isset($_POST["search"])) { // if search is filled out
    $db = getDB();
    $curr_category = $_POST["category"];
    if ($curr_category == "") { // if no category is chosen
        switch ($_POST["sort"]) {
            case "asc":
                $stmt = $db->prepare($lookup_query . "name like :q " . $asc_query);
                break;
            case "desc":
                $stmt = $db->prepare($lookup_query . "name like :q " . $desc_query);
                break;
            default:
                $stmt = $db->prepare($lookup_query . "name like :q ");
                break;
        }
        //$stmt = $db->prepare($lookup_query . "name like :q " . $asc_query);
        $r = $stmt->execute([":q" => "%$query%"]);
    }
    else { // if a category is picked
        switch ($_POST["sort"]) {
            case "asc":
                $stmt = $db->prepare($lookup_query . "name like :q AND category=:category " . $asc_query);
                break;
            case "desc":
                $stmt = $db->prepare($lookup_query . "name like :q AND category=:category " . $desc_query);
                break;
            default:
                $stmt = $db->prepare($lookup_query . "name like :q AND category=:category ");
                break;
        }
        //$stmt = $db->prepare($lookup_query . "name like :q AND category=:category " . $asc_query);
        $r = $stmt->execute([":q" => "%$query%", ":category" => $curr_category]);
    }
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}
?>
    <form method="POST">
        <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
        <label>Category:</label>
        <select name="category" value="" >
            <option value="">None</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php safer_echo($cat["category"]); ?>"><?php safer_echo($cat["category"]); ?></option>
            <?php endforeach; ?>
        </select>
        <div>
            <label>Sort by price:</label>
            <select name="sort" value="">
                <option value ="">Best Match</option>
                <option value ="asc">Low to High</option>
                <option value ="desc">High to Low</option>
            </select>
        </div>
        <input type="submit" value="Search" name="search"/>
    </form>

<title>Shop</title>
    <div class="results"></div>
<?php if (count($results) > 0): ?>
    <div class="list-group">
        <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div class="card">
                        <form method = "POST">
                        <div class="card-title">
                            <div><b><?php safer_echo($r["name"]); ?></b></div>
                            <div>Price:<div>$<?php safer_echo($r["price"]); ?></div></div>
                            <input type="hidden" name="id" value="<?php safer_echo($r["id"]); ?>">
                            <input type="hidden" name="name" value="<?php safer_echo($r["name"]); ?>">
                        </div>
                        <div>Quantity:</div>
                        <div class="quantity">
                            <input type="number" min="1" max="<?php safer_echo($r["quantity"])?>" step="1" name="quantity" value="1"/>
                        </div>
                        <div class="card-body">
                            <button type="sumbit" class="btn btn-primary btn-lg" name="save">Add to Cart
                            </button>
                        </div>
                        </form>
                    </div>
                    <div>
                        <?php if (has_role("Admin")): ?>
                        <button type="button" onClick="document.location.href='edit_product.php?id=<?php safer_echo($r['id']); ?>'">Edit</button>
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

        <?php
            if (isset($_POST["save"])) {
                if (is_logged_in()) {
                    //TODO add proper validation/checks
                    $name = $_POST["id"];
                    $quantity = $_POST["quantity"];
                    $price = getPrice($name);
                    $user = get_user_id();
                    $db = getDB();
                    $stmt = $db->prepare("INSERT INTO Cart (product_id, quantity, price, user_id) VALUES(:name, :quantity, :price,:user) ON DUPLICATE KEY UPDATE quantity = quantity + :quantity");
                    $r = $stmt->execute([
                        ":name" => $name,
                        ":quantity" => $quantity,
                        ":price" => $price,
                        ":user" => $user
                    ]);
                    if ($r) {
                        flash($_POST["name"] . " was successfully added to cart");
                    }
                    else {
                        $e = $stmt->errorInfo();
                        if ($e[0] == 23000) {
                            flash("Item already in cart");
                        }
                        else {
                            flash("Error adding to cart: " . var_export($e, true));
                        }
                    }
                }
                else {
                    flash("Please login to add to cart.");
                }
            }

        ?>
</script>

<?php require(__DIR__ . "/partials/flash.php");