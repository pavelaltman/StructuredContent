<?php

abstract class FormImp
{
	abstract function Header($url) ;
	abstract function End() ;
	abstract function TextInput($name,$size) ;
	abstract function Fieldset() ;
	abstract function Fieldset_end() ;
}

class HtmlFormImp extends FormImp
{
	function Header($url) { return '<form action="'.$url.'">' ; }
	function End() { return '</form>' ; } 
	function Fieldset() { return '<fieldset>' ; } 
	function Fieldset_end() { return '</fieldset>' ; } 
	function TextInput($name,$size) { return $name.' <input type="'.'text" name="'.$name.'" size="'.$size.'">' ; }
}

class FormInterface
{
	private $imp ;

	function __construct($imp) { $this->imp=$imp ; }
	function Header() { return $this->imp->Header("/") ; }
	function End() { return $this->imp->End() ; }
	function Fieldset() { return $this->imp->Fieldset() ; }
	function Fieldset_end() { return $this->imp->Fieldset_end() ; }
	function TextInput($name,$size) { return $this->imp->TextInput($name,$size) ; }
}


?>