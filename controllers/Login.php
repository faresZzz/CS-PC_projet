<?php

class Login extends Controller {

	private $_login ="admin" ;
	private $_password ='$2y$10$r84DfIPS8wXeaSfIpXfgm.ZU96gWbYQUdNbTe1tNPTTKS9RwGCZQK' ;

	/*ACCUEIL*/
    function display($f3) {
		$f3->clear('SESSION.csrf');
		$f3->set('AGENT',$f3->get('AGENT'));
		$rand= mt_rand(792207, 79070500);
		$f3->set('csrf',$rand);
		$f3->set('SESSION.csrf',$rand);
		echo \Template::instance()->render('views/login/login.html','text/html');

    }
	/*LISTE DES LOGS*/
	function Connexion($f3){
		if ($f3->get('POST.csrf') == $f3->get('SESSION.csrf')) {
			$data = $f3->get('POST');
			$valid = Validate::is_valid($data, array("Login"=>"required|max_len,100|min_len,3", "Password"=>"required|max_len,50|min_len,5"));

			if($valid){
				$login = $data['Login'];
				$password = $data['Password'];

				if ($login == $this->_login && password_verify($password, $this->_password)){
					$f3->set('SESSION.username',$login);
					$f3->set('SESSION.password',$password);
					$f3->set('SESSION.Agent',$f3->get('AGENT'));
					$f3->set('SESSION.level',"admin");
					//session_regenerate_id();
					$f3->reroute('/views/listeFactures');


				}
				else{
					// LOGIN OR PASSWORD ERROR
					sleep(2);
					//BAD LOGIN
					//LOGE DE LA TENTATIVE
					$logger = new \Log('/logs/session.log');
					$logger->write('BAD LOGIN : '.$f3->get('IP').' | Login : '.$login.' | Route : '.$f3->get('PATH'));
					//
					$f3->clear('SESSION');
					$f3->clear('CACHE');
					$f3->set("messageLogin","Utilisateur ou mot de passe incorrect !");
					echo \Template::instance()->render('views/login/login.html','text/html');
				}
			}
			else{
				// INVALIDE INPUT
				$f3->set("messageInvalideInput","Votre saisie ne respecte pas les champs obligatoires");
				echo \Template::instance()->render('views/login/login.html','text/html');
			}
		}

	}


	function Logout($f3) {
		$f3->clear('SESSION');
		session_start();
		session_destroy();
		session_commit();
		$f3->reroute('/');
	}

}
