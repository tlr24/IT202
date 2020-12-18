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
if (isset($_POST["quan"])) {
    $quan = $_POST["quan"];
}
else {
    $quan = "99999999";
}
$lookup_query = (has_role("Admin"))?"SELECT p.id,p.name,p.quantity,p.price,p.description,p.visibility,p.user_id,AVG(IFNULL(r.rating,0)) as rating from Products as p LEFT JOIN Ratings as r ON p.id = r.product_id WHERE ":"SELECT p.id,p.name,p.quantity,p.price,p.description,p.visibility,p.user_id,AVG(IFNULL(r.rating,0)) as rating from Products as p LEFT JOIN Ratings as r ON p.id = r.product_id WHERE visibility = '1' AND quantity > '0' AND ";
$asc_query = " ORDER BY price ASC ";
$desc_query = " ORDER BY price DESC ";
$limit_query = " LIMIT :offset, :count";
$groupby_query = " GROUP by p.id ";
$category = null;
$sort = null;
$quan = "99999999";
$sortedByPrice = false;
if (empty($query)) { // show all products initially
    if (isset($_POST["quan"])) {
        if ($_POST['quan'] == "") {
            $quan ='99999999';
        }
        else {
            $quan = $_POST["quan"];
        }
    }
    else {
        $quan = "99999999";
    }
    if (isset($_POST["category"])) {
        $curr_category = $_POST["category"];
        if ($curr_category != "") { // if the query is empty but a category is chosen
            $lookup_query .= has_role("Admin")?" p.quantity <=:quantity AND category=:category ".$groupby_query:"category=:category ".$groupby_query;
            switch ($_POST["sort"]) {
                case "asc":
                    $lookup_query .= $asc_query;
                    $sortedByPrice = true;
                    break;
                case "desc":
                    $lookup_query .= $desc_query;
                    $sortedByPrice = true;
                    break;
                default:
                    break;
            }
            switch ($_POST["rate"]) {
                case "asc":
                    $lookup_query .= ($sortedByPrice)?", rating asc ":" ORDER BY rating asc ";
                    break;
                case "desc":
                    $lookup_query .= ($sortedByPrice)?", rating desc ":" ORDER BY rating desc ";
                    break;
                default:
                    break;
            }
            $lookup_query .= $limit_query;
            $stmt = $db->prepare($lookup_query);
            $params = (has_role("Admin")?[":quantity"=> $quan, ":category" => $curr_category]:[":category" => $curr_category]);
            $pag_query .= has_role("Admin")?" p.quantity <=:quantity AND category=:category ":"category=:category ";
            paginate($pag_query, $params, $per_page);
            //$r = $stmt->execute([":category" => $curr_category]);
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            if (has_role("Admin")) {
                $stmt->bindValue(":quantity", $quan);
            }
            $stmt->bindValue(":category", $curr_category);
            $r = $stmt->execute();
        }
        else { // if the query is empty and category is not chosen
            $setup_query = has_role("Admin")?$lookup_query . " p.quantity <=:quantity AND name like :q ".$groupby_query:$lookup_query . " name like :q ".$groupby_query;
            switch ($_POST["sort"]) {
                case "asc":
                    $setup_query .= $asc_query;
                    $sortedByPrice = true;
                    break;
                case "desc":
                    $setup_query .= $desc_query;
                    $sortedByPrice = true;
                    break;
                default:
                    break;
            }
            switch ($_POST["rate"]) {
                case "asc":
                    $setup_query .= ($sortedByPrice)?", rating ASC ":" ORDER BY rating ASC ";
                    break;
                case "desc":
                    $setup_query .= ($sortedByPrice)?", rating desc ":" ORDER BY rating desc ";
                    break;
                default:
                    break;
            }
            $setup_query .= $limit_query;
            $stmt = $db->prepare($setup_query);
            $params = has_role("Admin")?[":quantity"=> $quan, ":q" => "%$query%"]:[":q" => "%$query%"];
            $pag_query .= has_role("Admin")?" p.quantity <=:quantity AND name like :q ":"name like :q ";
            flash($pag_query);
            paginate($pag_query, $params, $per_page);
            flash($total_pages);
            //$r = $stmt->execute([":q" => "%$query%"]);
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
            if (has_role("Admin")) {
                $stmt->bindValue(":quantity", $quan);
            }
            $stmt->bindValue(":q", "%$query%");
            $r = $stmt->execute();
        }
    }
    else { // if query is empty and category isn't set
        $setup_query = has_role("Admin")?$lookup_query . " p.quantity <=:quantity AND name like :q ".$groupby_query:$lookup_query . " name like :q ".$groupby_query;
        if (isset($_POST["sort"])) { // if price sort is set
            switch ($_POST["sort"]) {
                case "asc":
                    $setup_query .= $asc_query;
                    $sortedByPrice = true;
                    break;
                case "desc":
                    $setup_query .= $desc_query;
                    $sortedByPrice = true;
                    break;
                default:
                    break;
            }
            switch ($_POST["rate"]) {
                case "asc":
                    $setup_query .= ($sortedByPrice)?", rating asc ":" ORDER BY rating asc ";
                    break;
                case "desc":
                    $setup_query .= ($sortedByPrice)?", rating desc ":" ORDER BY rating desc ";
                    break;
                default:
                    break;
            }
        } // if price sort isn't set
        else {
            //$stmt = $db->prepare(has_role("Admin")?$lookup_query . " p.quantity <=:quantity AND name like :q " .$groupby_query. $limit_query:$lookup_query . "name like :q " .$groupby_query. $limit_query);
        }
        $setup_query .= $limit_query;
        $stmt = $db->prepare($setup_query);
        $params = has_role("Admin")?[":quantity"=> $quan, ":q" => "%$query%"]:[":q" => "%$query%"];
        $pag_query .= has_role("Admin")?" p.quantity <=:quantity AND name like :q ":"name like :q ";
        paginate($pag_query, $params, $per_page);
        //$r = $stmt->execute([":q" => "%$query%"]);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
        if (has_role("Admin")) {
            $stmt->bindValue(":quantity", $quan);
        }
        $stmt->bindValue(":q", "%$query%");
        $r = $stmt->execute();
    }

    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
        flash(var_dump($stmt->errorInfo()));
    }
}
else if (isset($_POST["search"])) { // if search is filled out
    $db = getDB();
    $curr_category = $_POST["category"];
    if (isset($_POST["quan"])) {
        if ($_POST['quan'] == "") {
            $quan ='99999999';
        }
        else {
            $quan = $_POST["quan"];
        }    }
    else {
        $quan = "99999999";
    }
    if ($curr_category == "") { // if no category is chosen
        $setup_query = has_role("Admin")?$lookup_query . " p.quantity <=:quantity AND name like :q ".$groupby_query:$lookup_query . " name like :q ".$groupby_query;
        switch ($_POST["sort"]) {
            case "asc":
                $setup_query .= $asc_query;
                $sortedByPrice = true;
                break;
            case "desc":
                $setup_query .= $desc_query;
                $sortedByPrice = true;
                break;
            default:
                break;
        }
        switch ($_POST["rate"]) {
            case "asc":
                $setup_query .= ($sortedByPrice)?", rating asc ":" ORDER BY rating asc ";
                break;
            case "desc":
                $setup_query .= ($sortedByPrice)?", rating desc ":" ORDER BY rating desc ";
                break;
            default:
                break;
        }
        $setup_query .= $limit_query;
        $stmt = $db->prepare($setup_query);
        $params = has_role("Admin")?[":quantity"=> $quan, ":q" => "%$query%"]:[":q" => "%$query%"];
        $pag_query .= has_role("Admin")?" p.quantity <= :quantity AND name like :q ":"name like :q ";
        paginate($pag_query, $params, $per_page);
        //$stmt = $db->prepare($lookup_query . "name like :q " . $asc_query);
        //$r = $stmt->execute([":q" => "%$query%"]);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
        if (has_role("Admin")) {
            $stmt->bindValue(":quantity", $quan);
        }
        $stmt->bindValue(":q", "%$query%");
        $r = $stmt->execute();
    }
    else { // if a category is picked
        $setup_query = has_role("Admin")?$lookup_query . " p.quantity <= :quantity AND name like :q AND category=:category ".$groupby_query:$lookup_query . "name like :q AND category=:category ".$groupby_query;
        switch ($_POST["sort"]) {
            case "asc":
                $setup_query .= $asc_query;
                $sortedByPrice = true;
                break;
            case "desc":
                $setup_query .= $desc_query;
                $sortedByPrice = true;
                break;
            default:
                break;
        }
        switch ($_POST["rate"]) {
            case "asc":
                $setup_query .= ($sortedByPrice)?", rating asc ":" ORDER BY rating asc ";
                break;
            case "desc":
                $setup_query .= ($sortedByPrice)?", rating desc ":" ORDER BY rating desc ";
                break;
            default:
                break;
        }
        $setup_query .= $limit_query;
        $stmt = $db->prepare($setup_query);
        $params = has_role("Admin")?[":quantity"=> $quan,":q" => "%$query%", ":category" => $curr_category]:[":q" => "%$query%", ":category" => $curr_category];
        $pag_query .= has_role("Admin")?" p.quantity <= :quantity AND name like :q AND category=:category ":"name like :q  AND category=:category ";
        paginate($pag_query, $params, $per_page);
        //$stmt = $db->prepare($lookup_query . "name like :q AND category=:category " . $asc_query);
        //$r = $stmt->execute([":q" => "%$query%", ":category" => $curr_category]);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
        if (has_role("Admin")) {
            $stmt->bindValue(":quantity", $quan);
        }
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
        <?php if (has_role("Admin")): ?>
        <label>Sort by max quantity:</label>
        <input type="number" name="quan" placeholder="Quantity" value="<?php isset($_POST['quan'])?safer_echo($_POST['quan']):safer_echo(""); ?>" min="0"/>
        <?php endif; ?>
        <div>
            <label>Sort by rating:</label>
            <select name="rate" value="">
                <option value ="" <?php echo (isset($_POST["rate"]))?(($_POST["rate"]=="")?"selected":""):""?>>Best Match</option>
                <option value ="asc" <?php echo (isset($_POST["rate"]))?(($_POST["rate"]=="asc")?"selected":""):""?>>Low to High</option>
                <option value ="desc" <?php echo (isset($_POST["rate"]))?(($_POST["rate"]=="desc")?"selected":""):""?>>High to Low</option>
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