<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create

function is_logged_in(){
    return isset($_SESSION["user"]);
}
function has_role($role){
    if(is_logged_in() && isset($_SESSION["user"]["roles"])){
        foreach($_SESSION["user"]["roles"] as $r){
            if($r["name"] == $role){
                return true;
            }
        }
    }
    return false;
}
function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}
function get_firstname($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT first_name from Users where id = :id");
    $result = $stmt->execute([":id" => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    return $item["first_name"];
}
function get_lastname($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT last_name from Users where id = :id");
    $result = $stmt->execute([":id" => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    return $item["last_name"];
}

function get_username_from_id($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT username from Users where id = :id");
    $result = $stmt->execute([":id" => $id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    return $item["username"];
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

function getPrice($productID) {
    $db = getDB();
    $stmt = $db->prepare("SELECT price from Products where id = :id");
    $result = $stmt->execute([":id" => $productID]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    return $item["price"];
}

function getProductName($productID) {
    $db = getDB();
    $stmt = $db->prepare("SELECT name from Products where id = :id");
    $result = $stmt->execute([":id" => $productID]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    return $item["name"];
}

function getCategories() {
    $db = getDB();
    $stmt = (has_role("Admin")) ? $db->prepare("SELECT category from Products WHERE NOT category='' ") : $db->prepare("SELECT category from Products WHERE NOT category='' AND visibility='1' AND quantity!='0'");
    $r = $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $return_cats = [];
    foreach($categories as $cat) {
        if (!in_array($cat, $return_cats)) {
            array_push($return_cats, $cat);
        }
    }
    return $return_cats;
}

function getURL($path) {
    if (substr($path, 0, 1) == "/") {
        return path;
    }
    return $_SERVER["CONTEXT_PREFIX"] . "/IT202repo/project/$path";
}

/**
 * @param $query must have a column called "total"
 * @param array $params
 * @param int $per_page
 */
function paginate($query, $params = [], $per_page = 10) {
    global $page;
    if (isset($_GET["page"])) {
        try {
            $page = (int)$_GET["page"];
        }
        catch (Exception $e) {
            $page = 1;
        }
    }
    else {
        $page = 1;
    }
    $db = getDB();
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = 0;
    if ($result) {
        $total = (int)$result["total"];
    }
    global $total_pages;
    $total_pages = ceil($total / $per_page);
    global $offset;
    $offset = ($page - 1) * $per_page;
}
?>
