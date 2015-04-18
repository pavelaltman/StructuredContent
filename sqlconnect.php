<?php

// interface to work with any sql database
abstract class SqlConnector
{
	abstract function QueryObject($query,$classname,$params) ; // returns one (first) result raw as object ;
	abstract function SimpleQuery($query) ; 
	// abstract function QueryObjectIterator($query,$classname,$params) ;
}

// mysql implementation of SqlConnect 
class MySqliConnector extends SqlConnector
{
	private $connection ;
	private $result ;

	function __construct($host,$username,$pass,$dbname)
	{
		$this->connection=new mysqli($host,$username,$pass,$dbname) ;
	}
	
	function QueryObject($query, $classname="stdClass", $params=0)
	{
		$this->result=$this->connection->query($query) ;
		return $params ? $this->result->fetch_object($classname,$params) : $this->result->fetch_object($classname) ;
	}

	function SimpleQuery($query) { $this->result=$this->connection->query($query) ; } 
}



?>