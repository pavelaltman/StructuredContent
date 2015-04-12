<?php 

// Content classes, based on GoF "Composite" design pattern   

require_once 'foundation.php';
require_once 'forminterface.php';

abstract class Content
{
	abstract function Accept($visitor) ;

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
}

class StringContent extends SimpleContent
{
	private $size ;

	function GetSize() { return $this->size ; } 
	function __construct($name,$size) { $this->size=$size ; parent::__construct($name) ; }

	function Accept($visitor) 
	{ 
		return $visitor->VisitString($this) ;
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
	
	function Accept($visitor) 
	{ 
		return "" ;
	}
}

class MasterTable extends CompositeContent
{
}

class MultiDetailTable extends CompositeContent
{
}

class AttributeTable extends CompositeContent
{
	function Accept($visitor)
	{
		return $visitor->VisitAttributeTable($this) ;
	}
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
}


class GroupDecorator extends FormDecorator
{
}


// GoF "Visitor" classes to get specific information from content structure
abstract class ContentVisitor
{
	function VisitString($content) {} 
	function VisitMasterTable($content) {} 
	function VisitMultiDetailTable($content) {}
	function VisitAttributeTable($content) {}
} 

class FormElementVisitor extends ContentVisitor
{
	private $form_interface ;
	private $after ;
	
	function __construct($interface,$after=0) { $this->form_interface=$interface ; $this->after=$after ; }
	
	function VisitString($string)
	{
		$ret="" ;
		if (!$this->after)
		{
			$ret.=$this->form_interface->Paragraf() ;
			$ret.=$this->form_interface->TextInput($string->GetName(),$string->GetSize()) ;
			$ret.=$this->form_interface->Paragraf_end() ;
		}
		return $ret ;
	}

	function VisitAttributeTable($content) 
	{
		if ($this->after)
			 return $this->form_interface->Fieldset_end() ;
		else 
		{
			$ret=$this->form_interface->Fieldset() ;
			$ret.=$this->form_interface->ListInput($content->GetName(),array()) ;
			return $ret ;   
		}
	}
}


// GoF "Builder" class to build various objects from content structure 
abstract class Builder
{
	abstract function BuildStart() ;
	abstract function BuildEnd() ;
	abstract function BuildElementStart($form_element) ;
	abstract function BuildElementEnd($form_element) ;
}


class FormBuilder extends Builder
{
	private $form ; // Form object to build
	private $form_visitor ;
	private $form_visitor_after ;
	private $form_interface ;
	
	function __construct($form_interface)
	{
		$this->form_visitor=new FormElementVisitor($form_interface) ;
		$this->form_visitor_after=new FormElementVisitor($form_interface,1) ;
		$this->form_interface=$form_interface ;
		$this->form="" ;
	}
	
	function GetForm() { return $this->form ; }
	
	// implementing builder interface
	function BuildStart() {	$this->form=$this->form_interface->Header() ; }
	function BuildEnd() {	$this->form.=$this->form_interface->End() ; }
	function BuildElementStart($form_element) {	$this->form.=$form_element->Accept($this->form_visitor) ; }
	function BuildElementEnd($form_element) { $this->form.=$form_element->Accept($this->form_visitor_after) ; }
}

// Object to parse content structure
class ContentParser
{
	private $form_builder ;
	
	function __construct($builder) { $this->form_builder=$builder ; }
	
	function ParseElement($content_element)
	{
		$this->form_builder->BuildElementStart($content_element) ;
		
		$iterator=$content_element->GetChildrenIterator() ;
		for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
			$this->ParseElement($iterator->Current()) ;
				
		$this->form_builder->BuildElementEnd($content_element) ;
	}

	
	function Parse($content)
	{
		$this->form_builder->BuildStart() ;
		$this->ParseElement($content) ;
		$this->form_builder->BuildEnd() ;
	}	
}


$content=new MasterTable('Words',array(
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

// print_r($form) ;

$imp=new HtmlFormImp() ;
$form_interface=new FormInterface($imp) ;
$builder=new FormBuilder($form_interface) ;
$parser=new ContentParser($builder) ;
$parser->Parse($content) ;

echo $builder->GetForm() ;
?>