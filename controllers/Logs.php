<?php

class Logs extends Controller {

	/*LISTE DES LOGS*/
    function LogsListe($f3) {
		$Logs = new LogsModel($this->db);
		$f3->set('Logs',$Logs->all());
		$f3->set('CONTENT','views/outils/logs_liste.html');
		$f3->set('CONTENTJS','views/outils/logs_liste.js');
		echo \Template::instance()->render('views/accueil/accueil.html','text/html');
		$this->db = null;
    }
	/*LISTE DES LOGS*/

}
