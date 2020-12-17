<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<h1>Shop</h1>
<?php

$per_page = 10;

$db = getDB();
$pag_query = (has_role("Admin"))?"SELECT count(*) as total from Products WHERE ":"SELECT count(*) as total from Products WHERE visibility = '1' AND quantity > '0' AND ";
$query = "";
$results = [];
$categories = getCategories();
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
$lookup_query = (has_role("Admin"))?"SELECT id,name,quantity,price,description,visibility,user_id from Products WHERE ":"SELECT id,name,quantity,price,description,visibility,user_id from Products WHERE visibility = '1' AND quantity > '0' AND ";
$asc_query = "ORDER BY price ASC ";
$desc_query = "ORDER BY price DESC ";
$limit_query = "LIMIT :offset, :count";
$category = null;
$sort = null;
if (empty($query)) { // show all products initially
    $db = getDB();
    if (isset($_POST["category"])) {
        $curr_category = $_POST["category"];
        if ($curr_category != "") { // if the query is empty but a category is chosen
            switch ($_POST["sort"]) {
                case "asc":
                    $stmt = $db->prepare($lookup_query . "category=:category " . $asc_query . $limit_query);
                    break;
                case "desc":
                    $stmt = $db->prepare($lookup_query . "category=:category " . $desc_query . $limit_query);
                    break;
                default:
                    $stmt = $db->prepare($lookup_query . "category=:category " . $limit_query);
                    break;
            }
            $params = [":category" => $curr_category];
            $pag_query .= "category=:category ";
            paginate($pag_query, $params, $per_page);
            //$r = $stmt->execute([":category" => $curr_category]);
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":category", $curr_category);
            $r = $stmt->execute();
        }
        else { // if the query is empty and category is not chosen
            switch ($_POST["sort"]) {
                case "asc":
                    $stmt = $db->prepare($lookup_query . "name like :q " . $asc_query . $limit_query);
                    break;
                case "desc":
                    $stmt = $db->prepare($lookup_query . "name like :q " . $desc_query . $limit_query);
                    break;
                default:
                    $stmt = $db->prepare($lookup_query . "name like :q " . $limit_query);
                    break;
            }
            $params = [":q" => "%$query%"];
            $pag_query .= "name like :q ";
            paginate($pag_query, $params, $per_page);
            //$r = $stmt->execute([":q" => "%$query%"]);
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            $stmt->bindValue(":q", "%$query%");
            $r = $stmt->execute();
        }
    }
    else { // if query is empty and category isn't set
        if (isset($_POST["sort"])) { // if price sort is set
            switch ($_POST["sort"]) {
                case "asc":
                    $stmt = $db->prepare($lookup_query . "name like :q " . $asc_query . $limit_query);
                    break;
                case "desc":
                    $stmt = $db->prepare($lookup_query . "name like :q " . $desc_query . $limit_query);
                    break;
                default:
                    $stmt = $db->prepare($lookup_query . "name like :q " . $limit_query);
                    break;
            }
        } // if price sort isn't set
        else {
            $stmt = $db->prepare($lookup_query . "name like :q " . $limit_query);
        }
        $params = [":q" => "%$query%"];
        $pag_query .= "name like :q ";
        paginate($pag_query, $params, $per_page);
        //$r = $stmt->execute([":q" => "%$query%"]);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
        $stmt->bindValue(":q", "%$query%");
        $r = $stmt->execute();
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
                $stmt = $db->prepare($lookup_query . "name like :q " . $asc_query . $limit_query);
                break;
            case "desc":
                $stmt = $db->prepare($lookup_query . "name like :q " . $desc_query . $limit_query);
                break;
            default:
                $stmt = $db->prepare($lookup_query . "name like :q " . $limit_query);
                break;
        }
        $params = [":q" => "%$query%"];
        $pag_query .= "name like :q ";
        paginate($pag_query, $params, $per_page);
        //$stmt = $db->prepare($lookup_query . "name like :q " . $asc_query);
        //$r = $stmt->execute([":q" => "%$query%"]);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
        $stmt->bindValue(":q", "%$query%");
        $r = $stmt->execute();
    }
    else { // if a category is picked
        switch ($_POST["sort"]) {
            case "asc":
                $stmt = $db->prepare($lookup_query . "name like :q AND category=:category " . $asc_query . $limit_query);
                break;
            case "desc":
                $stmt = $db->prepare($lookup_query . "name like :q AND category=:category " . $desc_query . $limit_query);
                break;
            default:
                $stmt = $db->prepare($lookup_query . "name like :q AND category=:category " . $limit_query);
                break;
        }
        $params = [":q" => "%$query%", ":category" => $curr_category];
        $pag_query .= "name like :q  AND category=:category ";
        paginate($pag_query, $params, $per_page);
        //$stmt = $db->prepare($lookup_query . "name like :q AND category=:category " . $asc_query);
        //$r = $stmt->execute([":q" => "%$query%", ":category" => $curr_category]);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
        $stmt->bindValue(":q", "%$query%");
        $stmt->bindValue(":category", $curr_category);
        $r = $stmt->execute();
    }
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
    if (isset($_POST["category"])) {
        $category = $_POST["category"];
    }
    if (isset($_POST["sort"])) {
        $sort = $_POST["sort"];
    }
}
?>
    <form method="POST">
        <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
        <label>Category:</label>
        <select name="category" value="" >
            <option value="" <?php echo (isset($_POST["category"]))?(($_POST["category"]=="")?"selected":""):""?>>None</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php safer_echo($cat["category"]); ?>" <?php echo (isset($_POST["category"]))?(($_POST["category"]==$cat["category"])?"selected":""):""?>><?php safer_echo($cat["category"]); ?></option>
            <?php endforeach; ?>
        </select>
        <div>
            <label>Sort by price:</label>
            <select name="sort" value="">
                <option value ="" <?php echo (isset($_POST["sort"]))?(($_POST["sort"]=="")?"selected":""):""?>>Best Match</option>
                <option value ="asc" <?php echo (isset($_POST["sort"]))?(($_POST["sort"]=="asc")?"selected":""):""?>>Low to High</option>
                <option value ="desc" <?php echo (isset($_POST["sort"]))?(($_POST["sort"]=="desc")?"selected":""):""?>>High to Low</option>
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
    </div>
    <?php include(__DIR__."/partials/pagination.php");?>
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