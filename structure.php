<?php
require_once 'content.php';

// builds html table with content structure
class StructureViewBuilder extends Builder 
{
	use SqlConnectable ;
	
	private $result ; // table with form elements to return 
	private $level ;  // current level in content composite
	
	protected $output_interface ;
	
	private $add_child_name, $add_sibling_name, $edit_name ;
	
	function SetAddChild($name) { $this->add_child_name=$name ; }
	function SetAddSibling($name) { $this->add_sibling_name=$name ; }
	function SetEdit($name) { $this->edit_name=$name ; }
	
	function __construct($output_interface,$settings,$connect)
	{
		$this->output_interface=$output_interface ;
		$this->result="" ;
		$this->SqlConnectableSet($settings, $connect) ;
	}
	
	function Get() { return $this->result ; }
	
	function BuildStart($content)
	{
		$this->result.=$this->output_interface->Header($this->settings->MainFormId()) ;
		$this->result.=$this->output_interface->Table() ;
		$this->result.=$this->output_interface->TableRow() ;
		$this->result.=$this->output_interface->TableHeadCol()."Name" ; 
		$this->result.=$this->output_interface->TableHeadCol_end() ; 
		$this->result.=$this->output_interface->TableHeadCol()."ClassName" ; 
		$this->result.=$this->output_interface->TableHeadCol_end() ; 
		$this->result.=$this->output_interface->TableHeadCol()."Size" ; 
		$this->result.=$this->output_interface->TableHeadCol_end() ; 
		$this->result.=$this->output_interface->TableHeadCol()."DisplayChild" ; 
		$this->result.=$this->output_interface->TableHeadCol_end() ; 
		$this->result.=$this->output_interface->TableHeadCol()."FilteredByChild" ; 
		$this->result.=$this->output_interface->TableHeadCol_end() ; 
		$this->result.=$this->output_interface->TableHeadCol()."FiltersOutput" ; 
		$this->result.=$this->output_interface->TableHeadCol_end() ; 
		$this->result.=$this->output_interface->TableRow_end() ;
		
	}
	
	
	function TreeIndent()
	{
		for($i=0 ; $i< $this->level ; $i++)
			$this->result.="....." ;
		$this->result.="!__" ;
	}
	
	function BuildFormRow($parent_name,$after_child_name="")
	{
		$this->result.=$this->output_interface->TableRow() ;

		// Name
		$this->result.=$this->output_interface->TableCol() ;
		$this->TreeIndent() ;
		$this->result.=$this->output_interface->TextInput("Name",10) ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		// Type
		$this->result.=$this->output_interface->TableCol() ;
		$this->result.=$this->output_interface->ListInput("ClassName",$this->settings->GetTypesIterator(),
				                                          "ClassName","ClassName","0","ClassName") ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		// Size
		$this->result.=$this->output_interface->TableCol() ;
		$this->result.=$this->output_interface->TextInput("Size",3) ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		// hidden inputs, parent name and name of child to insert after 
		$this->result.=$this->output_interface->HiddenInput("Parent",$parent_name) ;
		$this->result.=$this->output_interface->HiddenInput("AfterChild",$after_child_name) ;
		
		$this->result.=$this->output_interface->TableRow_end() ;
	}
	
	function BuildElementStart($element)
	{
		$this->result.=$this->output_interface->TableRow() ;

		$this->result.=$this->output_interface->TableCol() ;

		$this->TreeIndent() ;
		
		$this->result.=$element->GetName() ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		$this->result.=$this->output_interface->TableCol() ;
		$this->result.=get_class($element) ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		$this->result.=$this->output_interface->TableCol() ;
		$this->result.=$element->GetSize() ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		$this->result.=$this->output_interface->TableCol() ;
		$this->result.=$element->DisplayChild() ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		$this->result.=$this->output_interface->TableCol() ;
		$this->result.=$element->FilteredByChild() ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		$this->result.=$this->output_interface->TableCol() ;
		$this->result.=$element->FiltersOutput() ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		
		// "add child" button
		$this->result.=$this->output_interface->TableCol() ;
		if (!$element->IsLeaf()) 
			$this->result.=$this->output_interface->Button($this->settings->MainFormId(),"add child",
					                                       "CommandAddChild".$element->GetName(),$element->GetName()) ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		// "add sibling" button
		$this->result.=$this->output_interface->TableCol() ;
		if ($element->Par())
			$this->result.=$this->output_interface->Button($this->settings->MainFormId(),"add sibling",
					                                       "CommandAddSibling".$element->GetName(),$element->GetName()) ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		// "edit" button
		$this->result.=$this->output_interface->TableCol() ;
		$this->result.=$this->output_interface->Button($this->settings->MainFormId(),"edit",
			 	                                       "CommandEditElement".$element->GetName(),$element->GetName()) ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		// "delete" button
		$this->result.=$this->output_interface->TableCol() ;
		if ($element->Par())
			$this->result.=$this->output_interface->Button($this->settings->MainFormId(),"delete",
					                                       "CommandDeleteElement".$element->GetName(),$element->GetName()) ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		$this->level++ ;
		$this->result.=$this->output_interface->TableRow_end() ;
		
		// builds form row if CommandAddChild was invoked
		if ($this->add_child_name==$element->GetName())
			$this->BuildFormRow($element->GetName()) ;
	}
	
	function BuildElementEnd($element) 
	{ 
		$this->level-- ; 

		// builds form row if CommandAddSibling was invoked
		if ($this->add_sibling_name==$element->GetName())
			$this->BuildFormRow($element->Par()->GetName(),$element->GetName()) ;
		
	}

	function BuildEnd($content)
	{
		$this->result.=$this->output_interface->Table_end() ;
		$this->result.=$this->output_interface->End("CommandInsertElement") ;
	}
}


class EditContentStructureCommand extends POSTCommand
{
	protected $struct_view_builder ;
	
	function __construct($builder)
	{
		$this->struct_view_builder=$builder ;
	}
}

class CommandAddChild extends EditContentStructureCommand
{
	function Execute() { $this->struct_view_builder->SetAddChild($this->suffix) ;}
}

class CommandAddSibling extends EditContentStructureCommand
{
	function Execute() { $this->struct_view_builder->SetAddSibling($this->suffix) ; }
}

class CommandEditElement extends EditContentStructureCommand
{
	function Execute() { $this->struct_view_builder->SetEdit($this->suffix) ; }
}


// visitor to modify content tables when add
class StructureAddVisitor extends ContentVisitor
{
	private $settings ;
	
	function __construct($settings) { $this->settings=$settings ; }
	
	function VisitString($string)
	{
		return "alter table ".$this->settings->Prefix().$string->Par()->GetName().
		       " add ".$string->GetName()." char(".$string->GetSize().")" ;
	}
}




class ChangeContentStructureCommand extends ContentSQLCommand
{
	function Save()
	{
		$save_builder=new SaveBuilder($this->settings, $this->sqlconnect) ;
		$save_parser=new ContentParser($save_builder) ;
		$save_parser->Parse($this->content) ;
	}
}

class CommandDeleteElement extends ChangeContentStructureCommand
{
	function Execute()
	{
		$element=$this->content->GetElementByName($this->suffix) ;
		if ($element)
		{
			$element->Par()->DelChild($this->suffix) ;	
			
			// preform query to drop or alter table
			if ($element->IsLeaf())
			{
				$query="alter table ".$this->settings->Prefix().$element->Par()->GetName()." drop ".$element->GetName() ;
				$this->sqlconnect->SimpleQuery($query) ;
			}	
			
			$this->Save() ;
		}
	}
}

class CommandInsertElement extends ChangeContentStructureCommand
{
 	function Execute()
 	{
		$post_obj=(object)$_POST ;

		//add new child in proper place
 		$element=$this->content->GetElementByName($post_obj->Parent) ;
 		if ($element)
 		{
 	 		$classname=$post_obj->ClassName ;
 	 		$new_object=new $classname($post_obj->Name,array()) ;
 	 
 	 		// using visitor object to restore specific fields from POST object
 	 		$restore_visitor=new RestoreElementVisitor ;
 	 		$restore_visitor->SetObject($post_obj) ;
 	 		$new_object->Accept($restore_visitor) ;
 	 
 	 		
 	 		$element->AddChild($new_object,$post_obj->AfterChild) ;

 	 		// perform query to create or alter table
 	 		$struct_add_visitor=new StructureAddVisitor($this->settings) ;
 	 		$query=$new_object->Accept($struct_add_visitor) ;
 	 		$this->sqlconnect->SimpleQuery($query) ;
 	 		
 	 		// save whole structure
			$this->Save() ;
 		}
 	}
 }


class StructureView extends PageView
{
	private $struct_view_builder ;
	
	function InitObjects()
	{
		$this->struct_view_builder=new StructureViewBuilder($this->form_interface,$this->settings,$this->sqlconnect) ;
	}
	
	function GetCommandsArray()
	{
		return array("CommandAddChild" => new CommandAddChild($this->struct_view_builder),
		             "CommandAddSibling" => new CommandAddSibling($this->struct_view_builder),
		             "CommandEditElement" => new CommandEditElement($this->struct_view_builder),
				     "CommandInsertElement" => new CommandInsertElement($this->settings, $this->sqlconnect, $this->content),
					 "CommandDeleteElement" => new CommandDeleteElement($this->settings, $this->sqlconnect, $this->content)) ;
		
	}
	
	function GetPage()
	{
		$parser=new ContentParser($this->struct_view_builder) ;
		$parser->Parse($this->content) ;
		return $this->struct_view_builder->Get() ;
	}
}

$view=new StructureView() ;
echo $view->GetView() ;
?>