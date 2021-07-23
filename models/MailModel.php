<?php

class MailModel extends Model
{
    public function __construct(DB\SQL $db)
	{
		parent::__construct($db, 'mail');
    }


    function newMail($nomFacture){
        $this->reset();
        $this->NomFacture = $nomFacture;
        $this->save();
    }

    function getDate($nomFacture){
        $this->reset();
        return $this->db->exec("SELECT * , DATE_FORMAT(DateEnvoi,'%d/%m/%Y') as DateEnvoi FROM mail WHERE NomFacture = ?", [$nomFacture]);

    }
}