<?php 

// Content classes, based on GoF "Composite" design pattern   

require_once 'foundation.php';
require_once 'forminterface.php';

abstract class Content
{
	abstract function GetFormElement($form_interface) ;
	abstract function GetFormElement_end($form_interface) ;
	abstract function AddChild(Content $Child) ; 
	abstract function DelChild($name) ;
	abstract function GetChildrenIterator() ;
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
	function GetChildrenIterator() { global $null_iterator ; return $null_iterator ; }
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
	protected $children ;
	private $iterator ;
	
	function __construct($name,$children) 
	{ 
		$this->children=$children ;
		$this->iterator=new GofArrayIterator($this->children) ;
		parent::__construct($name) ; 
	}
	function AddChild(Content $Child) { $this->children[$Child->GetName()]=$Child ; }
	function DelChild($name) { unset($this->children[$name]) ; }
	function GetChildrenIterator() { return $this->iterator ; }
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
abstract class FormDecorator extends CompositeContent
{
	private $childkey ;
	function __construct($name,$Child,$childkey) 
	{ 
		$this->childkey= $childkey ; 
		parent::__construct($name,$Child) ; 
	}
	
	function GetChild() { return $this->children[$this->childkey] ; }

	function AddChild(Content $Child) {} 
	function DelChild($name) {}
	function ReplaceChild_keepname($name,$newchild) 
	{ 
		$this->children[$this->childkey]=$newchild ; 
	}
	function ReplaceChild_newname($name,$newname,$newchild)
	{
		$this->children[$newname]=$newchild ;
		unset($this->children[$this->childkey]) ;
		$this->children[$this->childkey]=$newname ;
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
		
		$iterator=$form_element->GetChildrenIterator() ;
		for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
			$ret.=$this->BuildElement($iterator->Current()) ;
				
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