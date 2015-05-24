<?php
require_once 'content.php';

// builds html table with content structure
class StructureViewBuilder extends Builder 
{
	use SqlConnectable ;
	
	private $result ; // table with form elements to return 
	private $level ;  // current level in content composite
	private $root_content ;  // root of content stucture
	private $ref_flag ;
	
	protected $output_interface ;
	
	private $add_child_name,		// content name after which to draw form to add new content 
			$add_sibling_name,		// -----
			$edit_name,				// -----
			$change_parent_name ;	// -----
	
	function SetAddChild($name) { $this->add_child_name=$name ; }
	function SetAddSibling($name) { $this->add_sibling_name=$name ; }
	function SetEdit($name) { $this->edit_name=$name ; }
	function SetChangeParent($name) { $this->change_parent_name=$name ; }
	
	function __construct($output_interface,$settings,$connect,$root)
	{
		$this->output_interface=$output_interface ;
		$this->result="" ;
		$this->root_content=$root ;
		$this->composites_iterator=$this->root_content->GetCompositesIterator() ;
		$this->contents_iterator=$this->root_content->GetContentsIterator() ;
		$ref_flag=0 ;
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
		
		// DisplayChild
		$this->result.=$this->output_interface->TableCol() ;
		$this->result.=$this->output_interface->TextInput("DisplayChild",20) ;
		$this->result.=$this->output_interface->TableCol_end() ;
		
		// hidden inputs, parent name and name of child to insert after 
		$this->result.=$this->output_interface->HiddenInput("Parent",$parent_name) ;
		$this->result.=$this->output_interface->HiddenInput("AfterChild",$after_child_name) ;
		
		// button to insert
		$this->result.=$this->output_interface->TableCol() ;
		$this->result.=$this->output_interface->Button($this->settings->MainFormId(),"Insert",
					                                   "CommandInsertElement","") ;
		$this->result.=$this->output_interface->TableCol_end() ;

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
		
		
		if (!$this->ref_flag) // draw buttons only if not inside reference
		{
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
			
			// "change parent" or "new parent" button
			$this->result.=$this->output_interface->TableCol() ;
			if ($element->Par())
				if ($this->change_parent_name==$element->GetName()) // change parent command allready was 
				{
					$this->result.=$this->output_interface->Button($this->settings->MainFormId(),"new parent",
															       "CommandNewParent".$element->GetName(),$element->GetName()) ;
					$this->result.=$this->output_interface->ListInput("NewParent",$this->composites_iterator,
					                                                  "name","name","0","NewParent") ;
					$this->result.=$this->output_interface->ListInput("AfterChild",$this->contents_iterator,
					                                                  "name","name","0","AfterChild") ;
					$this->result.="<br/>Reference ".$this->output_interface->TextInput("Reference",20) ;
				}	
				else 
				{
					$this->result.=$this->output_interface->Button($this->settings->MainFormId(),"change parent",
															   "CommandChangeParent".$element->GetName(),$element->GetName()) ;
				}
	
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
		}
			
		$this->level++ ; 
		if ($element->IsReference())
			$this->ref_flag=1 ;
			
		$this->result.=$this->output_interface->TableRow_end() ;

		// builds form row if CommandAddChild was invoked
		if ($this->add_child_name==$element->GetName())
			$this->BuildFormRow($element->GetName()) ;
	}
	
	function BuildElementEnd($element) 
	{ 
		$this->level-- ; 
		if ($element->IsReference())
			$this->ref_flag=0 ;
		
		// builds form row if CommandAddSibling was invoked
		if ($this->add_sibling_name==$element->GetName())
		{
			$this->BuildFormRow($element->Par()->GetName(),$element->GetName()) ;
		}
		
	}

	function BuildEnd($content)
	{
		$this->result.=$this->output_interface->Table_end() ;
		$this->result.=$this->output_interface->End("") ;
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

class CommandChangeParent extends EditContentStructureCommand
{
	function Execute() { $this->struct_view_builder->SetChangeParent($this->suffix) ; }
}


// visitor to modify content tables when add
class StructureAddVisitor extends ContentVisitor
{
	private $settings ;
	private $table_create ;
	
	function __construct($settings,$table_create=0) { $this->settings=$settings ; $this->table_create=$table_create ; }
	
	function VisitString($string)
	{
		return "alter table ".$this->settings->Prefix().$string->Par()->GetName().
		       " add ".$string->GetName()." char(".$string->GetSize().")" ;
	}

	function VisitMasterTable($string)
	{
		return "alter table ".$this->settings->Prefix().$string->Par()->GetName().
		       " add ".$string->GetName()." char(".$string->GetSize().")" ;
	}
}




class ChangeContentStructureCommand extends ContentSQLCommand
{
	// perform content-specific operations after adding $new_object as child child using StructureAddVisitor
	// if $table_create then not only add field to parent table, but create new table for TableContent  
	function Add($new_object,$table_create=0)
	{
		$struct_add_visitor=new StructureAddVisitor($this->settings) ;
		$query=$new_object->Accept($struct_add_visitor) ;
		if (strlen($query))
			$this->sqlconnect->SimpleQuery($query) ;
	}
	
	
	// perform content-specific operations after deleting $element from structure 
	// if $table_drop then not only drop field from parent table, but drop entire table for TableContent  
	function Del($element,$table_drop=0)
	{
		if ($element->Par()->IsTableContent()) // if parent is table, then drop field from it
		{
			$query="alter table ".$this->settings->Prefix().$element->Par()->GetName()." drop ".$element->GetName() ;
			$this->sqlconnect->SimpleQuery($query) ;
		}
	}
	
	// save whole structure
	function Save()
	{
		$save_builder=new SaveBuilder($this->settings, $this->sqlconnect) ;
		$save_parser=new ContentParser($save_builder,1) ;
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
			
			$this->Del($element,1) ;			
			$this->Save() ;
		}
	}
}

class CommandInsertElement extends ChangeContentStructureCommand
{
 	function Execute()
 	{
		//add new child in proper place
 		$element=$this->content->GetElementByName($this->post_obj->Parent) ;
 		
 		// print_r($this->post_obj) ;
 		
 		if ($element)
 		{
 	 		$classname=$this->post_obj->ClassName ;
 	 		$new_object=new $classname($this->post_obj->Name,array()) ;
 	 
 	 		// using visitor object to restore specific fields from POST object
 	 		$restore_visitor=new RestoreElementVisitor ;
 	 		$restore_visitor->SetObject($this->post_obj) ;
 	 		$new_object->Accept($restore_visitor) ;
 	 
 	 		
 	 		$element->AddChild($new_object,$this->post_obj->AfterChild) ;

			$this->Add($new_object,1) ;
			$this->Save() ;
 		}
	}
}


class CommandNewParent extends ChangeContentStructureCommand
{
	function Execute()
	{
		$element=$this->content->GetElementByName($this->suffix) ;
		if ($element)
		{
			$new_parent=$this->content->GetElementByName($this->post_obj->NewParent) ;
			$old_parent=$element->Par() ;
			
			$new_parent->AddChild($element,$this->post_obj->AfterChild) ;
			$this->Add($element,0) ; // need not create table
				
			if (strlen($this->post_obj->Reference) && $element->IsTableContent())
			{
				// create reference in place of moved table
				// Id's in field remain, only rename field
				
				$new_object=new TableReference($this->post_obj->Reference,array()) ;
				$new_object->SetDisplayChild($element->GetName()) ;
				$old_parent->ReplaceChild_keepname($element->GetName(),$new_object) ;
				
				$query='alter table '.$this->settings->Prefix().$old_parent->GetName().
				       ' change '.$element->GetName().' '.$this->post_obj->Reference ;
				
				echo $query ;
				// $this->sqlconnect->SimpleQuery($query) ;
			}
			else 
			{
				// simply del from old parent, table not drop but empty
				
				$old_parent->DelChild($element->GetName()) ;
				$this->Del($element,0) ;			
			}
			
			
			$this->Save() ;
		}
	}
}

 
 
class StructurePageView extends PageView
{
	private $struct_view_builder ;
	
	function InitObjects()
	{
		$this->struct_view_builder=new StructureViewBuilder($this->form_interface,$this->settings,
				                                            $this->sqlconnect,$this->content) ;
	}
	
	function GetCommandsArray()
	{
		return array("CommandAddChild" => new CommandAddChild($this->struct_view_builder),
		             "CommandAddSibling" => new CommandAddSibling($this->struct_view_builder),
		             "CommandEditElement" => new CommandEditElement($this->struct_view_builder),
		             "CommandChangeParent" => new CommandChangeParent($this->struct_view_builder),
					 "CommandInsertElement" => new CommandInsertElement($this->settings, $this->sqlconnect, $this->content),
				     "CommandNewParent" => new CommandNewParent($this->settings, $this->sqlconnect, $this->content),
					 "CommandDeleteElement" => new CommandDeleteElement($this->settings, $this->sqlconnect, $this->content)) ;
		
	}
	
	function GetPage()
	{
		$parser=new ContentParser($this->struct_view_builder,1) ;
		
		$parser->Parse($this->content) ;
		return $this->struct_view_builder->Get() ;
	}
}

$view=new StructurePageView() ;
echo $view->GetView() ;
?>