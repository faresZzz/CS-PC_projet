<?php


class Erreur extends Controller
{
    function envoiMessage($f3){

        $image = $f3->POST['image'];
        $message = $f3->POST['messageErreur'];
        $currentURL = $f3->POST['urlErreur'];
        $session = $f3->get("SESSION");
        $agent = $f3->get("AGENT");
        

        $f3-> set("objet", "ERREUR SITE");
        $f3-> set("Message", $message);
        $f3-> set("image", $image);
        $f3-> set("urlPageErreur", $currentURL);
        $body =  \Template::instance()->render("/template_erreur.html", 'text/html');
        \MailSender::sendMail($f3->get('MAIL.destinataire'), 'Erreur', $body, ["attachments"=>[$image] ]);


    }

    function test($f3){
        $f3->reroute('/');
    }
}