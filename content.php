<?php 

// Content classes, based on GoF "Composite" design pattern   

require_once 'foundation.php';
require_once 'query.php';
require_once 'sqlconnect.php';
require_once 'forminterface.php';

class Settings
{
	private $prefix ;
	private $content_table ;
	
	function __construct($pref,$tab) { $this->content_table=$tab ; $this->prefix=$pref ; }
	function ContentTable() { return $this->prefix.$this->content_table ; }
	function Prefix() { return $this->prefix ; }
}


abstract class Content
{
	abstract function Accept($visitor) ;

	abstract function AddChild(Content $Child) ; 
	abstract function DelChild($name) ;
	
	abstract function ReplaceChild_keepname($name,$newchild) ;
	abstract function ReplaceChild_newname($name,$newname,$newchild) ;
	
	private $name ;
	private $par ;
	
	protected $children ;
	private $iterator ;
	
	function __construct($name,$children)
	{
		$this->name=$name ;
		$this->children=$children ;
		$this->par=null ;
				
		// create iterator to itereate children
		$this->iterator=new GofArrayIterator($this->children) ;
	
		// set parent to all chidren
		for ($this->iterator->First(); !$this->iterator->IsDone() ; $this->iterator->Next())
			$this->iterator->Current()->SetPar($this) ;
	}
	
	function GetChildrenIterator() { return $this->iterator ; }
	function GetName() { return $this->name ; }
	function Par() { return $this->par ; }
	function SetPar($par) { $this->par=$par ; }
}

abstract class SimpleContent extends Content 
{ 
	function AddChild(Content $Child) {} 
	function DelChild($name) {}
	function ReplaceChild_keepname($name,$newchild) {}
	function ReplaceChild_newname($name,$newname,$newchild) {}
}

class StringContent extends SimpleContent
{
	private $size ;

	function GetSize() { return $this->size ; } 
	function SetSize($size) { $this->size=$size ; } 

	function Accept($visitor) 
	{ 
		return $visitor->VisitString($this) ;
	}
}

abstract class CompositeContent extends Content
{
	function AddChild(Content $Child) 
	{  
		$Child->SetPar($this) ;
		$this->children[$Child->GetName()]=$Child ; 
	}
	function DelChild($name) { unset($this->children[$name]) ; }
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
	private $display_child ;
	
	function DisplayChild() { return $display_child ; }
	function SetDisplayChild($display_child) { $this->display_child=$display_child ; }
	
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


// Visitor to get html elements from content structure
abstract class HtmlElementVisitor extends ContentVisitor
{
	protected $form_interface ;
	protected $after ;
	
	function __construct($interface,$after=0) { $this->form_interface=$interface ; $this->after=$after ; }
}

// Visitor to get form elements from content structure
class FormElementVisitor extends HtmlElementVisitor
{
	protected $settings ;
	protected $sqlconnect ;
	
	function __construct($settings,$sqlconnect,$interface,$after=0) 
	{ 
		$this->settings=$settings ; 
		$this->sqlconnect=$sqlconnect ; 
		parent::__construct($interface,$after) ;
	}
	
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
		if (!$this->after)
		{
			$ret=$this->form_interface->Fieldset() ;

			// $It=$this->sqlconnect->QueryObjectIterator("select ". from ".$this->settings->ContentTable()." order by ord desc") ;
			$ret.=$this->form_interface->ListInput($content->GetName(),array()) ;

			return $ret ;   
		}
		else
		    return $this->form_interface->Fieldset_end() ;
	}
}

class TableHeadVisitor extends HtmlElementVisitor
{
	function VisitString($string)
	{
		$ret="" ;
		if (!$this->after)
		{
			$ret.=$this->form_interface->TableHeadCol() ;
			$ret.=$string->GetName() ;
			$ret.=$this->form_interface->TableHeadCol_end() ;
		}
		return $ret ;
	}
}

class TableRowVisitor extends HtmlElementVisitor
{
	private $row ;
	
	function SetRow($row) { $this->row=$row ; }
	
	function VisitString($string)
	{
		$ret="" ;
		if (!$this->after)
		{
			$ret.=$this->form_interface->TableCol() ;
			$name=$string->GetName() ;
			$ret.=$this->row->$name ;
			$ret.=$this->form_interface->TableCol_end() ;
		}
		return $ret ;
	}
}



// Visitor to get sql query elements from content structure
class QueryElementVisitor extends ContentVisitor
{
	private $query, $settings ;
	
	function __construct($query,$settings) { $this->query=$query ; $this->settings=$settings ; }
	
	function VisitString($string) 
	{ 
		$this->query->add_select($this->settings->Prefix().$string->Par()->GetName().".".$string->GetName()) ; 
	}
	function VisitMasterTable($mastertable)	
	{ 
		$this->query->add_from($this->settings->Prefix().$mastertable->GetName()) ;
	}
	function VisitMultiDetailTable($mdt)	
	{ 
		$this->query->add_join($this->settings->Prefix().$mdt->GetName(),
				               $this->settings->Prefix().$mdt->GetName().'.'.$mdt->Par()->GetName().'='.$this->settings->Prefix().$mdt->Par()->GetName().'.Id') ;
	}
	function VisitAttributeTable($at)	
	{ 
		$this->query->add_join($this->settings->Prefix().$at->GetName(),
				               $this->settings->Prefix().$at->GetName().'.Id='.$this->settings->Prefix().$at->Par()->GetName().'.'.$at->GetName()) ;
	}
}


// visitor to add type-specific fields to insert query  
class SaveElementVisitor extends ContentVisitor
{
	private $query ;
	
	function __construct($query) { $this->query=$query ; }
	
	function VisitString($string) 
	{ 
		$this->query->add_values("Size",$string->GetSize()) ; 
	}
}


// visitor to add type-specific fields to insert query
class RestoreElementVisitor extends ContentVisitor
{
	private $object ;

	function SetObject($object) { $this->object=$object ; }

	function VisitString($string)
	{
		$string->SetSize($this->object->Size) ;
	}

	function VisitAttributeTable($content)
	{
		$content->SetDisplayChild($this->object->DisplayChild) ;
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


// Builds some html 
abstract class HtmlBuilder extends Builder
{
	protected $result ; // object to build
	protected $before_visitor ;
	protected $after_visitor ;
	protected $output_interface ;

	function __construct($output_interface)
	{
		$this->output_interface=$output_interface ;
		$this->result="" ;
	}

	function Get() { return $this->result ; }

	// implementing builder interface
	function BuildElementStart($element) {	$this->result.=$element->Accept($this->before_visitor) ; }
	function BuildElementEnd($element) { $this->result.=$element->Accept($this->after_visitor) ; }
}

// Builds html form to add new and query content 
class FormBuilder extends HtmlBuilder
{
	function __construct($settings,$sqlconnect,$form_interface)
	{
		$this->before_visitor=new FormElementVisitor($settings,$sqlconnect,$form_interface) ;
		$this->after_visitor=new FormElementVisitor($settings,$sqlconnect,$form_interface,1) ;
		parent::__construct($form_interface) ;
	}
	
	// implementing builder interface
	function BuildStart() { $this->result.=$this->output_interface->Header() ; }
	function BuildEnd() { $this->result.=$this->output_interface->End() ; }
}


// Builds html table head to display content
class TableHeadBuilder extends HtmlBuilder
{
	function __construct($form_interface)
	{
		$this->before_visitor=new TableHeadVisitor($form_interface) ;
		$this->after_visitor=new TableHeadVisitor($form_interface,1) ;
		parent::__construct($form_interface) ;
	}

	// implementing builder interface
	function BuildStart() { $this->result.=$this->output_interface->TableRow() ; }
	function BuildEnd() { $this->result.=$this->output_interface->TableRow_end() ; }
}

// Builds html table row to display content
class TableRowBuilder extends HtmlBuilder
{
	function __construct($form_interface)
	{
		$this->before_visitor=new TableRowVisitor($form_interface) ;
		$this->after_visitor=new TableRowVisitor($form_interface,1) ;
		parent::__construct($form_interface) ;
	}
	
	function SetRow($row) 
	{
		$this->before_visitor->SetRow($row) ;
		$this->after_visitor->SetRow($row) ;
		$this->result="" ;
	}

	// implementing builder interface
	function BuildStart() { $this->result.=$this->output_interface->TableRow() ; }
	function BuildEnd() { $this->result.=$this->output_interface->TableRow_end() ; }
}



// Builds select query to get content 
class QueryBuilder extends Builder
{
	private $query ; // select query object to build
	private $query_visitor ;

	function __construct($settings)
	{
		$this->query=new SqlQuery();
		$this->query_visitor=new QueryElementVisitor($this->query,$settings) ;
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
	private $sqlconnect, $settings ;
	private $order ;

	function __construct($connect,$settings)
	{
		$this->sqlconnect=$connect ;
		$this->settings=$settings ;
		$this->query=new SqlQuery();
		$this->save_visitor=new SaveElementVisitor($this->query) ;
		$order=0 ;
	}

	function GetQuery() { return $this->query->get_query() ; }

	// implementing builder interface
	function BuildElementStart($element) 
	{
		$this->query->Reset() ;
		$this->query->add_insert($this->settings->ContentTable()) ; 
		
		// adding common fields to insert query
		$this->query->add_values("Name",$element->GetName()) ; 
		$this->query->add_values("ClassName",get_class($element)) ;
		if ($par=$element->Par()) 
	  		$this->query->add_values("ParentName",$par->GetName()) ;
		
		
		$element->Accept($this->save_visitor) ; // adding class-specific fields to query 
		
		$this->query->add_values("Ord",++$this->order) ; // adding order
		$this->query->add_values("Chldrn",$element->GetChildrenIterator()->Num()) ; // adding children count
		
		$this->query->add_duplicate("Ord=".$this->order) ; // if allready exists then update order and number of children
		$this->query->add_duplicate("Chldrn=".$element->GetChildrenIterator()->Num()) ;
		
		//echo "<p>".$this->query->get_insert_query()."</p>" ;
		$this->sqlconnect->SimpleQuery($this->query->get_insert_query()) ; 
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
		
		//echo "<br/>I: "  ; print_r($content_element->GetName()) ;
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


class ContentRestorer
{
	private $sqlconnect, $settings ;

	function __construct($connect,$settings) 
	{
		$this->sqlconnect=$connect ;
		$this->settings=$settings ;
	}
	
	function Restore()
	{
		$It=$this->sqlconnect->QueryObjectIterator("select * from ".$this->settings->ContentTable()." order by ord desc") ;

		// creating visitor object to restore specific fields
		$restore_visitor=new RestoreElementVisitor ;
		
		$stack=array() ;
		for ($It->First() ; !$It->IsDone() ; $It->Next()) 
		{
			$row=$It->Current() ;
			
			$classname=$row->ClassName ;
			$chldrn=array_slice($stack,0,$row->Chldrn) ; // get sub-array of children from stack 
			
			// create object of specific class but with generic constructor
			$current_obj=new $classname($row->Name,$chldrn) ; 
			
			// adding class-specific fields to query
			$restore_visitor->SetObject($row) ;
			$current_obj->Accept($restore_visitor) ; 
					
			array_splice($stack,0,$row->Chldrn) ; // shifts stack from used children
			array_unshift($stack,$current_obj) ; // unshift new object to stack 
		}
		return $current_obj ;
	}
	
}


// GoF "Facade" class to generate web page
class View
{
	private $sqlconnect, $settings, $form_interface ;
	
	function __construct($connect,$settings,$form_interface)
	{
		$this->sqlconnect=$connect ;
		$this->settings=$settings ;
		$this->form_interface=$form_interface ;
	}


	function GetView()
	{
		// create restorer object and use it to restore content structure from sql table
		$restorer=new ContentRestorer($this->sqlconnect,$this->settings) ;
		$content=$restorer->Restore() ;
	
		//print_r($content) ;
	
		// create form builder
		$form_builder=new FormBuilder($this->settings,$this->sqlconnect,$this->form_interface) ;
	
		// create query builder
		$query_builder=new QueryBuilder($this->settings) ;
		
		// create table head builder
		$tablehead_builder=new TableHeadBuilder($this->form_interface) ;
		
		// create all_builders object, containing all needed builders
		$all_builders=new Builders(array($form_builder,$query_builder,$tablehead_builder)) ;
		$parser=new ContentParser($all_builders) ;
	
		// parse composite structure with all builders
		$parser->Parse($content) ;
	
		$view=$form_builder->Get() ; // form
	    $view.=$this->form_interface->Table() ;	
		$view.=$tablehead_builder->Get() ; // table head
		
		// builder and parser to build rows
		$row_builder=new TableRowBuilder($this->form_interface) ;
		$row_parser=new ContentParser($row_builder) ;
				
		$outrows=$this->sqlconnect->QueryObjectIterator($query_builder->GetQuery()) ;
		for ($outrows->First() ; !$outrows->IsDone() ; $outrows->Next())
		{
			$row=$outrows->Current() ;
			$row_builder->SetRow($row) ;
			$row_parser->Parse($content) ;
			$view.=$row_builder->Get() ;
		}

		$view.=$this->form_interface->Table_end() ;
		return $view ;
	
	}

}

/*
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
*/

// create settings
$settings=new Settings("sc_", "_content") ;

// create MySqli connection
$db=new MySqliConnector('dollsfun.mysql.ukraine.com.ua','dollsfun_content','93hfkudn', 'dollsfun_content') ;

// create form interface and imlementation
$imp=new HtmlFormImp() ;
$form_interface=new FormInterface($imp) ;

$view=new View($db, $settings, $form_interface) ;
echo $view->GetView() ;

/* temp code to update smth
$defs=$db->QueryObjectIterator("select * from sc_Definitions") ;
for ($defs->First() ; !$defs->IsDone() ; $defs->Next())
{
 $row=$defs->Current() ;

 $db->SimpleQuery("update sc_Words set Topics=".$row->topic_id." where sc_Words.Id=".$row->Words) ;
}
*/
?>