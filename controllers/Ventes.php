<?php

date_default_timezone_set('Etc/UTC');

class Ventes extends Controller {

    function import($f3){
        $folder = "ui/";
        foreach (glob($folder."JAL*.TXT") as $filename) {
            $file = explode(".", $filename);
            echo "Import du fichier : ".$filename."\r\n";
            echo "<br/>\r\n";
            $fp = fopen($filename,"r");
            $i = 1;
            while(!feof($fp)){
                $ligne = fgets($fp);
                if ($ligne) {
                    $Contenue = explode("!", $ligne);
                    $Refclient = intval($Contenue[0]);
                    if ($Refclient > 0) {
						$f3->set('NomClient',$DateNow);
                        $NomClient = $Contenue[1];
						$DateFacture = $Contenue[2];
						$MontantHT  = $Contenue[3];
						$TypeClient  = $Contenue[4];
						$Representant = $Contenue[5];
						echo $NomClient;
						echo "<br/>\r\n";
						$DateNow =  date('d/m/Y');
						$f3->set('NomClient',$NomClient);
						$f3->set('DateVente',$DateFacture);
						$f3->set('DateFacture',$DateFacture);
						$f3->set('MontantHT',$MontantHT);
						$f3->set('TypeClient',$TypeClient);
						$f3->set('Representant',$Representant);
						$f3->set('Refclient',$Refclient);
						$f3->set('NumFacture',$i);
						/*MPDF*/
						require_once 'vendor/autoload.php';
						$mpdf = new \Mpdf\Mpdf([
							'margin_left' => 5,
							'margin_right' => 5,
							'margin_top' => 50,
							'margin_bottom' => 20,
							'margin_header' => 5,
							'margin_footer' => 5,
							'default_font_size' => 9
						]);
						$mpdf->SetCompression(true);
						$mpdf->img_dpi = 54;
						$mpdf->allow_charset_conversion=true;
						$mpdf->charset_in='UTF-8';
						$mpdf->useSubstitutions = false; //speed
						$mpdf->simpleTables = true; //speed ==> true
						$mpdf->packTableData = true;
						$mpdf->SetTitle('Facture '.$Refclient);
						$mpdf->SetDisplayMode('fullpage');
						$html = \Template::instance()->render('template_factures.html');
						$html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
						$mpdf->WriteHTML($html);
						$mpdf->Output('FACTURE-'.$Refclient.'-'.$NomClient.'.pdf', 'F');
						/*MPDF*/
                    }
                }
                echo "\r\n";
                $i++;
            }
            echo "<br>";
            $db = null;
            fclose($fp);
        }
    }



    function import2($f3){
        $folder = "ui/upload/";
		$fp = file_get_contents($folder.$f3["NomDocument"]);
		$result = [];
		$lines = explode(PHP_EOL, $fp);
		foreach($lines as $line){
			$fields = explode('!', $line);
			foreach($fields as &$field){
				$field = trim($field);
			}
			if($fields[0] == 'Corps'){
				$result[$fields[0]][] = $fields;
			}
			else if($fields[0] == 'Ligne BL'){
				$result[$fields[0]][] = $fields;
			}
			else $result[$fields[0]] = $fields;
		}
		$NumFacture = $result['Facture'][1];
		$Email = $result['Facture'][2];
		$NomClient = $result['Entete'][1];
		//$NumClient = $result['Entete'][2];
		$Adresse = $result['Entete'][2];
		$Adresse2 = $result['Entete'][3];
		$Cp = $result['Entete'][5];
		$Ville = $result['Entete'][6];
		$DateFacture = $result['Entete'][10];
		$Observation = $result['Observation'][1];
		$LigneBL = $result['Ligne BL'];
		$Corps = $result['Corps'];
		// foreach ($LigneBL as $key => $value) {
		// 	echo "<pre>";
		// 	echo $value[1];
		// 	echo "</pre>";
		// }
		// foreach ($Corps as $key => $value) {
		// 	echo "<pre>";
		// 	echo "Quantite: ".$value[1];
		// 	echo "Designation: ".$value[2];
		// 	echo "P.U: ".$value[3];
		// 	echo "TTHT: ".$value[4];
		// 	echo "</pre>";
		// }
		$Port = $result['Conditionnement et port'][1];
		$Remise = $result['Remise'][1];
		$Escompte = $result['Escompte'][1];
		$Surenchere = $result['Surenchere'][1];
		$DateEcheance = $result['Pied facture'][5];
		// echo"<pre>";
		// print_r( $result);
		// echo"</pre>";
		/*MPDF*/
		$f3->set('NumFacture',$NumFacture);
		$f3->set('Email',$Email);
		$f3->set('NomClient',$NomClient);
		$f3->set('NumClient',"NumeroClient");
		$f3->set('Adresse',$Adresse);
		$f3->set('Adresse2',$Adresse2);
		$f3->set('Cp',$Cp);
		$f3->set('Ville',$Ville);
		$f3->set('DateFacture',$DateFacture);
		$f3->set('Observation',$Observation);
		$f3->set('DateEcheance',$DateEcheance);
		$f3->set('LigneBL',$LigneBL);
		$f3->set('Corps',$Corps);
		$f3->set('Port',$Port);
		$f3->set('Remise',$Remise);
		$f3->set('Escompte',$Escompte);
		$f3->set('Surenchere',$Surenchere);
		require_once 'vendor/autoload.php';
		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 5,
			'margin_right' => 5,
			'margin_top' => 50,
			'margin_bottom' => 20,
			'margin_header' => 5,
			'margin_footer' => 5,
			'default_font_size' => 9
		]);
		$mpdf->SetCompression(true);
		$mpdf->img_dpi = 54;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->useSubstitutions = false; //speed
		$mpdf->simpleTables = true; //speed ==> true
		$mpdf->packTableData = true;
		$mpdf->SetTitle('Facture '.$NumFacture);
		$mpdf->SetDisplayMode('fullpage');
		$html = \Template::instance()->render('template_factures2.html');
		$html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
		$mpdf->WriteHTML($html);
		$f3->set('NomFichier','FACTURE-'.$NumFacture.'-'.$NomClient.'-'.random_int(0,1000).'.pdf');
		$mpdf->Output('ui/facturePDF/'.$f3["NomFichier"], 'F');
		/*MPDF*/


    }

}
