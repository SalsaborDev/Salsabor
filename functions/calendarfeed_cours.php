<?php
require_once "db_connect.php";
/** Page servant à alimenter le planning des cours **/
try
{
	
	$db = PDOFactory::getConnection();
    /** Obtention des cours **/
    $calendar = $db->prepare('SELECT * FROM cours JOIN salle ON (cours_salle=salle.salle_id) JOIN niveau ON (cours_niveau=niveau.niveau_id)');
    $calendar->execute();
    $events = array();

    /** Remplissage récursif d'un tableau et encodage JSON **/
    while($row_calendar = $calendar->fetch(PDO::FETCH_ASSOC)){
        $e = array();
        $e['id'] = $row_calendar['cours_id'];
        $e['title'] = $row_calendar['cours_intitule']."\n".$row_calendar['salle_name']."\n".$row_calendar['niveau_name'];
        $e['start'] = $row_calendar['cours_start'];
        $e['end'] = $row_calendar['cours_end'];
		$e['prestation'] = $row_calendar['cours_type'];
		$e['type'] = 'cours';
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