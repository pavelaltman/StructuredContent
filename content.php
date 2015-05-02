<?php 

// Content classes, based on GoF "Composite" design pattern   

require_once 'foundation.php';
require_once 'query.php';
require_once 'sqlconnect.php';
require_once 'forminterface.php';

// application settings, i.e. table names
class Settings
{
	private $prefix ;
	private $content_table ;
	private $state_table ;
	
	function __construct($pref,$cont_tab,$stat_tab) 
	{ 
		$this->content_table=$cont_tab ; $this->state_table=$stat_tab ; $this->prefix=$pref ; 
	}
	function ContentTable() { return $this->prefix.$this->content_table ; }
	function StateTable() { return $this->prefix.$this->state_table ; }
	function Prefix() { return $this->prefix ; }
}



abstract class Content
{
	abstract function Accept($visitor) ;

	abstract function AddChild(Content $Child) ; 
	abstract function DelChild($name) ;
	
	abstract function ReplaceChild_keepname($name,$newchild) ;
	abstract function ReplaceChild_newname($name,$newname,$newchild) ;
	
	abstract function IsLeaf() ;
	function DependsFromParent() { return false ; } 
	
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
	
	function IsLeaf() { return true ;}
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
	private $display_child ;
	 
	function SetDisplayChild($display_child) { $this->display_child=$display_child ; }
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
	function GetChild($name) { return $this->children[$name] ; }	
	
	
	function Accept($visitor) 
	{ 
		return "" ;
	}

	function DisplayChild() { return $this->display_child ; }
	
	function IsLeaf() { return false ;}
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
	function DependsFromParent() { return true ; }
	
	function Accept($visitor)
	{
		return $visitor->VisitMultiDetailTable($this) ;
	}
}

class AttributeTable extends CompositeContent
{
	private $filtered_by_child , $filters_output , $current_value ;
	
	function FilteredByChild() { return $this->filtered_by_child ; }
	function CurrentValue() { return $this->current_value ; }
	
	function SetFilteredByChild($filtered_by_child) { $this->filtered_by_child=$filtered_by_child ; }
	function FiltersOutput() { return $this->filters_output ; }
	function SetFiltersOutput($filters_output) { $this->filters_output=$filters_output ; }
	function SetCurrentValue($current_value) { $this->current_value=$current_value ; }
	
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
	function VisitString($content) { return "" ; } 
	function VisitMasterTable($content) { return "" ; } 
	function VisitMultiDetailTable($content) { return "" ; }
	function VisitAttributeTable($content) { return "" ; }
} 

abstract class ContentVisitorBeforeAfter extends ContentVisitor
{
	protected $after ;
	
	function __construct($after=0) { $this->after=$after ; }
	
	function VisitStringBefore($content) { return "" ; }
	function VisitMasterTableBefore($content) { return "" ; }
	function VisitMultiDetailTableBefore($content) { return "" ; }
	function VisitAttributeTableBefore($content) { return "" ; }
	
	function VisitStringAfter($content) { return "" ; }
	function VisitMasterTableAfter($content) { return "" ; }
	function VisitMultiDetailTableAfter($content) { return "" ; }
	function VisitAttributeTableAfter($content) { return "" ; }
	
	final function VisitString($content) 
	{ return !$this->after ? $this->VisitStringBefore($content) : $this->VisitStringAfter($content) ; }

	final function VisitMasterTable($content)
	{ return !$this->after ? $this->VisitMasterTableBefore($content) : $this->VisitMasterTableAfter($content) ; }

	final function VisitMultiDetailTable($content)
	{ return !$this->after ? $this->VisitMultiDetailTableBefore($content) : $this->VisitMultiDetailTableAfter($content) ; }

	final function VisitAttributeTable($content)
	{ return !$this->after ? $this->VisitAttributeTableBefore($content) : $this->VisitAttributeTableAfter($content) ; }
}


trait SqlConnectable
{
	protected $settings ;
	protected $sqlconnect ;

	function SqlConnectableSet($settings,$sqlconnect)
	{
		$this->settings=$settings ;
		$this->sqlconnect=$sqlconnect ;
	}
	function __construct($settings,$sqlconnect) { $this->SqlConnectableSet($settings,$sqlconnect) ; }
}

class POSTElementVisitor extends ContentVisitor
{
	use SqlConnectable ;
	
	function VisitAttributeTable($content)
	{
		// setting current value from post request
		$content->SetCurrentValue($_POST[$content->GetName()]) ;
		
		// saving current value to state tabe
		$query=new SqlQuery() ;
		$query->add_insert($this->settings->StateTable()) ;
		$query->add_values("Name",$content->GetName()) ;
		$query->add_values("Value",$content->CurrentValue()) ;
		$query->add_duplicate("Value=".$content->CurrentValue()) ;
		$this->sqlconnect->SimpleQuery($query->get_insert_query()) ;
	}
}




// Visitor to get html elements from content structure
class HtmlElementVisitor extends ContentVisitorBeforeAfter
{
	protected $form_interface ;

	function __construct($interface,$after=0)
	{
		$this->form_interface=$interface ;
		parent::__construct($after) ;
	}
}

// Visitor to get form elements from content structure
class FormElementVisitor extends HtmlElementVisitor
{
	use SqlConnectable ;
	
	function __construct($settings,$sqlconnect,$interface,$after=0) 
	{ 
		$this->SqlConnectableSet($settings,$sqlconnect) ;
		parent::__construct($interface,$after) ;
	}
	
	function VisitStringBefore($string)
	{
		$ret="" ;
		//$ret.=$this->form_interface->Paragraf() ;
		$ret.=$this->form_interface->TextInput($string->GetName(),$string->GetSize()) ;
		//$ret.=$this->form_interface->Paragraf_end() ;
		return $ret ;
	}

	function VisitAttributeTableBefore($content) 
	{
		$ret=$this->form_interface->Fieldset() ;

		// fill list with attribute teable rows
		$query="select Id,".$content->DisplayChild()." from ".$this->settings->Prefix().$content->GetName() ;
			
		// add child filter if needed
		$chldnm=$content->FilteredByChild() ;
		if (strlen($chldnm))
		{
			 $chld=$content->GetChild($chldnm) ;
			 $query.=" where ".$chldnm."=".$chld->CurrentValue() ;
			 // echo "<br/>".$chldnm."<br/>".$query ;
		}
			
			
		$It=$this->sqlconnect->QueryObjectIterator($query) ;
		$selected= $content->CurrentValue() ;
		$ret.=$this->form_interface->ListInput($content->GetName(),$It,"Id",$content->DisplayChild(),$selected,$content->DisplayChild()) ;

		return $ret ;   
	}
		
	function VisitAttributeTableAfter($content)
	{ 
		return $this->form_interface->Fieldset_end() ;
	}
}

class TableHeadVisitor extends HtmlElementVisitor
{
	function VisitStringBefore($string)
	{
		$ret="" ;
		$ret.=$this->form_interface->TableHeadCol() ;
		$ret.=$string->GetName() ;
		$ret.=$this->form_interface->TableHeadCol_end() ;
		return $ret ;
	}
}

class TableRowVisitor extends HtmlElementVisitor
{
	private $row ;
	
	function SetRow($row) { $this->row=$row ; }
	
	function VisitStringBefore($string)
	{
		$ret="" ;
		$ret.=$this->form_interface->TableCol() ;
		$name=$string->GetName() ;
		$ret.=$this->row->$name ;
		$ret.=$this->form_interface->TableCol_end() ;
		return $ret ;
	}
}



// Visitor to get sql query elements from content structure
class QueryElementVisitor extends ContentVisitor
{
	private $query, $settings ;
	
	function __construct($query,$settings) 
	{ 
		$this->query=$query ; $this->settings=$settings ; 
	}
	
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
		
		// if this attribute table has current value, then adding filter 
		if ($at->FiltersOutput())
		{
			$value=$at->CurrentValue() ;
			if ($value)
				$this->query->add_where($this->settings->Prefix().$at->Par()->GetName().'.'.$at->GetName().'='.$value) ;
		}
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

	function VisitAttributeTable($content) 
	{ 
		$this->query->add_values("DisplayChild",$content->DisplayChild()) ; 
		$this->query->add_values("FilteredByChild",$content->FilteredByChild()) ; 
		$this->query->add_values("FiltersOutput",$content->FiltersOutput()) ; 
	}
}
	

// visitor to copy type-specific fields from untyped object to content element
class RestoreElementVisitor extends ContentVisitor
{
	private $object ;
	private $post_request ;
	
	function __construct($post) { $this->post_request=$post ; }

	function SetObject($object) { $this->object=$object ; }

	function VisitString($string)
	{
		$string->SetSize($this->object->Size) ;
	}

	function VisitCompositeContent($content)
	{
		$content->SetDisplayChild($this->object->DisplayChild) ;
	}
	function VisitMasterTable($content) { $this->VisitCompositeContent($content) ; }
	function VisitMultiDetailTable($content) { $this->VisitCompositeContent($content) ; }
	
	function VisitAttributeTable($content)
	{
		$this->VisitCompositeContent($content) ;
		$content->SetFilteredByChild($this->object->FilteredByChild) ;
		$content->SetFiltersOutput($this->object->FiltersOutput) ;
		
		if (!$this->post_request)
			$content->SetCurrentValue($this->object->Value) ;
				
	}
}



// GoF "Builder" class to build various objects from content structure 
abstract class Builder
{
	function BuildStart() {}
	function BuildEnd() {}
	function BuildElementStart($element) {}
	function BuildElementEnd($element) {}
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



// Adds filter information to content structure from POST and stores it in database 
class POSTBuilder extends Builder
{
	private $post_visitor ;

	function __construct($settings,$connect)
	{
		$this->post_visitor=new POSTElementVisitor($settings,$connect) ;
	}

	// implementing builder interface
	function BuildElementStart($element) {	$element->Accept($this->post_visitor) ; }
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


// Builds insert query to save content structure for every element and executes it
class SaveBuilder extends Builder
{
	use SqlConnectable ;
	
	private $query ; // insert query object to execute
	private $save_visitor ;
	private $order ;

	function __construct($settings,$connect)
	{
		$this->SqlConnectableSet($settings, $connect) ;
		$this->query=new SqlQuery();
		$this->save_visitor=new SaveElementVisitor($this->query) ;
		$order=0 ;
	}

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


// inserts data into user tables from POST request 
class InsertBuilder extends Builder
{
	use SqlConnectable ;
	
	// inserts data to one user table, asumes dependent tables were processed before
	function BuildElementStart($element)
	{
		if (strlen($_POST[$element->DisplayChild()]))
		{
			$query=new SqlQuery ;
			$query->add_insert($this->settings->Prefix().$element->GetName()) ;
			
			// iterate children, except those depending on this, to add to insert query
			$It=$element->GetChildrenIterator() ;
			for($It->First(); !$It->IsDone(); $It->Next())
			{
				$ch=$It->Current() ;
				if (!$ch->DependsFromParent())
					$query->add_values($ch->GetName(), $_POST[$ch->GetName()]) ;
			}
			// adding parent table if this depends from parent
			if ($element->DependsFromParent())
				$query->add_values($element->Par()->GetName(), $_POST[$element->Par()->GetName()]) ;
				
			// insert data and then add Id to $_POST
			$this->sqlconnect->SimpleQuery($query->get_insert_query()) ;
			$_POST[$element->GetName()]=$this->sqlconnect->InsertId() ;
		}
		
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

	function ParseCompositesByDependency($content_element)
	{
		$iterator=$content_element->GetChildrenIterator() ;
		
		// first, parse elements not depending from this  
		for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
		{
			$el=$iterator->Current() ;
			if (!$el->DependsFromParent() && !$el->IsLeaf())
				$this->ParseCompositesByDependency($el) ;
		}
		
		// handle this element
		$this->builders->BuildElementStart($content_element) ;
		
		// now parse elements depending from this
		for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
		{
			$el=$iterator->Current() ;
			if ($el->DependsFromParent() && !$el->IsLeaf())
				$this->ParseCompositesByDependency($el) ;
		}
	}
}


class ContentRestorer
{
	use SqlConnectable ;
	
	private $post_request ;

	function __construct($settings,$connect,$post) 
	{
		$this->SqlConnectableSet($settings, $connect) ;
		$this->post_request=$post ;
	}
	
	function Restore()
	{
		$query=new SqlQuery() ;
		$query->add_select($this->settings->ContentTable().".*") ;
		$query->add_from($this->settings->ContentTable()) ;
		$query->add_order("ord desc") ;
		if (!$this->post_request)
		{
			$query->add_select("Value") ;
			$query->add_join($this->settings->StateTable(),$this->settings->StateTable().".Name=".$this->settings->ContentTable().".Name") ;
		}	
		  
		$It=$this->sqlconnect->QueryObjectIterator($query->get_query()) ;

		// creating visitor object to restore specific fields
		$restore_visitor=new RestoreElementVisitor ;
		
		$stack=array() ;
		for ($It->First() ; !$It->IsDone() ; $It->Next()) 
		{
			$row=$It->Current() ;
			
			$classname=$row->ClassName ;
			
			$_chldrn=array_slice($stack,0,$row->Chldrn) ; // get sub-array of children from stack 

			// changing numeric keys to names 
			$chldrn=array() ;
			foreach($_chldrn as $key => $value)
			{
				$chldrn[$value->GetName()]=$value ;
			}	
			
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
	use SqlConnectable ;
	
	private $form_interface ;
	
	function __construct($settings,$connect,$form_interface)
	{
		$this->SqlConnectableSet($settings, $connect) ;
		$this->form_interface=$form_interface ;
	}


	function GetView()
	{
		$post= ($_SERVER['REQUEST_METHOD']=='POST') ;
		
		// create restorer object and use it to restore content structure from sql table
		$restorer=new ContentRestorer($this->settings,$this->sqlconnect,$post) ;
		$content=$restorer->Restore() ;
	

		if ($post)
		{
			// insert data into user tables 
			$insert_builder=new InsertBuilder($this->settings,$this->sqlconnect) ;
			$insert_parser=new ContentParser($insert_builder) ;
			$insert_parser->ParseCompositesByDependency($content) ;
			
			// copy values from $_POST and save to state table
			$post_builder=new POSTBuilder($this->settings,$this->sqlconnect) ;
			$post_parser=new ContentParser($post_builder) ;
			$post_parser->Parse($content) ;
		}
		
		
		// create form builder
		$form_builder=new FormBuilder($this->settings,$this->sqlconnect,$this->form_interface) ;
	
		// create query builder
		$query_builder=new QueryBuilder($this->settings) ;
		
		// create table head builder
		$tablehead_builder=new TableHeadBuilder($this->form_interface) ;
		
		$builders_array=array($form_builder,$query_builder,$tablehead_builder) ;
		
		// print_r($builders_array) ;
		
		// create all_builders object, containing all needed builders
		$all_builders=new Builders($builders_array) ;
		
		// parse composite structure with all builders
		$parser=new ContentParser($all_builders) ;
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
$settings=new Settings("sc_", "_content","_state") ;

// create MySqli connection
$db=new MySqliConnector('dollsfun.mysql.ukraine.com.ua','dollsfun_content','93hfkudn', 'dollsfun_content') ;

// create form interface and imlementation
$imp=new HtmlFormImp() ;
$form_interface=new FormInterface($imp) ;

$view=new View($settings,$db,$form_interface) ;
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