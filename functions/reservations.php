<?php
function addResa(){
	$demandeur = $_POST['identite'];
	$prestation = $_POST['prestation'];
	$date_debut = $_POST['date_debut']." ".$_POST['heure_debut'];
	$date_fin = $_POST['date_debut']." ".$_POST['heure_fin'];
	$lieu = $_POST['lieu'];
	
	$unite = (strtotime($_POST['heure_fin']) - strtotime($_POST['heure_debut']))/3600;
	$prix = $_POST['prix_resa'];
	
	$priorite = 0;
	$paiement = 0;
	
	$db = new PDO('mysql:host=localhost;dbname=Salsabor;charset=utf8', 'root', '');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	try{
		$db->beginTransaction();
		$insertResa = $db->prepare('INSERT INTO reservations(reservation_personne, type_prestation, reservation_start, reservation_end, reservation_salle, reservation_unite, reservation_prix, priorite, paiement_effectue)
		VALUES(:reservation_personne, :type_prestation, :reservation_start, :reservation_end, :lieu, :unite, :prix, :priorite, :paiement_effectue)');
		$insertResa->bindParam(':reservation_personne', $demandeur);
		$insertResa->bindParam(':type_prestation', $prestation);
		$insertResa->bindParam(':reservation_start', $date_debut);
		$insertResa->bindParam(':reservation_end', $date_fin);
		$insertResa->bindParam(':lieu', $lieu);
		$insertResa->bindParam(':unite', $unite);
		$insertResa->bindParam(':prix', $prix);
		$insertResa->bindParam(':priorite', $priorite);
		$insertResa->bindParam(':paiement_effectue', $paiement);
		$insertResa->execute();
		
		$db->commit();
	} catch(PDOException $e){
		$db->rollBack();
		var_dump($e->getMessage());
	}
}