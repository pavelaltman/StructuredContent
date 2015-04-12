<?php

abstract class FormImp
{
	abstract function Header($url) ;
	abstract function End() ;
	abstract function TextInput($name,$size) ;
	abstract function ListInput($name,$opt_arr) ;
	abstract function Fieldset() ;
	abstract function Fieldset_end() ;
	abstract function Paragraf() ;
	abstract function Paragraf_end() ;
}

class HtmlFormImp extends FormImp
{
	function Header($url) { return '<form action="'.$url.'">' ; }
	function End() { return '</form>' ; } 
	function TextInput($name,$size) { return $name.' <input type="'.'text" name="'.$name.'" size="'.$size.'">' ; }

	function ListInput($name,$opt_arr) 
	{ 
		$ret='<select name="'.$name.'"> <option value="0"> '.$name.'</option>' ;
		// insert loop here
		$ret.='</select>' ;
		return $ret ; 
	}

	function Fieldset() { return '<fieldset>' ; } 
	function Fieldset_end() { return '</fieldset>' ; } 
	function Paragraf() { return '<p>' ; } 
	function Paragraf_end() { return '</p>' ; } 
}

class FormInterface
{
	private $imp ;

	function __construct($imp) { $this->imp=$imp ; }
	function Header() { return $this->imp->Header("/") ; }
	function End() { return $this->imp->End() ; }
	function Fieldset() { return $this->imp->Fieldset() ; }
	function Fieldset_end() { return $this->imp->Fieldset_end() ; }
	function Paragraf() { return $this->imp->Paragraf() ; }
	function Paragraf_end() { return $this->imp->Paragraf_end() ; }
	function TextInput($name,$size) { return $this->imp->TextInput($name,$size) ; }
	function ListInput($name,$opt_arr) { return $this->imp->ListInput($name,$opt_arr) ; }
}


?>