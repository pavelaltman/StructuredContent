<?php

require_once 'foundation.php';

// interface to work with any sql database
abstract class SqlConnector
{
	abstract function QueryObject($query,$classname,$params) ; // returns one (first) result raw as object ;
	abstract function SimpleQuery($query) ; 
	abstract function QueryObjectIterator($query) ;
	abstract function InsertId() ;
}


class GofMySqliResultIterator extends GofIterator
{
	private $result ;
	private $i ;

	function __construct($result) 
	{ 
		$this->result=$result ; 
	}
	function First() { $this->i=0 ; }
	function Next() { $this->result->data_seek(++$this->i) ; }
	Function IsDone() { return $this->i >= $this->result->num_rows ; }
	Function &Current() { return $this->result->fetch_object() ; }
	Function Num() { return $this->result->num_rows ; }
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

	function SimpleQuery($query) 
	{ 
		if (!$this->connection->query($query))
			echo $this->connection->error ; 
	} 
	
	function QueryObjectIterator($query)
	{
		$this->result=$this->connection->query($query) ;
		return new GofMySqliResultIterator($this->result) ;
	}
	
	function InsertId() { return $this->connection->insert_id ; }
}



?>