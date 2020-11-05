<?php require_once(__DIR__ . "/partials/nav.php"); ?><title>Profile</title>
    <title>Test Create Products</title>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<h1>Test Create Products</h1>
<form method="POST">
    <p>
        <label>Name</label>
        <input name="name" placeholder="Name"/>
        <label>Description</label>
        <input type="text" name="description" placeholder="Description"/>
        <label>Quantity</label>
        <input type="number" min="1" name="quantity"/>
        <label>Price</label>
        <input type="number" min="0.01" step="0.01" name="price"/>
        <input type="submit" name="save" value="Create"/>
    </p>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$name = $_POST["name"];
	$quantity = $_POST["quantity"];
	$price = $_POST["price"];
	$description = $_POST["description"];
	$created = date('Y-m-d H:i:s');//calc
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Products (name, quantity, price, description, user_id) VALUES(:name, :quantity, :price, :description,:user)");
	$r = $stmt->execute([
		":name"=>$name,
		":quantity"=>$quantity,
		":price"=>$price,
		":description"=>$description,
		":user"=>$user
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");
