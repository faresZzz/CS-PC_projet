<?php


class Factures extends Controller
{
    function listeFactures($f3){
        echo \Template::instance()->render('views/dossiers/listeFactures.html', 'text/html');
    }

    function getFactures($f3){

        $factureModel = new FactureModel($this->db);
        $facture = $factureModel->getAllFactures();
        header('Content-Type: application/json');
		echo json_encode($facture);

    }




    function ajoutFacture($f3){
        echo \Template::instance()->render('views/dossiers/AddFacture.html', 'text/html');
    }

    function upload($f3){
        $web = \Web::instance();
        $f3->set('UPLOADS','ui/upload/'); // don't forget to set an Upload directory, and make it writable!
        $overwrite = true; // set to true, to overwrite an existing file; Default: false
        $slug = true; // rename file to filesystem-friendly version
        $files = $web->receive(function($file,$formFieldName){
            global $f3;
            $name = explode('/',$file["name"]);
            $f3->set("NomDocument", $name[2]);
            if($file['size'] > (2 * 1024 * 1024))
                return false; // this file is not valid, return false will skip moving it

            // everything went fine, hurray!
            return true; // allows the file to be moved from php tmp dir to your defined upload dir
        },
        $overwrite,
        $slug
        );

        if ($files){
            $vente = new Ventes;
            $vente->import2($f3);
            $nouvelleFacture =  new FactureModel($this->db);
            $nouvelleFacture->newFacture();
            $f3->set("Validation", "Votre facture a bien été crée, sous le nom: ".$f3["NomFichier"]);
            echo \Template::instance()->render('views/dossiers/listeFactures.html', 'text/html');
        }
        else{
            $f3->set("ErrorMessage", "Erreur lors du chargement du fichier");
            echo \Template::instance()->render('views/dossiers/listeFactures.html', 'text/html');

        }
    }

    function newMail($f3){
        $fichiers = explode(",",$f3->POST['ListePourGeneration']);
         $this->mail($fichiers);


    }
    private function mail($fichiers){
		global $f3;

		date_default_timezone_set('Etc/UTC');
        $date = date("d/m/Y");
        

        $message = "<br>Lyon, le ".$date ." <br>";
        $message .= "<br>Madame, Monsieur <br>";
        $message .= "Je vous transmet en Piece-Jointe les factures <br>";
        $message .= "Cordialement<br>";
        $message .= "<br>---------------------<br>";
        $message .= "<br>A votre service<br>";
        $message .= "<br>---------------------";

        //$attachement = [];
        foreach($fichiers as $fichier){

            // 1 MAIL PAR DOCUMENT
            //array_push($attachement, 'ui/facturePDF/'.$fichier);
            $f3-> set("objet", "Factures");
            $f3-> set("Message", $message);
            $body = \Template::instance()->render("/mailTemplate.html", 'text/html');
		    $mail = \MailSender::sendMail($f3->get('MAIL.destinataire'), 'Factures', $body, ["attachments"=>['ui/facturePDF/'.$fichier] ]);

            if ($mail){
                $newmail = new MailModel($this->db);
                $newmail->newMail($fichier);
            }

        }


        // MAIL GLOBAL
        // $f3-> set("objet", "Factures");
        // $f3-> set("Message", $message);
        // $body = \Template::instance()->render("/mailTemplate.html", 'text/html');

		// \MailSender::sendMail($f3->get("MAIL.destinataire"), 'Factures', $body, ["attachments"=>$attachement ]);

	}

    public function listeMail($f3){
		$mail = new MailModel($this->db);
		$nomFacture = $f3->PARAMS["NomFacture"];
        $dateEnvoi = $mail->getDate($nomFacture);
		header("Content-Type: application/json");
		echo json_encode($dateEnvoi);
	}

}
