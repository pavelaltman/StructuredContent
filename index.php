<?php
require_once 'content.php';

// visitor to display view, defined by ViewContent element of Content structure
class GetViewVisitor extends ContentVisitor
{
	use SqlConnectable ;
	
	protected $form_interface,
			  $dispatcher ;
	
	function __construct($settings,$sqlconnect,$interface,$dispatcher)
	{
		$this->form_interface=$interface ;
		$this->dispatcher=$dispatcher ;
		$this->SqlConnectableSet($settings, $sqlconnect) ;
	}

	function VisitMasterTableViewContent($viewcontent)
	{
		$master=$viewcontent->MasterTableObject() ;

		// create form builder
		$form_builder=new FormBuilder($this->settings,$this->sqlconnect,$this->form_interface,$viewcontent->GetName(),
				$this->dispatcher->GetCommand("CommandEditContent")->Obj()) ;
		
		// create query builder
		$query_builder=new QueryBuilder($this->settings) ;
		
		// create table head builder
		$tablehead_builder=new TableHeadBuilder($this->form_interface) ;
		$builders_array=array($form_builder,$query_builder,$tablehead_builder) ;
		
		// create all_builders object, containing all needed builders
		$all_builders=new Builders($builders_array) ;
		
		// parse composite structure with all builders
		$parser=new ContentParser($all_builders) ;
		$parser->Parse($master) ;
		
		$view=$form_builder->Get() ; // form
		$view.=$this->form_interface->Table() ;
		$view.=$tablehead_builder->Get() ; // table head
		
		// builder and parser to build rows
		$row_builder=new TableRowBuilder($this->form_interface) ;
		$row_parser=new ContentParser($row_builder) ;
		
		// echo "<br/>".$query_builder->GetQuery() ;
		
		$outrows=$this->sqlconnect->QueryObjectIterator($query_builder->GetQuery()) ;
		for ($outrows->First() ; !$outrows->IsDone() ; $outrows->Next())
		{
			$row=$outrows->Current() ;
			$row_builder->SetRow($row) ;
			$row_parser->Parse($master) ;
			$view.=$row_builder->Get() ;
		}
		
		$view.=$this->form_interface->Table_end() ;
		return $view ;
	}
}


class ContentPageView extends PageView
{
	private $view_visitor ;
	
	function GetCommandsArray()
	{
		return array("CommandEditContent" => new CommandEditContent($this->settings, $this->sqlconnect, $this->content),
		             "CommandInsertContent" => new CommandInsertContent($this->settings, $this->sqlconnect, $this->content),
		             "CommandDeleteContent" => new CommandDeleteContent($this->settings, $this->sqlconnect, $this->content)) ;
		
	}

	function FindInGetPost($strname)
	{
	    if (array_key_exists($strname,$_GET))
	       return $_GET[$strname] ;
	    else if (array_key_exists($strname,$_POST))
	       return $_POST[$strname] ;
	    else return "" ;
	                
	}
	
	
	function GetPage()
	{
	    $domain_name=$this->FindInGetPost('domain');
	    if (strlen($domain_name))
	        $domain_object=$this->content->GetElementByName($domain_name) ;
	    else 
	    {
	        $domain_object=$this->content->DisplayChildObject() ;
	        $domain_name=$domain_object->GetName() ;
	    }
	    
	    $view_name=$this->FindInGetPost('view');
	    if (strlen($view_name))
	    {
	        $view_object=$this->content->GetElementByName($view_name) ;
	    }
	    else
	    {
	        $view_name=$domain_object->DisplayChild() ;
	        $view_object=$this->content->GetElementByName($view_name) ;
	    }
	     
		
		

		// view string
		$view='<!DOCTYPE html> <p> <a href="index.php"><b>Content</b></a> <a href="structure.php">Structure</a></p>'."\n<p>" ;
		
		// Adding domains to header, current domain is bold
		$iterator=$this->content->GetChildrenIterator() ;
		for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
		{
		    $dname=$iterator->Current()->GetName() ;
		    if ($dname==$domain_name)
		        $view=$view.'<a href="index.php? domain='.$dname.'"><b>'.$dname.'</b></a>'."  ";
		    else
		        $view=$view.'<a href="index.php? domain='.$dname.'">'.$dname.'</a>'."  ";
		}
		$view=$view."</p>\n<p>";
		
		// Adding views to header, current view is bold
		$iterator=$domain_object->GetChildrenIterator() ;
		for ($iterator->First() ; !$iterator->IsDone() ; $iterator->Next())
		{
		    if (get_class($iterator->Current())=="MasterTableViewContent")
		    {
		      $vname=$iterator->Current()->GetName() ;
		    
		      if ($vname==$view_object->GetName())
		        $view=$view.'<a href="index.php? domain='.$domain_name.'& view='.$vname.'"><b>'.$vname.'</b></a>'."  ";
		      else
		        $view=$view.'<a href="index.php? domain='.$domain_name.'& view='.$vname.'">'.$vname.'</a>'."  ";
		
		    }
		}
		$view=$view."</p>\n";
		
		
		$view_visitor=new GetViewVisitor($this->settings, $this->sqlconnect,$this->form_interface, $this->dispatcher) ;
		
		return $view.$view_object->Accept($view_visitor) ;
	}
}

$view=new ContentPageView() ;
echo $view->GetView() ;
?>