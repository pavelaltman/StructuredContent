<?php 

// Content classes, based on GoF "Composite" design pattern   

require_once 'foundation.php';
require_once 'query.php';
require_once 'sqlconnect.php';
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
	private $par ;
	
	function __construct($name) { $this->name=$name ; $par=null ; }
	function GetName() { return $this->name ; }
	function Par() { return $this->par ; }
	function SetPar($par) { $this->par=$par ; }
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

		// create iterator to itereate children  
		$this->iterator=new GofArrayIterator($this->children) ;
		
		// set parent to all chidren
		for ($this->iterator->First(); !$this->iterator->IsDone() ; $this->iterator->Next())
			$this->iterator->Current()->SetPar($this) ;
		
		parent::__construct($name) ; 
	}
	function AddChild(Content $Child) 
	{  
		$Child->SetPar($this) ;
		$this->children[$Child->GetName()]=$Child ; 
	}
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
	function Accept($visitor)
	{
		return $visitor->VisitMasterTable($this) ;
	}
}

class MultiDetailTable extends CompositeContent
{
	function Accept($visitor)
	{
		return $visitor->VisitMultiDetailTable($this) ;
	}
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


// Visitor to get form elements from content structure
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


// Visitor to get sql query elements from content structure
class QueryElementVisitor extends ContentVisitor
{
	private $query ;
	
	function __construct($query) { $this->query=$query ; }
	
	function VisitString($string) { $this->query->add_select($string->Par()->GetName().".".$string->GetName()) ; }
	function VisitMasterTable($mastertable)	{ $this->query->add_from($mastertable->GetName()) ;}
	function VisitMultiDetailTable($mdt)	
	{ 
		$this->query->add_join($mdt->GetName(),$mdt->GetName().'.'.$mdt->Par()->GetName().'='.$mdt->Par()->GetName().'.Id') ;
	}
	function VisitAttributeTable($at)	
	{ 
		$this->query->add_join($at->GetName(),$at->GetName().'.Id='.$at->Par()->GetName().'.'.$at->GetName()) ;
	}
}

class SaveElementVisitor extends ContentVisitor
{
	private $query ;
	
	function __construct($query) { $this->query=$query ; }
	
	function VisitString($string) 
	{ 
		$this->query->add_values("Size",$string->GetSize()) ; 
	}
}



// GoF "Builder" class to build various objects from content structure 
abstract class Builder
{
	function BuildStart() {}
	function BuildEnd() {}
	function BuildElementStart($form_element) {}
	function BuildElementEnd($form_element) {}
}

// array of builders to build several objects at one parse
class Builders extends Builder
{
	private $builders ;
	private $it ;

	function __construct($builders)
	{
		$this->builders=$builders ;
		$this->it=new GofArrayIterator($this->builders) ;
	}

	function BuildStart() { for ($this->it->First() ; !$this->it->IsDone() ; $this->it->Next()) $this->it->Current()->BuildStart() ; }
	function BuildEnd() { for ($this->it->First() ; !$this->it->IsDone() ; $this->it->Next()) $this->it->Current()->BuildEnd() ; }
	function BuildElementStart($el) { for ($this->it->First() ; !$this->it->IsDone() ; $this->it->Next()) $this->it->Current()->BuildElementStart($el) ; }
	function BuildElementEnd($el) { for ($this->it->First() ; !$this->it->IsDone() ; $this->it->Next()) $this->it->Current()->BuildElementEnd($el) ; }
}

// Builds html form 
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


// Builds select query to get content 
class QueryBuilder extends Builder
{
	private $query ; // select query object to build
	private $query_visitor ;

	function __construct()
	{
		$this->query=new SqlQuery();
		$this->query_visitor=new QueryElementVisitor($this->query) ;
	}

	function GetQuery() { return $this->query->get_query() ; }

	// implementing builder interface
	function BuildElementStart($element) {	$element->Accept($this->query_visitor) ; }
}


// Builds insert query to save content structure
class SaveBuilder extends Builder
{
	private $query ; // insert query object to execute
	private $save_visitor ;

	function __construct()
	{
		$this->query=new SqlQuery();
		$this->save_visitor=new SaveElementVisitor($this->query) ;
	}

	function GetQuery() { return $this->query->get_query() ; }

	// implementing builder interface
	function BuildElementStart($element) 
	{
		$this->query->Reset() ;
		$this->query->add_insert("sc_content") ; 
		$this->query->add_values("Name",$element->GetName()) ; 
		$this->query->add_values("ClassName",get_class($element)) ;
		if ($par=$element->Par()) 
	  		$this->query->add_values("ParentName",$par->GetName()) ;
		$element->Accept($this->save_visitor) ;
		print("<p>".$this->query->get_insert_query()."</p>") ; 
	}
}


// Object to parse content structure
class ContentParser
{
	private $builders ;
	
	function __construct($builders) { $this->builders=$builders ; }
	
	function ParseElement($content_element)
	{
		$this->builders->BuildElementStart($content_element) ;
		
		$iterator=$content_element->GetChildrenIterator() ;
		for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
			$this->ParseElement($iterator->Current()) ;
				
		$this->builders->BuildElementEnd($content_element) ;
	}

	
	function Parse($content)
	{
		$this->builders->BuildStart() ;
		$this->ParseElement($content) ;
		$this->builders->BuildEnd() ;
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

// create form builder
$imp=new HtmlFormImp() ;
$form_interface=new FormInterface($imp) ;
$form_builder=new FormBuilder($form_interface) ;

// create query builder
$query_builder=new QueryBuilder() ;

// create save builder
$save_builder=new SaveBuilder() ;

// create all_builders object, containing all needed builders
$all_builders=new Builders(array($form_builder,$query_builder,$save_builder)) ;
$parser=new ContentParser($all_builders) ;

// parse composite structure with all builders
$parser->Parse($content) ;

echo $form_builder->GetForm() ;
echo $query_builder->GetQuery() ;

$db=new MySqliConnector('dollsfun.mysql.ukraine.com.ua','dollsfun_content','93hfkudn', 'dollsfun_content') ;
$x=$db->QueryObject("select * from wl_state") ;

print_r($x) ;
?>