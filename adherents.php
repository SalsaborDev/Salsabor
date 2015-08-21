    <?php
require_once 'functions/db_connect.php';
$db = PDOFactory::getConnection();

$queryAdherents = $db->query('SELECT * FROM users ORDER BY user_nom ASC');
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Adhérents | Salsabor</title>
    <?php include "includes.php";?>
</head>
<body>
  <?php include "nav.php";?>
   <div class="container-fluid">
       <div class="row">
           <?php include "side-menu.php";?>
           <div class="col-sm-10 main">
              <p id="current-time"></p>
               <h1 class="page-title"><span class="glyphicon glyphicon-user"></span> Base Clients</h1>
			  <div class="btn-toolbar">
                   <a href="inscription.php?status=eleve" role="button" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Inscrire un nouvel élève</a>
               </div> <!-- btn-toolbar -->
				<div class="input-group input-group-lg search-form">
					<span class="input-group-addon"><span class="glyphicon glyphicon-filter"></span></span>
					<input type="text" id="search" class="form-control" placeholder="Tapez pour rechercher...">
				</div>
               <div id="users-list">
               	<table class="table table-striped">
               		<thead>
               			<tr>
               				<th class="col-lg-3">Nom <span class="glyphicon glyphicon-sort sort" data-sort="user-name"></span></th>
               				<th class="col-lg-4">Mail <span class="glyphicon glyphicon-sort sort" data-sort="mail"></span></th>
               				<th class="col-lg-1"></th>
               				<th class="col-lg-1"></th>
               				<th class="col-lg-1"></th>
               				<th class="col-lg-1"></th>
               				<th class="col-lg-1"></th>
               			</tr>
               		</thead>
               		<tbody id="filter-enabled" class="list">
               			<?php while($adherents = $queryAdherents->fetch(PDO::FETCH_ASSOC)){
               							$produits = $db->query("SELECT * FROM produits_adherents JOIN produits ON id_produit=produits.produit_id WHERE produits_adherents.actif=1 AND id_adherent=$adherents[user_id] AND produit_nom!='Invitation'")->rowCount();
               							$invitation = $db->query("SELECT * FROM produits_adherents JOIN produits ON id_produit=produits.produit_id WHERE produits_adherents.actif=1 AND id_adherent=$adherents[user_id] AND produit_nom='Invitation'")->rowCount();
               							$passages = $db->query("SELECT * FROM passages WHERE (status=0 OR status=3) AND passage_eleve='$adherents[user_rfid]'")->rowCount();
               							$echeances = $db->query("SELECT * FROM produits_echeances JOIN produits_adherents ON id_produit_adherent=produits_adherents.id_transaction WHERE echeance_effectuee=2 AND id_adherent=$adherents[user_id]")->rowCount();
               						?>
               			<tr>
               				<td class="col-lg-3 user-name"><?php echo $adherents['user_prenom']." ".$adherents['user_nom'];?></td>
               				<td class="col-lg-4 mail"><?php echo $adherents['mail'];?></td>
               				<?php if($produits != 0){ ?>
               								<td class="col-lg-1"><span class="glyphicon glyphicon-credit-card glyphicon-active" title="Un forfait actif en cours."></span></td>
               				<?php } else { ?>
               								<td class="col-lg-1"><span class="glyphicon glyphicon-credit-card glyphicon-inactive" title="Aucun forfait actif en cours."></span></td>
               				<?php } ?>
               				<?php if($invitation != 0){ ?>
               								<td class="col-lg-1"><span class="glyphicon glyphicon-heart-empty" title="Une invitation possédée"></span></td>
               				<?php } else { ?>
               								<td class="col-lg-1"><span class="glyphicon glyphicon-heart-empty glyphicon-inactive" title="Aucune invitation active"></span></td>
               				<?php } ?>
               							<?php if($passages != 0){ ?>
               								<td class="col-lg-1"><span class="glyphicon glyphicon-map-marker" title="Passages en attente d'association."></span></td>
               				<?php } else { ?>
               								<td class="col-lg-1"><span class="glyphicon glyphicon-map-marker glyphicon-inactive" title="Aucun passage en attente."></span></td>
               				<?php } ?>
               							<?php if($echeances != 0){ ?>
               								<td class="col-lg-1"><span class="glyphicon glyphicon-repeat glyphicon-danger" title="Echéances en retard."></span></td>
               				<?php } else { ?>
               								<td class="col-lg-1"><span class="glyphicon glyphicon-repeat glyphicon-inactive" title="Aucune échéance en retard."></span></td>
               				<?php } ?>
               				<td class="col-lg-1"><a href="user_details.php?id=<?php echo $adherents['user_id'];?>&status=membre" class="btn btn-default"><span class="glyphicon glyphicon-search"></span> Détails...</a></td>
               			</tr>
               						<?php } ?>
               		</tbody>
               	</table>
               </div>
           </div>
       </div>
   </div>
   <?php include "scripts.php";?>   
   <script>
	var options = {
		   valueNames: ['user-name', 'mail']
	   };
	   var usersList = new List('users-list', options);
	</script> 
</body>
</html>