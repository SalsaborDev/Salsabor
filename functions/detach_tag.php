<?php
require_once "db_connect.php";
$db = PDOFactory::getConnection();

$tag = $_POST["tag"];
$target = $_POST["target"];
$type = $_POST["type"];

$query = "SELECT entry_id FROM assoc_".$type."_tags WHERE ".$type."_id_foreign = $target AND tag_id_foreign = $tag";

$entry_id = $db->query($query)->fetch(PDO::FETCH_COLUMN);

$detach = $db->query("DELETE FROM assoc_user_tags WHERE entry_id = $entry_id");

echo $entry_id;
?>
