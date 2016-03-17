<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$compare_start = date("Y-m-d H:i:s");
$compare_end = date("Y-m-d H:i:s", strtotime($compare_start.'+90MINUTES'));
if(!isset($_POST["fetched"])){
	$load = $db->query("SELECT * FROM cours
								JOIN salle ON cours_salle=salle.salle_id
								JOIN users ON prof_principal=users.user_id
								JOIN niveau ON cours_niveau=niveau.niveau_id
								WHERE ouvert=1
								ORDER BY cours_start ASC, cours_id ASC");
} else {
	$fetched = $_POST["fetched"];
	$load = $db->query("SELECT * FROM cours
								JOIN salle ON cours_salle=salle.salle_id
								JOIN users ON prof_principal=users.user_id
								JOIN niveau ON cours_niveau=niveau.niveau_id
								WHERE ouvert=1 AND cours_id NOT IN ('".implode($fetched, "','")."')
								ORDER BY cours_start ASC, cours_id ASC");
}

$sessionsList = array();
while($details = $load->fetch(PDO::FETCH_ASSOC)){
	$s = array();
	$s["id"] = $details["cours_id"];
	$s["title"] = $details["cours_intitule"];
	$s["start"] = $details["cours_start"];
	$s["end"] = $details["cours_end"];
	$s["duration"] = $details["cours_unite"];
	$s["level"] = $details["niveau_name"];
	$s["room"] = $details["salle_name"];
	$s["teacher"] = $details["user_prenom"]." ".$details["user_nom"];
	array_push($sessionsList, $s);
}

echo json_encode($sessionsList);
?>