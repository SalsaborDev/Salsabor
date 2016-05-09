<?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();
$data = $_GET['id'];

// User details
$details = $db->query("SELECT *, COUNT(task_title) AS count FROM users u
						JOIN tasks t ON u.user_id = t.task_target
						WHERE user_id='$data'
						AND task_token LIKE '%USR%'
						AND task_state = 0")->fetch(PDO::FETCH_ASSOC);
?>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Participations de <?php echo $details["user_prenom"]." ".$details["user_nom"];?> | Salsabor</title>
		<base href="../../">
		<?php include "styles.php";?>
		<?php include "scripts.php";?>
		<script src="assets/js/products.js"></script>
		<script src="assets/js/participations.js"></script>
		<script>
			$(document).ready(function(){
				displayUserParticipations(<?php echo $data;?>);
			})
		</script>
	</head>
	<body>
		<?php include "nav.php";?>
		<div class="container-fluid">
			<div class="row">
				<?php include "side-menu.php";?>
				<div class="col-lg-10 col-lg-offset-2 main">
					<?php include "inserts/user_banner.php";?>
					<legend><span class="glyphicon glyphicon-user"></span> Participations</legend>
					<ul class="nav nav-tabs">
						<li role="presentation"><a href="user/<?php echo $data;?>">Informations personnelles</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/abonnements">Abonnements</a></li>
						<li role="presentation" class="active"><a href="user/<?php echo $data;?>/historique">Participations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/achats">Achats</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/reservations">Réservations</a></li>
						<li role="presentation"><a href="user/<?php echo $data;?>/taches">Tâches</a></li>
						<?php if($details["est_professeur"] == 1){ ?>
						<li role="presentation"><a>Cours donnés</a></li>
						<li role="presentation"><a>Tarifs</a></li>
						<li role="presentation"><a>Statistiques</a></li>
						<?php } ?>
					</ul>
					<div class="container-fluid participations-list-container">
						<p class="col-md-4"><span id="total-count"></span> Participations</p>
						<p class="col-md-4"><span id="valid-count"></span> Participations valides</p>
						<p class="col-md-4"><span id="over-count"></span> Participations hors forfait</p>
						<!--<button class='btn btn-default btn-modal btn-link-all' id='link-all' onclick='linkAll()' title='Délier tous les cours hors forfait'><span class='glyphicon glyphicon-arrow-right'></span> Associer toutes les participations irrégulières</button>-->
						<ul class="participations-list">
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php include "inserts/sub_modal_product.php";?>
	</body>
</html>
