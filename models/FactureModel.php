<?php

class FactureModel extends Model
{
    public function __construct(DB\SQL $db)
	{
		parent::__construct($db, 'factures');
    }

    function getAllFactures(){
        $this->reset();
        $factures =  $this->db->exec("SELECT * FROM factures ORDER BY `factures`.`IdFacture` DESC");
        return $factures;

    }


    function newFacture(){
        global $f3;
        $this->reset();
        $this->NomClient = $f3['NomClient'];
        $this->NumeroClient = $f3['NumClient'];
        $this->NumeroFacture = $f3['NumFacture'];
        $this->NomFacture = $f3['NomFichier'];
        // $this->db->exec("INSERT INTO `factures` ( `NomClient`, ``, ``, ``) VALUES (". ."," .$f3['']." , " .$f3['NomFichier']." , " .$f3['NumFacture']);
        $this->save();
    }
}