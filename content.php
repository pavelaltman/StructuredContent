<?php 

// Content classes, based on GoF "Composite" design pattern   

require_once 'forminterface.php';

abstract class Content
{
	abstract function GetFormElement($form_interface) ;
	abstract function GetFormElement_end($form_interface) ;
	abstract function AddChild(Content $Child) ; 
	abstract function DelChild($name) ;
	abstract function &GetChildren() ;
	abstract function ReplaceChild_keepname($name,$newchild) ;
	abstract function ReplaceChild_newname($name,$newname,$newchild) ;
	
	
	private $name ;
	
	function __construct($name) { $this->name=$name ; }
	function GetName() { return $this->name ; }
}

abstract class SimpleContent extends Content 
{ 
	function AddChild(Content $Child) {} 
	function DelChild($name) {}
	function &GetChildren() { return array(); }
	function ReplaceChild_keepname($name,$newchild) {}
	function ReplaceChild_newname($name,$newname,$newchild) {}
	
	function GetFormElement_end($form_interface) { return "" ; } 
}

class StringContent extends SimpleContent
{
	private $size ;

	function GetSize() { return $this->size ; } 
	function __construct($name,$size) { $this->size=$size ; parent::__construct($name) ; }
	function GetFormElement($form_interface) 
	{ 
		return $form_interface->TextInput($this->GetName(),$this->GetSize()) ;
	}
}

abstract class CompositeContent extends Content
{
	private $children ;
	
	function __construct($name,$children) { $this->children=$children ; parent::__construct($name) ; }
	function AddChild(Content $Child) { $this->children[$Child->GetName()]=$Child ; }
	function DelChild($name) { unset($this->children[$name]) ; }
	function &GetChildren() { return $this->children ; }
	function ReplaceChild_keepname($name,$newchild) 
	{ 
			$this->children[$name]=$newchild ;
	}
	function ReplaceChild_newname($name,$newname,$newchild)
	{
		$this->children[$newname]=$newchild ;
		unset($this->children[$name]) ;
	}
	
	function GetFormElement($form_interface) {	return "" ;	}
	function GetFormElement_end($form_interface)  {	return "" ;	}
}

class MasterTable extends CompositeContent
{
}

class MultiDetailTable extends CompositeContent
{
}

class AttributeTable extends CompositeContent
{
	function GetFormElement($form_interface) {	return $form_interface->Fieldset() ; }
	function GetFormElement_end($form_interface)  {	return $form_interface->Fieldset_end() ; }
}


// GoF "Decorator" class family 
abstract class FormDecorator extends Content
{
	private $Child, $childkey ;
	function __construct($name,$Child,$childkey) 
	{ 
		$this->Child=array() ; 
		$this->childkey= $childkey ; 
		$this->Child[$childkey]=$Child ; 
		parent::__construct($name) ; 
	}
	
	function &GetChildren() { return $this->Child ; }
	function GetChild() { return $this->Child[$this->childkey] ; }

	function AddChild(Content $Child) {} 
	function DelChild($name) {}
	function ReplaceChild_keepname($name,$newchild) 
	{ 
		$this->Child[$this->childkey]=$newchild ; 
	}
	function ReplaceChild_newname($name,$newname,$newchild)
	{
		$this->Child[$newname]=$newchild ;
		unset($this->Child[$this->childkey]) ;
		$this->Child[$this->childkey]=$newname ;
	}
	function GetFormElement($form_interface) { return "" ; }
	function GetFormElement_end($form_interface) { return "" ; }
	
}


class GroupDecorator extends FormDecorator
{
}


// GoF "Strategy" 
abstract class FormBuilder 
{
	private $form_interface ;

	function __construct($interface) { $this->form_interface=$interface ; }
	function GetFormInterface() { return $this->form_interface ; } 
	
	abstract function Build($form) ;
} 

// Builds form with basic elements only
class SimpleFormBuilder extends FormBuilder
{
	function BuildElement($form_element)
	{
		$ret=$form_element->GetFormElement($this->GetFormInterface()) ;
		
		foreach ($form_element->GetChildren() as $key => $child)
			$ret.=$this->BuildElement($child) ;
				
		$ret.=$form_element->GetFormElement_end($this->GetFormInterface()) ;
		
		return $ret ;
	}

	
	function Build($form)
	{
     $ret=$this->GetFormInterface()->Header() ;
     $ret.=$this->BuildElement($form) ;
     $ret.=$this->GetFormInterface()->End() ;
     return $ret ;
	}	
	
}


$form=new MasterTable('Words',array(
		                     'Word' => new StringContent('Word',20),
		                	 'Definitions' => new MultiDetailTable('Definitions',array(
		                	 	      'Parts' => new AttributeTable('Parts', array(
		                	 	      		'Part' => new StringContent('Part', 10)
		                	 	      )),    	
		                	 	      'Definition' => new StringContent('Definition', 100),    	
		                	 		  'Example' => new StringContent('Example', 100)    	
		                	 )),
		               		 'Topics' => new AttributeTable('Topics', array(
		               		 		'Topic' => new StringContent('Topic', 30),
		               		 		'Themes' => new AttributeTable('Themes', array(
		               		 				'Theme' => new StringContent('Theme', 30)
		               		 		))
		               		 ))
		                     )
		             ) ;

print_r($form) ;

$imp=new HtmlFormImp() ;
$form_interface=new FormInterface($imp) ;
$builder=new SimpleFormBuilder($form_interface) ;

echo $builder->Build($form) ;
?>