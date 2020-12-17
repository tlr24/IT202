<?php

if(!isset($page)) {
    flash("Pages variable not set!", "danger");
}
if(!isset($total_pages)) {
    flash("Total Pages variable not set!", "danger");
}
if(isset($id)){
    $addition = "id=" . $id . "&";
}
else {
    $addition = "";
}
if (isset($_SESSION['category'])) {
    $category = $_SESSION['category'];
    $addition .= "category=".$category."&";
}
if (isset($_SESSION['start'])) {
    $start = $_SESSION['start'];
    $addition .= "start=".$start."&";
}
if (isset($_SESSION['end'])) {
    $end = $_SESSION['end'];
    $addition .= "end=".$end."&";
}

?>
<?php if(isset($page) && isset($total_pages)):?>
    <nav aria-label="">
        <ul class="pagination justify-content-center">
            <?php if ($page != 1): ?>
            <div class="list-group-item <?php echo ($page-1) < 1?"disabled":"";?>">
                <?php $previous = "?" . $addition . "page=" . ($page-1) ?>
                <a class="page-link" href=<?php echo $previous?> tabindex="-1">Previous</a>
            </div>
            <?php endif; ?>
            <?php for($i = 0; $i < $total_pages; $i++):?>
                <?php $current = "?" . $addition . "page=" . ($i+1) ?>
                <div class="list-group-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href=<?php echo $current?>><?php echo ($i+1);?></a></div>
            <?php endfor; ?>
            <?php if ($page != $total_pages): ?>
            <div class="list-group-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                <?php $next = "?" . $addition . "page=" . ($page+1) ?>
                <a class="page-link" href=<?php echo $next?>>Next</a>
            </div>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif;?>
