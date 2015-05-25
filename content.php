<?php 

require_once 'foundation.php';
require_once 'query.php';
require_once 'sqlconnect.php';
require_once 'forminterface.php';
require_once 'command.php';

// application settings, i.e. table names
class Settings
{
	private $prefix ;
	private $content_table ;
	private $state_table ;
	private $main_form_id ;
	private $content_types ;
	
	function __construct($pref,$cont_tab,$stat_tab,$form_id,$types) 
	{ 
		$this->content_table=$cont_tab ; 
		$this->state_table=$stat_tab ; 
		$this->prefix=$pref ; 
		$this->main_form_id=$form_id ;
		
		// create array of objects as neeeded in form select 
		$this->content_types=array() ;
		foreach($types as $type)
		{
			$obj=new stdClass ; 
			$obj->ClassName=$type ;
			$this->content_types[]=$obj ;
		}	
	}
	function ContentTable() { return $this->prefix.$this->content_table ; }
	function StateTable() { return $this->prefix.$this->state_table ; }
	function Prefix() { return $this->prefix ; }
	function MainFormId() { return $this->main_form_id ; }
	function GetTypesIterator() { return new GoFArrayIterator($this->content_types) ; }
}



// Content classes, based on GoF "Composite" design pattern   
abstract class Content
{
	abstract function Accept($visitor,$t_par) ;

	//abstract function AddChild(Content $Child) ; 
	//abstract function DelChild($name) ;
	
	//abstract function ReplaceChild_keepname($name,$newchild) ;
	//abstract function ReplaceChild_newname($name,$newname,$newchild) ;
	
	abstract function IsLeaf() ;
	function DependsFromParent() { return false ; } 
	function IsTableContent() { return false ; }
	function IsReference() { return false ; }
	
	function GetSize() { return "" ; }
	function DisplayChild() { return "" ; }
	function FilteredByChild() { return "" ; }
	function FiltersOutput() { return false ; }
	
	
	
	public $name ;
	private $par ;
	
	protected $children ;
		
	function __construct($name,$children)
	{
		$this->name=$name ;
		$this->children=$children ;
		$this->par=null ;
				
		// set parent to all chidren
		$It=$this->GetChildrenIterator() ;
		for ($It->First(); !$It->IsDone() ; $It->Next())
			 $It->Current()->SetPar($this) ;
	}
	
	function GetChildrenIterator() { return new GofArrayIterator($this->children) ; }
	function GetName() { return $this->name ; }
	function Par() { return $this->par ; }
	function SetPar($par) { $this->par=$par ; }
	
	// useful functions to bypass reference in traversing 
	function UpperTableName() { return $this->name ; } // overloaded in Reference
	function LowerName() { return $this->name; } // overloaded in Reference
	
	function GetElementByName($name)
	{
		if ($this->GetName()==$name) 
			return $this ;
		
		if (!$this->IsReference())
		{
			$iterator=$this->GetChildrenIterator() ;
			for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
			{
				$ret=$iterator->Current()->GetElementByName($name) ;
				if ($ret!=null)
					return $ret ;		
			}		
		}
		return null ;
	}
	

	function GetArrayOfObjects($composites=0)
	{
		$arr=array() ;
		
		if (!$composites || !$this->IsLeaf())
			$arr[]=$this ;
		
		if (!$this->IsReference())
		{
			$iterator=$this->GetChildrenIterator() ;
			for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
				$arr=array_merge($arr,$iterator->Current()->GetArrayOfObjects($composites)) ;
		}
		return $arr ;
	}

	function GetCompositesIterator() { return new GofArrayIterator($this->GetArrayOfObjects(1)) ; }
	function GetContentsIterator() { return new GofArrayIterator($this->GetArrayOfObjects()) ; }
}



abstract class SimpleContent extends Content 
{ 
	function AddChild(Content $Child) {} 
	function DelChild($name) {}
	function ReplaceChild_keepname($name,$newchild) {}
	function ReplaceChild_newname($name,$newname,$newchild) {}
	
	function IsLeaf() { return true ;}
	function IsDisplay() { return $this->Par()->DisplayChild()==$this->GetName() ; }
}

class StringContent extends SimpleContent
{
	private $size ;

	function GetSize() { return $this->size ; } 
	function SetSize($size) { $this->size=$size ; } 

	function Accept($visitor,$t_par=null) 
	{ 
		return $visitor->VisitString($this) ;
	}
}

abstract class CompositeContent extends Content
{
	private $display_child ;
	 
	function SetDisplayChild($display_child) { $this->display_child=$display_child ; }
	function AddChild(Content $Child,$after_child) 
	{  
		if (!$this->IsReference())
			$Child->SetPar($this) ;
		
		// find $after_child position in children array
		$pos=0 ;
		if (strlen($after_child))
		{
			$i=0 ;
			foreach($this->children as $key => $child)
			{
				$i++ ;
				if ($key==$after_child)
					$pos=$i ;
			}
		}			
		
		$ins=array($Child->GetName() => $Child) ;
		
		$this->children=array_slice($this->children,0,$pos,$true)+
		                            $ins+array_slice($this->children,$pos,count($this->children)-$pos,$true) ;
		//array_splice($this->children, $pos, 0, $ins);
		
		// print_r($this->children) ;
		// $this->children[$Child->GetName()]=$Child ; 
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
	
	function DisplayChild() { return $this->display_child ; }
	function DisplayChildObject() { return $this->children[$this->DisplayChild()] ; }
	
	function IsLeaf() { return false ;}
	
	function CanCascadeDelete() { return false ; }
}


// root of content structure for specific domain
class Domain extends CompositeContent
{
	function Accept($visitor,$t_par=null)
	{
		return $visitor->VisitDomain($this) ;
	}
}


abstract class TableContent extends CompositeContent
{
	function IsTableContent() { return true ; }
}

class MasterTable extends TableContent
{
	function Accept($visitor,$t_par=null)
	{
		return $visitor->VisitMasterTable($this) ;
	}
	function CanCascadeDelete() { return true ; }
}

class MultiDetailTable extends TableContent
{
	function DependsFromParent() { return true ; }
	function CanCascadeDelete() { return true ; }
	
	function Accept($visitor,$t_par)
	{
		return $visitor->VisitMultiDetailTable($this) ;
	}
}

class AttributeTable extends TableContent
{
	private $filtered_by_child , $filters_output , $current_value ;
	
	function FilteredByChild() { return $this->filtered_by_child ; }
	function CurrentValue() { return $this->current_value ; }
	
	function SetFilteredByChild($filtered_by_child) { $this->filtered_by_child=$filtered_by_child ; }
	function FiltersOutput() { return $this->filters_output ; }
	function SetFiltersOutput($filters_output) { $this->filters_output=$filters_output ; }
	function SetCurrentValue($current_value) { $this->current_value=$current_value ; }
	
	function Accept($visitor,$t_par=null)
	{
		return $visitor->VisitAttributeTable($this,$t_par) ;
	}
}


// contains single child which is reference to table
class TableReference extends CompositeContent
{
	function IsReference() { return true ; }
	
	// overloads regular UpperTableName(), returns parent's 
	function UpperTableName() { return $this->Par()->GetName() ; }
	
	// overloads regular LowerName(), returns childs's
	function LowerName() { return $this->DisplayChildObject()->GetName() ; }
	
	function Accept($visitor,$t_par)
	{
		return $visitor->VisitTableReference($this) ;
	}
}


// defines view to show content
abstract class ViewContent extends CompositeContent
{
	
}

class MasterTableViewContent extends ViewContent
{
	function MasterTableObject() { return $this->DisplayChildObject()->DisplayChildObject() ; }
	
	function Accept($visitor,$t_par=null)
	{
		return $visitor->VisitMasterTableViewContent($this) ;
	}
} 


// GoF "Visitor" classes to get specific information from content structure, or do specific thingth with it
abstract class ContentVisitor
{
	function VisitString($content) { return "" ; } 
	function VisitMasterTable($content) { return "" ; } 
	function VisitMultiDetailTable($content) { return "" ; }
	function VisitAttributeTable($content,$t_par) { return "" ; }
	function VisitDomain($content) { return "" ; }
	function VisitTableReference($content) { return "" ; }
	function VisitMasterTableViewContent($content) { return "" ; }
} 

abstract class ContentVisitorBeforeAfter extends ContentVisitor
{
	protected $after ;
	
	function __construct($after=0) { $this->after=$after ; }
	
	function VisitStringBefore($content) { return "" ; }
	function VisitMasterTableBefore($content) { return "" ; }
	function VisitMultiDetailTableBefore($content) { return "" ; }
	function VisitAttributeTableBefore($content,$t_par) { return "" ; }
	
	function VisitStringAfter($content) { return "" ; }
	function VisitMasterTableAfter($content) { return "" ; }
	function VisitMultiDetailTableAfter($content) { return "" ; }
	function VisitAttributeTableAfter($content,$t_par) { return "" ; }
	
	final function VisitString($content) 
	{ return !$this->after ? $this->VisitStringBefore($content) : $this->VisitStringAfter($content) ; }

	final function VisitMasterTable($content)
	{ return !$this->after ? $this->VisitMasterTableBefore($content) : $this->VisitMasterTableAfter($content) ; }

	final function VisitMultiDetailTable($content)
	{ return !$this->after ? $this->VisitMultiDetailTableBefore($content) : $this->VisitMultiDetailTableAfter($content) ; }

	final function VisitAttributeTable($content,$t_par)
	{ return !$this->after ? $this->VisitAttributeTableBefore($content,$t_par) : $this->VisitAttributeTableAfter($content,$t_par) ; }
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
	
	function VisitAttributeTable($content,$t_par=null)
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

	private $edit_obj ;
	
	function __construct($settings,$sqlconnect,$interface,$edit_obj,$after=0) 
	{ 
		$this->SqlConnectableSet($settings,$sqlconnect) ;
		$this->edit_obj=$edit_obj ;
		parent::__construct($interface,$after) ;
	}
	
	function VisitStringBefore($string)
	{
		$ret="" ;
		//$ret.=$this->form_interface->Paragraf() ;
		$name=$string->GetName() ;
		$ret.=$name.' '.$this->form_interface->TextInput($name,$string->GetSize(),$this->edit_obj->$name) ;
		//$ret.=$this->form_interface->Paragraf_end() ;
		return $ret ;
	}


	function VisitMultiDetailTableBefore($content)
	{
		$name=$content->GetName() ;
		return $this->form_interface->HiddenInput($name,$this->edit_obj->$name) ;
	}
	
	function VisitMasterTableBefore($content)
	{
		$name=$content->GetName() ;
		return $this->form_interface->HiddenInput($name,$this->edit_obj->$name) ;
	}
	

	function VisitAttributeTableBefore($content,$t_par=null) 
	{
		$ret=$this->form_interface->Fieldset() ;


		$name=$content->GetName() ;
		
		// fill list with attribute table rows
		$query="select Id,".$content->DisplayChild()." from ".$this->settings->Prefix().$name ;
			
		// is this form to edit existing record
		$edited=$this->edit_obj->$name ;
				
		// add child filter if needed
		$chldnm=$content->FilteredByChild() ;
		if (strlen($chldnm) && !$edited)
		{
			 $chld=$content->GetChild($chldnm) ;
			 $query.=" where ".$chldnm."=".$chld->CurrentValue() ;
			 // echo "<br/>".$chldnm."<br/>".$query ;
		}
			
			
		$It=$this->sqlconnect->QueryObjectIterator($query) ;

		$selected= $content->CurrentValue() ;
		if ($edited) $selected=$edited ;

		$ret.=$this->form_interface->ListInput($name,$It,"Id",$content->DisplayChild(),$selected,$content->DisplayChild()) ;
		
		if ($selected)
			$ret.=$this->form_interface->Button("mainform","d","CommandDeleteContent".$name,$selected) ;
		
		return $ret ;   
	}
		
	function VisitAttributeTableAfter($content,$t_par=null)
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

	function VisitMasterTableBefore($mt)
	{
		$ret="" ;
		$ret.=$this->form_interface->TableHeadCol() ;
		$ret.="Edit" ;
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

		// if this is a display field for parent table then add local edit button 
		if ($string->IsDisplay())
		{
			$name=$string->Par()->GetName() ;
			$ret.=$this->form_interface->Button("mainform","e","CommandEditContent".$name,$this->row->$name) ;

			if ($string->Par()->CanCascadeDelete())
				$ret.=$this->form_interface->Button("mainform","d","CommandDeleteContent".$name,$this->row->$name) ;
		}
		
		$ret.=$this->form_interface->TableCol_end() ;
		
		return $ret ;
	}

	function VisitMasterTableBefore($content)
	{
		$ret="" ;
		$ret.=$this->form_interface->TableCol() ;
		$name=$content->GetName() ;
		$ret.=$this->form_interface->Button("mainform","E","CommandEditContent",$this->row->$name) ;
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
	
	function AddIdToQuery($table)
	{
		$this->query->add_select($this->settings->Prefix().$table->GetName().'.Id as '.$table->GetName()) ;
	}
	
	function VisitMasterTable($mastertable)	
	{ 
		$this->query->add_from($this->settings->Prefix().$mastertable->GetName()) ;
		// $this->query->add_select($this->settings->Prefix().$mastertable->GetName().".Id") ; 
		$this->AddIdToQuery($mastertable) ;
		$this->query->add_order($mastertable->DisplayChild()) ;
	}
	
	function VisitMultiDetailTable($mdt)	
	{ 
		$this->query->add_join($this->settings->Prefix().$mdt->GetName(),
				               $this->settings->Prefix().$mdt->GetName().'.'.$mdt->Par()->GetName().'='.$this->settings->Prefix().$mdt->Par()->GetName().'.Id') ;
		$this->AddIdToQuery($mdt) ;
	}
	
	function VisitAttributeTable($at,$t_par=null)	
	{ 
		$tabname=$this->settings->Prefix().$at->GetName() ;
		$this->query->add_join($tabname,
				               $tabname.'.Id='.$this->settings->Prefix().$t_par->UpperTableName().'.'.$at->GetName()) ;
		
		$this->AddIdToQuery($at) ;
		
		// if this attribute table has current value, then adding filter 
		if ($at->FiltersOutput())
		{
			$value=$at->CurrentValue() ;
			if ($value)
				$this->query->add_where($this->settings->Prefix().$t_par->UpperTableName().'.'.$at->GetName().'='.$value) ;
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

	function VisitCompositeContent($content)
	{
		$this->query->add_values("DisplayChild",$content->DisplayChild()) ;
	}
	
	function VisitAttributeTable($content,$t_par=null) 
	{ 
		$this->VisitCompositeContent($content) ; 
		$this->query->add_values("FilteredByChild",$content->FilteredByChild()) ; 
		$this->query->add_values("FiltersOutput",$content->FiltersOutput()) ; 
	}

	function VisitMasterTable($content) { $this->VisitCompositeContent($content) ; }
	function VisitMultiDetailTable($content) { $this->VisitCompositeContent($content) ; }
	function VisitDomain($content) { $this->VisitCompositeContent($content) ; }
	function VisitTableReference($content) { $this->VisitCompositeContent($content) ; }
	function VisitMasterTableViewContent($content) { $this->VisitCompositeContent($content) ; }
}
	

// visitor to copy type-specific fields from untyped object to content element
class RestoreElementVisitor extends ContentVisitor
{
	private $object ;

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
	function VisitDomain($content) { $this->VisitCompositeContent($content) ; }
	function VisitMasterTableViewContent($content) { $this->VisitCompositeContent($content) ; }
	
	function VisitTableReference($content) 
	{ 
		$this->VisitCompositeContent($content) ; 
	}
	
	function VisitAttributeTable($content,$t_par=null)
	{
		$this->VisitCompositeContent($content) ;
		$content->SetFilteredByChild($this->object->FilteredByChild) ;
		$content->SetFiltersOutput($this->object->FiltersOutput) ;
		
		$content->SetCurrentValue($this->object->Value) ;
	}
}



// GoF "Builder" class to build various objects from content structure 
abstract class Builder
{
	function BuildStart() {}
	function BuildEnd() {}
	function BuildElementStart($element,$t_par) {}
	function BuildElementEnd($element,$t_par) {}
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
	function BuildElementStart($el,$t_par=null) { for ($this->it->First() ; !$this->it->IsDone() ; $this->it->Next()) $this->it->Current()->BuildElementStart($el,$t_par) ; }
	function BuildElementEnd($el,$t_par=-null) { for ($this->it->First() ; !$this->it->IsDone() ; $this->it->Next()) $this->it->Current()->BuildElementEnd($el,$t_par) ; }
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
	function BuildElementStart($element,$t_par=null) {	$this->result.=$element->Accept($this->before_visitor,$t_par) ; }
	function BuildElementEnd($element,$t_par=null) { $this->result.=$element->Accept($this->after_visitor,$t_par) ; }
}

// Builds html form to add new and query content 
class FormBuilder extends HtmlBuilder
{
	use SqlConnectable ;
	
	private $view_name ;
	
	function __construct($settings,$sqlconnect,$form_interface,$view_name,$edit_obj)
	{
		$this->before_visitor=new FormElementVisitor($settings,$sqlconnect,$form_interface,$edit_obj) ;
		$this->after_visitor=new FormElementVisitor($settings,$sqlconnect,$form_interface,$edit_obj,1) ;
		$this->SqlConnectableSet($settings, $sqlconnect) ;
		$this->view_name=$view_name ;
		parent::__construct($form_interface) ;
	}
	
	// implementing builder interface
	function BuildStart() { $this->result.=$this->output_interface->Header($this->settings->MainFormId()) ; }
	function BuildEnd() 
	{ 
		$this->result.=$this->output_interface->HiddenInput('view',$this->view_name) ;
		$this->result.=$this->output_interface->End("CommandInsertContent") ; 
	}
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
	function BuildElementStart($element,$t_par=null) {	$element->Accept($this->post_visitor,$t_par) ; }
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
	function GetSqlQuery() { return $this->query ; }
	
	// implementing builder interface
	function BuildElementStart($element,$t_par=null) {	$element->Accept($this->query_visitor,$t_par) ; }
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

	function BuildStart()
	{
		$this->query->Reset() ;
		$this->query->add_from($this->settings->ContentTable()) ;
		$this->sqlconnect->SimpleQuery($this->query->get_delete_query()) ;
	}
	
	function BuildElementStart($element,$t_par=null) 
	{
		$this->query->Reset() ;
		$this->query->add_insert($this->settings->ContentTable()) ; 
		
		// adding common fields to insert query
		$this->query->add_values("Name",$element->GetName()) ; 
		$this->query->add_values("ClassName",get_class($element)) ;
		if ($par=$element->Par()) 
	  		$this->query->add_values("ParentName",$par->GetName()) ;
		
		
		$element->Accept($this->save_visitor,$t_par) ; // adding class-specific fields to query 
		
		$this->query->add_values("Ord",++$this->order) ; // adding order
		
		// adding children count, if reference force to zero
		$chld_num=$element->IsReference() ? 0 : $element->GetChildrenIterator()->Num() ; 
		$this->query->add_values("Chldrn",$chld_num) ;
		
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
	function BuildElementStart($element,$t_par)
	{
		
		if (strlen($_POST[$element->DisplayChild()]) && $element->IsTableContent())
		{
			$query=new SqlQuery ;
			$query->add_insert($this->settings->Prefix().$element->GetName()) ;
			
			// iterate children, except those depending on this, to add to insert query
			$It=$element->GetChildrenIterator() ;
			for($It->First(); !$It->IsDone(); $It->Next())
			{
				$ch=$It->Current() ;
				if (!$ch->DependsFromParent())
					$query->add_values($ch->LowerName(), $this->sqlconnect->Esc($_POST[$ch->LowerName()])) ;
			}
			// adding parent table if this depends from parent
			if ($element->DependsFromParent())
				$query->add_values($t_par->GetName(), $this->sqlconnect->Esc($_POST[$t_par->GetName()])) ;
				
			// insert or upate data and then add Id to $_POST
			if ($_POST[$element->GetName()])
			{
				// Table Id already exists in POST, so it is update
				$query->add_where("Id=".$_POST[$element->GetName()]) ;

				// echo "<br/>".$query->get_update_query() ;
				
				$this->sqlconnect->SimpleQuery($query->get_update_query()) ;
			}
			else 
			{
				// Insert 
				
				// echo "<br/>".$query->get_insert_query() ;
				
				$this->sqlconnect->SimpleQuery($query->get_insert_query()) ;
				
				// adding Id of new inserted recors to POST
				$_POST[$element->GetName()]=$this->sqlconnect->InsertId() ;
			}
		}
	}
}


// Object to parse content structure
class ContentParser
{
	private $builders ;
	private $stop_reference ; // if set to non-zero, stops parsing on reference content 
	
	function __construct($builders, $stop=0) { $this->builders=$builders ; $this->stop_reference=$stop ; }
	
	// "normal" parse
	function ParseElement($content_element,$t_par=null)
	{
		$this->builders->BuildElementStart($content_element,$t_par) ;
		
		if (!$this->stop_reference || !$content_element->IsReference())
		{
			$iterator=$content_element->GetChildrenIterator() ;
			for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
				$this->ParseElement($iterator->Current(),$content_element) ;
		}
		
		$this->builders->BuildElementEnd($content_element,$t_par) ;
	}

	// main function of normal parse	
	function Parse($content)
	{
		$this->builders->BuildStart() ;
		$this->ParseElement($content) ;
		$this->builders->BuildEnd() ;
	}

	
	// parse by dependency relation
	function ParseCompositesByDependency($content_element,$t_par)
	{
		$iterator=$content_element->GetChildrenIterator() ;
		
		// first, parse elements not depending from this  
		for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
		{
			$el=$iterator->Current() ;
			if (!$el->DependsFromParent() && !$el->IsLeaf())
				$this->ParseCompositesByDependency($el,$content_element) ;
		}
		
		// handle this element
		$this->builders->BuildElementStart($content_element,$t_par) ;
		
		// now parse elements depending from this
		for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
		{
			$el=$iterator->Current() ;
			if ($el->DependsFromParent() && !$el->IsLeaf())
				$this->ParseCompositesByDependency($el,$content_element) ;
		}
	}
}


// adds children to refs
class ReferencesBuilder extends Builder
{
	private $root ;
	
	function __construct($root) { $this->root=$root ; }
	
	function BuildElementStart($element) 
	{	
		if ($element->IsReference())
		{
			$chld=$this->root->GetElementByName($element->DisplayChild()) ;
			$element->AddChild($chld) ; 
			//print_r($element) ;
		}
	}
}


class ContentRestorer
{
	use SqlConnectable ;
	
	function Restore()
	{
		// query content structure table
		$query=new SqlQuery() ;
		$query->add_select($this->settings->ContentTable().".*") ;
		$query->add_from($this->settings->ContentTable()) ;
		$query->add_order("ord desc") ;

		// add state table to query
		$query->add_select("Value") ;
		$query->add_join($this->settings->StateTable(),$this->settings->StateTable().".Name=".$this->settings->ContentTable().".Name") ;
		  
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
		
		// adding children to references
		$ref_builder=new ReferencesBuilder($current_obj) ;
		$ref_parser=new ContentParser($ref_builder) ;
		$ref_parser->Parse($current_obj) ;
		
		return $current_obj ;
	}
	
}



// content command with sql operations
abstract class ContentSQLCommand extends POSTCommand
{
	use SqlConnectable ;

	protected $content ;
	
	function __construct($settings,$connect,$content)
	{
		$this->content=$content ;
		parent::__construct() ;
		$this->SqlConnectableSet($settings, $connect) ;
	}
}

class ContentPageViewCommand extends ContentSQLCommand
{
	protected $master ;
	
	function SetMaster()
	{
		$view_object=$this->content->GetElementByName($this->post_obj->view) ;
		$this->master=$view_object->MasterTableObject() ;
	}
}

// command to insert form data to user tables 
class CommandInsertContent extends ContentPageViewCommand
{
	function Execute()
	{
		$this->SetMaster() ;
		
		$insert_builder=new InsertBuilder($this->settings,$this->sqlconnect) ;
		$insert_parser=new ContentParser($insert_builder) ;
		$insert_parser->ParseCompositesByDependency($this->master) ;
			
		// copy values from $_POST and save to state table
		$post_builder=new POSTBuilder($this->settings,$this->sqlconnect) ;
		$post_parser=new ContentParser($post_builder) ;
		$post_parser->Parse($this->master) ;
	}
}



// command to fill form fields with existing row values
// it queries data and store it in $obj 
class CommandEditContent extends ContentPageViewCommand
{
	private $obj ; 
	
	function Obj() { return $this->obj ; }
	function Execute()
	{
		if (strlen($this->suffix))	
		{
			// only one table updated, obtain table name from command suffix
			$query= new SqlQuery() ;
			$query->add_from($this->settings->Prefix().$this->suffix) ;
			$query->add_select('*') ;
		    $query->add_select('Id as '.$this->suffix) ;
			$query->add_where("Id=".$this->value) ;
			
			$this->obj=$this->sqlconnect->QueryObject($query->get_query()) ;
		}
		else 
		{
			// all tables updated, find master table object and build query
			
			$this->SetMaster() ;
			
			// Get sql query from content structure  
			$query_builder=new QueryBuilder($this->settings) ;
			$parser=new ContentParser($query_builder) ;
			
			// print_r($this->post_obj) ;
			
			$parser->Parse($this->master) ;
			$query=$query_builder->GetSqlQuery() ;

			// add Id of edited row and get result
			$query->add_where($this->settings->Prefix().$this->master->GetName().".Id=".$this->value) ;
			$this->obj=$this->sqlconnect->QueryObject($query->get_query()) ;
		}
	}
}

class CommandDeleteContent extends ContentPageViewCommand
{
	function Execute()
	{
		if (strlen($this->suffix))
		{
			// only from one table data can be deleted
			$query= new SqlQuery() ;
			$query->add_from($this->settings->Prefix().$this->suffix) ;
			$query->add_where("Id=".$this->value) ;
				
			// echo $query->get_delete_query() ;
				
			$this->sqlconnect->SimpleQuery($query->get_delete_query()) ;
		}
	}
}


// Generates web page with content structure as a model
// uses GoF "Template Method" pattern
class PageView
{
	use SqlConnectable ;
	
	protected $form_interface, $imp ;
	protected $content ;
	protected $dispatcher ;

	function InitSettings()
	{
		// create settings
	 	$this->settings=new Settings("sc_", "_content","_state","mainform",
		                              array("StringContent","AttributeTable","MasterTable","MultiDetailTable",
		                              		"TableReference","MasterTableViewContent"
		                              )) ;

		// create MySqli connection
		$this->sqlconnect=new MySqliConnector('dollsfun.mysql.ukraine.com.ua','dollsfun_content','93hfkudn', 'dollsfun_content') ;

		// create form interface and imlementation
		$this->imp=new HtmlFormImp() ;
		$this->form_interface=new FormInterface($this->imp) ;
	}


	// inits objects before to use in commands
	function InitObjects() {}
	
	function GetCommandsArray() { return array() ; }
	
	function GetPage() { return "" ; }
	
	function GetView()
	{
		$this->InitSettings() ;
		
		// create restorer object and use it to restore content structure from sql table
		$restorer=new ContentRestorer($this->settings,$this->sqlconnect) ;
		$this->content=$restorer->Restore() ;
		
		$this->InitObjects() ;
		
		$this->dispatcher=new Dispatcher($this->GetCommandsArray()) ;
		
		$post= ($_SERVER['REQUEST_METHOD']=='POST') ;
		if ($post)
			$this->dispatcher->ExecuteFromPOST() ;		
		
		$view='<p><a href="index.php">Content</a>  <a href="structure.php">Structure</a></p>' ;

		return $view.$this->GetPage() ;
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

?>