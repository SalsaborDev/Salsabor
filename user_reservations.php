<?php
session_start();
if(!isset($_SESSION["username"])){
	header('location: portal');
}
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// User details
$details = $db->query("SELECT * FROM users u
						WHERE user_id='$data'")->fetch(PDO::FETCH_ASSOC);

$details["count"] = $db->query("SELECT * FROM tasks
					WHERE ((task_token LIKE '%USR%' AND task_target = '$data')
					OR (task_token LIKE '%PRD%' AND task_target IN (SELECT id_produit_adherent FROM produits_adherents WHERE id_user_foreign = '$data'))
					OR (task_token LIKE '%TRA%' AND task_target IN (SELECT id_transaction FROM transactions WHERE payeur_transaction = '$data')))
						AND task_state = 0")->rowCount();

// On obtient l'historique de ses réservations
$queryResa = $db->prepare('SELECT * FROM reservations b
							JOIN users u ON b.booking_holder = u.user_id
							JOIN rooms r ON b.booking_room = r.room_id
							WHERE booking_holder=?');
$queryResa->bindValue(1, $data);
$queryResa->execute();

$is_teacher = $db->query("SELECT * FROM assoc_user_tags ur
								JOIN tags_user tu ON tu.rank_id = ur.tag_id_foreign
								WHERE rank_name = 'Professeur' AND user_id_foreign = '$data'")->rowCount();
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Editer - <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../../">
		<?php include "styles.php";?>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
					<?php include "inserts/user_banner.php";?>
					<ul class="nav nav-tabs">
						<li role="presentation" class="visible-xs-block"><a href="user/<?php echo $data;?>">Infos perso</a></li>
						<li role="presentation" class="hidden-xs"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/historique">Participations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/taches">Tâches</a></li>
						<?php if($is_teacher == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
					<section id="resa">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Plage horaire</th>
									<th>Lieu</th>
									<th>Activité</th>
									<th>Prix de la réservation</th>
								</tr>
							</thead>
							<tbody>
								<?php while($reservations = $queryResa->fetch(PDO::FETCH_ASSOC)){ ?>
								<tr>
									<td>Le <?php echo date_create($reservations["booking_start"])->format('d/m/Y \d\e H\hi');?> à <?php echo date_create($reservations["booking_end"])->format('H\hi');?></td>
									<td><?php echo $reservations["salle_name"];?></td>
									<td><?php echo $reservations["prestations_name"];?></td>
									<td><?php echo $reservations["booking_price"];?> €</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</section>
				</div>
			</div>
		</div>
		<?php include "scripts.php";?>
		<?php include "inserts/edit_modal.php";?>
	</body>
</html>
