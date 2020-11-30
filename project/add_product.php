<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
    <title>Add Products</title>
    <h3>Add Product</h3>
    <form method="POST">
        <p>
            <label>Name</label>
            <input name="name" placeholder="Name"/>
            <label>Description</label>
            <input type="text" name="description" placeholder="Description"/>
            <label>Category</label>
            <input type="text" name="category" placeholder="Category"/>
            <label>Quantity</label>
            <input type="number" min="0" name="quantity"/>
            <label>Price</label>
            <input type="number" min="0.01" step="0.01" name="price"/>
            <label>Visible?</label>
            <input type="radio" name="visible" value="1" />Yes
            <input type="radio" name="visible" value="0" checked="checked"/>No
        </p>
        <p><input type="submit" name="save" value="Create"/></p>
    </form>

<?php
if(isset($_POST["save"])){
    //TODO add proper validation/checks
    $name = $_POST["name"];
    $quantity = $_POST["quantity"];
    $price = $_POST["price"];
    $description = $_POST["description"];
    $created = date('Y-m-d H:i:s');//calc
    $visibility = $_POST["visible"];
    $category = $_POST["category"];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Products (name, quantity, price, description, visibility, category, user_id) VALUES(:name, :quantity, :price, :description, :visibility, :category, :user)");
    $r = $stmt->execute([
        ":name"=>$name,
        ":quantity"=>$quantity,
        ":price"=>$price,
        ":description"=>$description,
        ":user"=>$user,
        ":visibility"=>$visibility,
        ":category"=>$category
    ]);
    if($r){
        flash("Product added successfully");
    }
    else{
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php require(__DIR__ . "/partials/flash.php");
