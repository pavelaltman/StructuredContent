<?php 

abstract class GofIterator
{
	abstract function First() ;	
	abstract function Next() ;	
	abstract function IsDone() ;	
	abstract function &Current() ;	
	abstract function Num() ;
}

class GofNullIterator extends GofIterator
{
	function First() {}
	function Next() {}
	function IsDone() { return true ; }
	function &Current() { return null ; }
	function Num() { return 0 ; }
}

$null_iterator=new GofNullIterator ;

class GofArrayIterator extends GofIterator
{
	private $arr ;
	private $i ;
	
	function __construct($array) { $this->arr=$array ; } 
	function First() { $this->i=0 ; reset($this->arr) ; }
	function Next() { $this->i=$this->i+1 ; next($this->arr) ; }
	Function IsDone() { return $this->i >= count($this->arr) ; }
	Function &Current() { return current($this->arr) ; }
	Function Num() { return count($this->arr) ; }
}



?>