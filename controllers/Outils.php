<?php

class Outils extends Controller {

    function ExportFacturesView($f3) {
		$f3->set('CONTENT','views/outils/export-factures.html');
		$f3->set('CONTENTJS','views/outils/export-factures.js');
		$agences = new AgencesModel($this->db);
		$ListeAgences= $agences->all();
		$f3->set('Agences',$ListeAgences);
		$Export = new LogsExportModel($this->db);
		$ListeExport= $Export->all();
		$f3->set('ListeExport',$ListeExport);
		echo \Template::instance()->render('views/accueil/accueil.html','text/html');
		$this->db = null;
    }


    function ExportFacturesCSV($f3) {
		$excel = \Sheet::instance();
		$DateDeDebut = $f3->get('POST.DateDeDebut');
		$DateDeFin = $f3->get('POST.DateDeFin');
		$Agence = $f3->get('POST.Agence');
		$Ventes = new VentesModel($this->db);
		$VentesExport = $Ventes->Export($DateDeDebut,$DateDeFin,$Agence);
		$headers = ['NumVente'=>'NumVente', 'NumFacture'=>'NumFacture', 'Agence'=>'Agence','Client'=>'Client','CodeComptable'=>'N° comptable','DateVente'=>'DateVente','Type'=>'Type','Etat'=>'Etat','Reference'=>'Reference','Commercial'=>'Commercial','MontantHT'=>'Montant HT'];
		$excel->renderXLS($VentesExport,$headers,"exports-factures.xls");
		echo $excel;
    }


	function FormatExcel($number) {
		//$Virgule = number_format($number, 2, ',', ' ');
		$Virgule = number_format($number, 2);
		$SPrint = sprintf('%0.2f',$number);
		$ROUND = round($number,2, PHP_ROUND_HALF_EVEN);
		//return $Virgule;
		//return $SPrint;
		//return $number;
		return $ROUND;
	}

	/*Export des reglements*/
    function ExportReglementsCSV($f3) {
		date_default_timezone_set('UTC');
		$DateNow = date(dmyhis);
		$excel = \Sheet::instance();
		$DateDeDebut = $f3->get('POST.DateDeDebut');
		$DateDeFin = $f3->get('POST.DateDeFin');
		$Agence = $f3->get('POST.Agence');
		$Ventes = new ReglementsModel($this->db);
		$VentesExport = $Ventes->ExportReglements($DateDeDebut,$DateDeFin,$Agence);
		$MontantTTCTotal = "0";
		//
		$filename = "ui/export/EXPORT-REGLEMENTS-".$Agence."-".$DateNow.".csv";
		$fh = fopen($filename, "w");
		/*fputcsv($fh, array("Num Facture","Date de Cloture","Client","Mode","Montant TTC","Montant HT ","TVA 5.5","TVA 10","TVA 20","Commentaire"),";");*/
		fputcsv($fh, array("Num Facture","Date de Cloture","Client","Mode","Montant TTC","Commentaire"),";");
		foreach($VentesExport as $row) {
			$ModeReglement = utf8_decode($row['ModeReglement']);
			$NumVente = $row['NumVente'];
			$NumFacture = $row['NumFacture'];
			$DateCloture = $row['DateCloture'];
			$Client = utf8_decode($row['Client']);
			$MontantHT = $row['MontantHT'];
			$MontantTTC = $row['MontantTTC'];
			$MontantTTCTotal += $MontantTTC;
			$CommentaireCloture = utf8_decode($row['CommentaireCloture']);
			$TVAMODEL = new VentesLigneModel($this->db);
			$TVA05 = $TVAMODEL->TvaLigne($NumVente,"5.5");
			$TVA10 = $TVAMODEL->TvaLigne($NumVente,"10");
			$TVA20 = $TVAMODEL->TvaLigne($NumVente,"20");
			//ECRITURE DES LIGNES
			fputcsv($fh, array($NumFacture,$DateCloture,$Client,$ModeReglement,self::FormatExcel($MontantTTC),$CommentaireCloture),";");
			/*fputcsv($fh, array($NumFacture,$DateCloture,$Client,$ModeReglement,self::FormatExcel($MontantTTC),self::FormatExcel($MontantHT),self::FormatExcel($TVA05[0]['MONTANT_TVA']),self::FormatExcel($TVA10[0]['MONTANT_TVA']),self::FormatExcel($TVA20[0]['MONTANT_TVA']),$CommentaireCloture),";");*/
		}
		fputcsv($fh, array("","","","TOTAL TTC :",$MontantTTCTotal,"","","","",""),";");
		fclose($fh);
		$LogsExport = new LogsExportModel($this->db);
		$Logs = $LogsExport->add($filename,$MontantTTCTotal);
		$web = \Web::instance();
		$throttle = 2048; // throttle to around 256 KB /s
		$sent = $web->send($filename, NULL, $throttle);
		$db = null;
    }

	/*EXPORT COMPTA*/
	function ExportComptaCSV($f3) {
		date_default_timezone_set('UTC');
		$DateNow = date(dmyhis);
		$f3=Base::instance();
		$db = new DB\SQL($f3->get('MYSQL.dsn'),$f3->get('MYSQL.user'),$f3->get('MYSQL.password'));
		//ON RECUP LA LISTE DES FACTURES
		$DateDeDebut = $f3->get('POST.DateDeDebut');
		$DateDeFin = $f3->get('POST.DateDeFin');
		$Agence = $f3->get('POST.Agence');
		$GETID= $db->exec("SELECT
							ventes.NumVente,
							ventes.NumFacture,
							ventes.Agence,
							ventes.DateVente,
							ventes.AcompteSurDevis,
							ventes.SoldeSurDevis,
							ventes.AvoirSurFacture,
							clients.CodeComptable,
							clients.NomClient,
							clients.PrenomClient
							FROM
							ventes
							INNER JOIN clients ON ventes.NumClient = clients.NumClient
							WHERE
							ventes.Type = 'Facture' AND ventes.Agence = '$Agence' AND
							(ventes.DateVente BETWEEN '$DateDeDebut' AND '$DateDeFin')
							GROUP BY
							ventes.NumVente,
							ventes.Agence,
							ventes.DateVente,
							clients.CodeComptable
							ORDER BY ventes.NumVente ASC");
		//ON RECUP LA LISTE DES FACTURES

		//ON RECUP LA LIGNE DE FACTURES
		$filename = "ui/export/EXPORT-COMPTA-".$Agence."-".$DateNow.".csv";
		$fh = fopen($filename, "w");

		//
		$COLA = 0;
		$COLB = 0;
		$Ecart = 0;

		foreach($GETID as $row) {
			$NumVente = $row['NumVente'];
			$NumFacture = $row['NumFacture'];
			$NumFac = "FAC".$row['NumFacture'];
			$DateVente = $row['DateVente'];
			$CodeComptable = $row['CodeComptable'];
			$Nom = utf8_decode($row['NomClient']." ".$row['PrenomClient']);
			$AcompteSurDevis = $row['AcompteSurDevis'];
			$SoldeSurDevis = $row['SoldeSurDevis'];
			$Avoir = $row['AvoirSurFacture'];
			$Suffixe = str_replace(' ', '', substr($row['NomClient'], 0, 8));

			$TVAMODEL = new VentesLigneModel($this->db);
			$TVA05 = $TVAMODEL->TvaLigne($NumVente,"5.5");
			$TVA10 = $TVAMODEL->TvaLigne($NumVente,"10");
			$TVA20 = $TVAMODEL->TvaLigne($NumVente,"20");
			//
			$TTC00 = $TVAMODEL->TtcLigne($NumVente,"0");
			$TTC05 = $TVAMODEL->TtcLigne($NumVente,"5.5");
			$TTC10 = $TVAMODEL->TtcLigne($NumVente,"10");
			$TTC20 = $TVAMODEL->TtcLigne($NumVente,"20");
			//
			$HT00 = $TVAMODEL->HtLigne($NumVente,"0");
			$HT05 = $TVAMODEL->HtLigne($NumVente,"5.5");
			$HT10 = $TVAMODEL->HtLigne($NumVente,"10");
			$HT20 = $TVAMODEL->HtLigne($NumVente,"20");
			//

			//$VENTESMODEL = new VentesModel($this->db);
			//$GETALLACOMPTEPOURCENTAGE = $VENTESMODEL->GetAllAcomptePourcentage($SoldeSurDevis);

			$ttc0 = 0;
			$ttc5 = 0;
			$ttc10 = 0;
			$ttc20 = 0;
			$tva05 = 0;
			$tva10 = 0;
			$tva20 = 0;
			$ht00 = 0;
			$ht05 = 0;
			$ht10 = 0;
			$ht20 = 0;
			$E=0;

			//$TOTALTTC = $TotalHT+$TVA20[0]['MONTANT_TVA']+$TVA10[0]['MONTANT_TVA']+$TVA05[0]['MONTANT_TVA'];
			if ( $TTC00[0]['MONTANT_TTC'] > "0" ) {
				$ttc0 = self::FormatExcel($TTC00[0]['MONTANT_TTC']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE", "4119".$Suffixe, $NumFac, $Nom, $ttc0),";"); //MONTANT TTC TVA 0
				}
				else {
					fputcsv($fh, array($DateVente,"VE", "4119".$Suffixe, $NumFac,$Nom." Avoir", "", $ttc0),";");
				}
			}
			if ( $TTC05[0]['MONTANT_TTC'] > "0" ) {
				$ttc5 = self::FormatExcel($TTC05[0]['MONTANT_TTC']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE", "4115".$Suffixe,$NumFac, $Nom, $ttc5),";"); //MONTANT TTC TVA 5.5
				}
				else {
					fputcsv($fh, array($DateVente,"VE","4115".$Suffixe, $NumFac,$Nom." Avoir", "", $ttc5),";");
				}
			}
			if ( $TTC10[0]['MONTANT_TTC'] > "0" ) {
				$ttc10 = self::FormatExcel($TTC10[0]['MONTANT_TTC']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE", "411".$Suffixe, $NumFac, $Nom, $ttc10),";"); //MONTANT TTC TVA 10
				}
				else {
					fputcsv($fh, array($DateVente,"VE", "411".$Suffixe, $NumFac, $Nom." Avoir", "", $ttc10),";");
				}
			}
			if ( $TTC20[0]['MONTANT_TTC'] > "0" ) {
				$ttc20 = self::FormatExcel($TTC20[0]['MONTANT_TTC']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE", "4112".$Suffixe, $NumFac, $Nom, $ttc20),";"); //MONTANT TTC TVA 20
				}
				else {
					fputcsv($fh, array($DateVente,"VE", "4112".$Suffixe, $NumFac, $Nom." Avoir", "", $ttc20),";");
				}
			}
			//
			if ( $HT00[0]['MONTANT_HT'] > "0" ) {
				$ht00 = self::FormatExcel($HT00[0]['MONTANT_HT']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE","70419", $NumFac, $Nom, "", $ht00),";"); //MONTANT HT 0 VENTILE
				}
				else {
					fputcsv($fh, array($DateVente,"VE","70419", $NumFac, $Nom." Avoir", $ht00, ""),";");
				}
			}
			if ( $HT05[0]['MONTANT_HT'] > "0" ) {
				$ht05 = self::FormatExcel($HT05[0]['MONTANT_HT']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE", "70411", $NumFac, $Nom, "", $ht05),";"); //MONTANT HT 5.5 VENTILE
				}
				else {
					fputcsv($fh, array($DateVente,"VE", "70411", $NumFac, $Nom." Avoir", $ht05, ""),";"); //MONTANT HT 5.5 VENTILE
				}
			}
			if ( $HT10[0]['MONTANT_HT'] > "0" ) {
				$ht10 = self::FormatExcel($HT10[0]['MONTANT_HT']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE","70414", $NumFac, $Nom, "", $ht10),";"); //MONTANT HT 10 VENTILE
				}
				else {
					fputcsv($fh, array($DateVente,"VE","70414", $NumFac, $Nom." Avoir", $ht10, ""),";");
				}
			}
			if ( $HT20[0]['MONTANT_HT'] > "0" ) {
				$ht20 = self::FormatExcel($HT20[0]['MONTANT_HT']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE", "70415", $NumFac, $Nom, "", $ht20),";"); //MONTANT HT 20 VENTILE
				}
				else {
					fputcsv($fh, array($DateVente,"VE", "70415", $NumFac, $Nom." Avoir", $ht20, ""),";");
				}
			}
			//
			if ( $TVA05[0]['MONTANT_TVA'] > "0" ) {
				$tva05 = self::FormatExcel($TVA05[0]['MONTANT_TVA']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE","445711", $NumFac, $Nom, "", $tva05),";"); //MONTANT DE TVA5.5
				}
				else {
					fputcsv($fh, array($DateVente,"VE","445711", $NumFac, $Nom." Avoir", $tva05, ""),";");
				}
			}
			if ( $TVA10[0]['MONTANT_TVA'] > "0" ) {
				$tva10 = self::FormatExcel($TVA10[0]['MONTANT_TVA']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE", "445714", $NumFac, $Nom, "", $tva10),";"); //MONTANT DE TVA10
				}
				else {
					fputcsv($fh, array($DateVente,"VE", "445714",$NumFac, $Nom." Avoir", $tva10, ""),";");
				}
			}
			if ( $TVA20[0]['MONTANT_TVA'] > "0" ) {
				$tva20 = self::FormatExcel($TVA20[0]['MONTANT_TVA']);
				if ( is_null($Avoir) ) {
					fputcsv($fh, array($DateVente,"VE","445715", $NumFac, $Nom, "", $tva20),";"); //MONTANT DE TVA20
				}
				else {
					fputcsv($fh, array($DateVente,"VE","445715",$NumFac, $Nom." Avoir", $tva20, ""),";");
				}
			}
			$MontantTotalTTC = $ttc0+$ttc5+$ttc10+$ttc20;
			$MontantTotalHT = $ht00+$ht05+$ht10+$ht20+$tva05+$tva10+$tva20;
			$Ecart = self::FormatExcel(($MontantTotalTTC-$MontantTotalHT));
			if ( $Ecart != 0 ) {
				$E += $Ecart;
				fputcsv($fh, array($DateVente,"VE", "758", $NumFac, "$Nom", "", $Ecart),";"); //Ecart
			}
			$COLA += $MontantTotalTTC;
			$COLB += $MontantTotalHT+$E;
		};
		fputcsv($fh, array("","","", "", "", "", ),";");
		fputcsv($fh, array("","", "", "", "", $COLA, $COLB),";");
		fclose($fh);
		$LogsExport = new LogsExportModel($this->db);
		$Logs = $LogsExport->add($filename,$COLA);
		$web = \Web::instance();
		$throttle = 4096; // throttle to around 456 KB /s
		$sent = $web->send($filename, NULL, $throttle);
		$db = null;
    }
}
