<?php
require_once "db_connect.php";
/** Page servant à alimenter le planning des cours **/
try
{
    $db = PDOFactory::getConnection();
    /** Obtention des cours **/
    $calendar = $db->prepare('SELECT * FROM reservations JOIN users ON reservation_personne=users.user_id JOIN salle ON (reservation_salle=salle.salle_id) JOIN prestations ON (type_prestation=prestations.prestations_id)');
    $calendar->execute();
    $events = array();

    /** Remplissage récursif d'un tableau et encodage JSON **/
    while($row_calendar = $calendar->fetch(PDO::FETCH_ASSOC)){
        $e = array();
        $e['id'] = $row_calendar['reservation_id'];
        $e['title'] = $row_calendar['prestations_name']." (".$row_calendar['user_prenom']." ".$row_calendar['user_nom']." - ".$row_calendar['salle_name'].")";
		$e['lieu'] = $row_calendar['salle_id'];
        $e['start'] = $row_calendar['reservation_start'];
        $e['end'] = $row_calendar['reservation_end'];
		$e['type'] = 'reservation';
        $e['priorite'] = $row_calendar['priorite'];
        // Paramètre propriétaire de Fullcalendar.js qui sert à délimiter un évènement
        // à ses heures de début et de fin.
        $e['allDay'] = false;

        array_push($events, $e);
    }

    echo json_encode($events);
    exit();
} catch(PDOException $e) {
    echo $e->getMessage();
}
?>