<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();

/**
This code will:
- Compute the amount of remaining hours on a product based on the sessions taken with it.
- Deactivate the product if the remaining hours are equal or less than 0
- Activate the product if it has recieved records while it was still pending
- Compute the activation and expiration date everytime to act as a failsafe is the auto-activation script fails (it does)

Yes. This code does everything to ensure the information can be tracked and stay as accurate as possible.
**/

$product_id = $_POST["product_id"];

$product_details = $db->query("SELECT volume_horaire, est_illimite, pa.date_activation AS produit_adherent_activation, volume_horaire, validite_initiale, pa.actif AS produit_adherent_actif, date_achat,
						IF(date_prolongee IS NOT NULL, date_prolongee,
							IF (date_fin_utilisation IS NOT NULL, date_fin_utilisation, date_expiration)
							) AS produit_validity FROM produits_adherents pa
						JOIN produits p
							ON pa.id_produit_foreign = p.produit_id
						JOIN transactions t
							ON pa.id_transaction_foreign = t.id_transaction
						WHERE id_produit_adherent = '$product_id'")->fetch(PDO::FETCH_ASSOC);

$sessions_list = $db->query("SELECT cours_unite, cours_start, cours_end FROM cours_participants cp
							JOIN cours c ON cp.cours_id_foreign = c.cours_id
							WHERE produit_adherent_id = '$product_id'
							ORDER BY cours_start ASC");

$remaining_hours = $product_details["volume_horaire"];
$date_fin_utilisation = $product_details["produit_validity"];
$values = array();
$computeEnd = false;

while($session = $sessions_list->fetch(PDO::FETCH_ASSOC)){
	if($remaining_hours == $product_details["volume_horaire"] && $product_details["produit_adherent_actif"] != '1'){
		if($session["cours_start"] >= $product_details["date_achat"]){
			$date_activation = date_create($session["cours_start"])->format("Y-m-d 00:00:00");
			$setActivationDate = $db->query("UPDATE produits_adherents SET date_activation = '$date_activation' WHERE id_produit_adherent = '$product_id'");
			$computeEnd = true;
		}
	}
	$remaining_hours -= floatval($session["cours_unite"]);
	if($product_details["produit_validity"] == null || $product_details["produit_validity"] > $session["cours_end"]){
		if($remaining_hours >= 0){
			$date_fin_utilisation = $session["cours_end"];
		}
	}
}

if($remaining_hours <= 0){ // If the number of remaining hours is negative
	if($product_details["est_illimite"] == "1"){
		$status = '1';
		$deactivate = $db->query("UPDATE produits_adherents
							SET actif='1', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
		array_push($values, -1 * $remaining_hours); // Position 1 of the array
	} else {
		$status = '2';
		if($product_details["produit_adherent_actif"] == "2"){
			$deactivate = $db->query("UPDATE produits_adherents
							SET actif='2', volume_cours = '$remaining_hours', date_fin_utilisation = '$date_fin_utilisation'
							WHERE id_produit_adherent = '$product_id'");
		} else {
			$deactivate = $db->query("UPDATE produits_adherents
							SET actif='2', date_fin_utilisation='$date_fin_utilisation', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
		}
		array_push($values, $remaining_hours); // Position 1 of the array
	}
} else if($remaining_hours == $product_details["volume_horaire"]){
	$status = '0';
	array_push($values, $remaining_hours); // Position 1 of the array
	$deactivate = $db->query("UPDATE produits_adherents
							SET actif='0', volume_cours = '$remaining_hours'
							WHERE id_produit_adherent = '$product_id'");
	echo 0;
} else { // If the hours are still in positive.
	array_push($values, $remaining_hours);
	if($computeEnd){ // If the product was not active before and has to be activated.
		$date_fin_utilisation = date_create(computeExpirationDate($db, $date_activation, $product_details["validite_initiale"]))->format("Y-m-d H:i:s");
		if($date_fin_utilisation < $product_details["produit_validity"]){
			$status = '1';
		} else {
			$status = '2';
		}
		$update = $db->query("UPDATE produits_adherents
						SET actif='$status', date_activation = '$date_activation', date_expiration='$date_fin_utilisation', volume_cours = '$remaining_hours'
						WHERE id_produit_adherent = '$product_id'");
	} else { // If the product was already active before.
		if($product_details["produit_validity"] != '' && date_create($product_details["produit_validity"])->format("Y-m-d") < date("Y-m-d")){
			$status = '2';
		} else {
			$status = '1';
		}
		$update = $db->query("UPDATE produits_adherents
						SET actif='$status', volume_cours = '$remaining_hours'
						WHERE id_produit_adherent = '$product_id'");
	}
}
array_push($values, $date_fin_utilisation); // Position 0 of the array
array_push($values, $status); // Position 2 of the array
echo json_encode($values);
?>
