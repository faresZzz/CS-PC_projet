<?php

class Outils extends Controller {

    function ExportFacturesView($f3) {
		$f3->set('CONTENT','views/outils/export-factures.html');
		$f3->set('CONTENTJS','views/outils/export-factures.js');
		$agences = new AgencesModel($this->db);
		$ListeAgences= $agences->all();
		$f3->set('Agences',$ListeAgences);
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
		$Virgule = number_format($number, 2, ',', ' ');
		return $Virgule;
	}

	/*Export des remise de cheques*/
    function ExportChequesCSV($f3) {
		$excel = \Sheet::instance();
		$DateDeDebut = $f3->get('POST.DateDeDebut');
		$DateDeFin = $f3->get('POST.DateDeFin');
		$Agence = $f3->get('POST.Agence');
		$Ventes = new VentesModel($this->db);
		$VentesExport = $Ventes->ExportCheque($DateDeDebut,$DateDeFin,$Agence);
		$MontantTTCTotal = "0";
		//
		$filename = "/tmp/EXPORTCHEQUES-".$Agence.".csv";
		$fh = fopen($filename, "w");
		fputcsv($fh, array("Num Facture","Date de Cloture","Client","Montant TTC","Montant HT ","TVA 5.5","TVA 10","TVA 20","Commentaire"),";");
		foreach($VentesExport as $row) {
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
			fputcsv($fh, array($NumFacture,$DateCloture,$Client,self::FormatExcel($MontantTTC),self::FormatExcel($MontantHT),self::FormatExcel($TVA05[0]['MONTANT_TVA']),self::FormatExcel($TVA10[0]['MONTANT_TVA']),self::FormatExcel($TVA20[0]['MONTANT_TVA']),$CommentaireCloture),";");
		}
		fputcsv($fh, array("","","TOTAL TTC :",$MontantTTCTotal,"","","","",""),";");
		fclose($fh);
		$web = \Web::instance();
		$throttle = 2048; // throttle to around 256 KB /s
		$sent = $web->send($filename, NULL, $throttle);
		$db = null;
    }

	/*EXPORT COMPTA*/
	function ExportComptaCSV($f3) {
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
		$filename = "/tmp/EXPORTCOMPTA-".$Agence.".csv";
		$fh = fopen($filename, "w");

		//

		foreach($GETID as $row) {
			$NumVente = $row['NumVente'];
			$NumFacture = $row['NumFacture'];
			$NumFac = "FAC".$row['NumFacture'];
			$DateVente = $row['DateVente'];
			$CodeComptable = $row['CodeComptable'];
			$Nom = utf8_decode($row['NomClient']." ".$row['PrenomClient']);
			$AcompteSurDevis = $row['AcompteSurDevis'];
			$SoldeSurDevis = $row['SoldeSurDevis'];
			if ( $AcompteSurDevis > "0" ) {
				$Suffixe = str_replace(' ', '', substr($row['NomClient'], 0, 8))."-ACOMPTE";
			}
			else {
				$Suffixe = str_replace(' ', '', substr($row['NomClient'], 0, 8));
			}

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
			$VENTESMODEL = new VentesModel($this->db);
			$GETALLACOMPTEPOURCENTAGE = $VENTESMODEL->GetAllAcomptePourcentage($SoldeSurDevis);



			//$TOTALTTC = $TotalHT+$TVA20[0]['MONTANT_TVA']+$TVA10[0]['MONTANT_TVA']+$TVA05[0]['MONTANT_TVA'];
			if ( $TTC00[0]['MONTANT_TTC'] > "0" ) {
				$ttc0 = $TTC00[0]['MONTANT_TTC'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "4119".$Suffixe, $Nom, self::FormatExcel($TTC00[0]['MONTANT_TTC'])),";"); //MONTANT TTC TVA 0
			}
			if ( $TTC05[0]['MONTANT_TTC'] > "0" ) {
				$ttc5 = $TTC05[0]['MONTANT_TTC'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "4115".$Suffixe, $Nom, self::FormatExcel($TTC05[0]['MONTANT_TTC'])),";"); //MONTANT TTC TVA 5.5
			}
			if ( $TTC10[0]['MONTANT_TTC'] > "0" ) {
				$ttc10 = $TTC10[0]['MONTANT_TTC'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "411".$Suffixe, $Nom, self::FormatExcel($TTC10[0]['MONTANT_TTC'])),";"); //MONTANT TTC TVA 10
			}
			if ( $TTC20[0]['MONTANT_TTC'] > "0" ) {
				$ttc20 = $TTC20[0]['MONTANT_TTC'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "4112".$Suffixe, $Nom, self::FormatExcel($TTC20[0]['MONTANT_TTC'])),";"); //MONTANT TTC TVA 20
			}
			//
			if ( $HT00[0]['MONTANT_HT'] > "0" ) {
				$ht00 = $HT00[0]['MONTANT_HT'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "70419", $Nom, "", self::FormatExcel($HT00[0]['MONTANT_HT'])),";"); //MONTANT HT 0 VENTILE
			}
			if ( $HT05[0]['MONTANT_HT'] > "0" ) {
				$ht05 = $HT05[0]['MONTANT_HT'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "70411", $Nom, "", self::FormatExcel($HT05[0]['MONTANT_HT'])),";"); //MONTANT HT 5.5 VENTILE
			}
			if ( $HT10[0]['MONTANT_HT'] > "0" ) {
				$ht10 = $HT10[0]['MONTANT_HT'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "70414", $Nom, "", self::FormatExcel($HT10[0]['MONTANT_HT'])),";"); //MONTANT HT 10 VENTILE
			}
			if ( $HT20[0]['MONTANT_HT'] > "0" ) {
				$ht20 = $HT20[0]['MONTANT_HT'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "70415", $Nom, "", self::FormatExcel($HT20[0]['MONTANT_HT'])),";"); //MONTANT HT 20 VENTILE
			}
			//
			if ( $TVA05[0]['MONTANT_TVA'] > "0" ) {
				$tva05 = $TVA05[0]['MONTANT_TVA'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "445711", $Nom, "", self::FormatExcel($TVA05[0]['MONTANT_TVA'])),";"); //MONTANT DE TVA5.5
			}
			if ( $TVA10[0]['MONTANT_TVA'] > "0" ) {
				$tva10 = $TVA10[0]['MONTANT_TVA'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "445714", $Nom, "", self::FormatExcel($TVA10[0]['MONTANT_TVA'])),";"); //MONTANT DE TVA10
			}
			if ( $TVA20[0]['MONTANT_TVA'] > "0" ) {
				$tva20 = $TVA20[0]['MONTANT_TVA'];
				fputcsv($fh, array($DateVente,"VE", $NumFac, "445715", $Nom, "", self::FormatExcel($TVA20[0]['MONTANT_TVA'])),";"); //MONTANT DE TVA20
			}
			$MontantTotalTTC = ($ttc0+$ttc5+$ttc10+$ttc20);
			$MontantTotalHT = ($ht00+$ht05+$ht10+$ht20+$tva05+$tva10+$tva20);
			$Ecart = round( ($MontantTotalHT-$MontantTotalTTC),2 );
			if ( $Ecart != 0 ) {
				fputcsv($fh, array($DateVente,"VE", $NumFac, "758", "ECART DE FACTURATION", "", self::FormatExcel($Ecart)),";"); //Ecart
			}
		};
		fclose($fh);
		$web = \Web::instance();
		$throttle = 4096; // throttle to around 456 KB /s
		$sent = $web->send('/tmp/EXPORTCOMPTA-'.$Agence.'.csv', NULL, $throttle);
		$db = null;
    }

}
