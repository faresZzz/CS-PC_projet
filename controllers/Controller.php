<?php
class Controller {

	protected $f3;
    protected $db;

	function __construct() {
		$f3=Base::instance();
		$this->f3=$f3;
	    $db=new DB\SQL(
	        $f3->get('MYSQL.dsn'),
	        $f3->get('MYSQL.user'),
	        $f3->get('MYSQL.password'),
	        array( \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true,\PDO::MYSQL_ATTR_COMPRESS,true  )
	    );
		$db->logging=FALSE;
	    $this->db=$db;
	}

}