<?php
require_once "../functions/db_connect.php";
/** Ensemble de code pour ajouter un tarif pour une réservation **/
?>
<form action="tarifs_liste.php" method="post" class="form-horizontal" role="form">
    <div class="form-group">
        <label for="type_prestation" class="col-sm-3 control-label">Type de prestation <span class="mandatory">*</span></label>
        <div class="col-sm-9">
            <select name="type_prestation" class="form-control">
            <?php
            $prestations = $db->query('SELECT * FROM prestations WHERE est_resa=1');
            while($row_prestations = $prestations->fetch(PDO::FETCH_ASSOC)){
                echo "<option value=".$row_prestations['prestations_id'].">".$row_prestations['prestations_name']."</option>";
            }
            ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="jour" class="col-sm-3 control-label">Jours de réservation <span class="mandatory">*</span></label>
        <div class="col-sm-9">
            <input type="checkbox" name="jour-1" id="jour-1" class="checkbox-inline" value="1">Semaine</input>
            <input type="checkbox" name="jour-2" id="jour-2" class="checkbox-inline" value="2">Samedi</input>
            <input type="checkbox" name="jour-3" id="jour-3" class="checkbox-inline" value="3">Dimanche</input>
        </div>
    </div>
    <div class="form-group">
       <fieldset>
            <label for="heure_debut" class="col-sm-3 control-label">Début à <span class="mandatory">*</span></label>
            <div class="col-sm-9">
                <input type="time" class="form-control" name="heure_debut" placeholder="10h00">
            </div>
            <label for="heure_fin" class="col-sm-3 control-label">Fin à <span class="mandatory">*</span></label>
            <div class="col-sm-9">
                <input type="time" class="form-control" name="heure_fin" placeholder="14h00">
            </div>
       </fieldset>
    </div>
    <div class="form-group">
        <label for="lieu_resa" class="col-sm-3 control-label">Lieu réservé <span class="mandatory">*</span></label>
        <div class="col-sm-9">
           <select name="lieu_resa" class="form-control">
            <?php
            $lieux = $db->query('SELECT * FROM salle');
            while($row_lieux = $lieux->fetch(PDO::FETCH_ASSOC)){
                echo "<option value=".$row_lieux['salle_id'].">".$row_lieux['salle_name']."</option>";
            }
            ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="prix_resa" class="col-sm-3 control-label">Prix <span class="mandatory">*</span></label>
        <div class="col-sm-9 input-group">
            <input type="text" class="form-control" name="prix_resa"><span class="input-group-addon">€</span>
        </div>
    </div>
    <input type="submit" name="addTarifResa" value="Ajouter" class="btn btn-default">
</form>