<?php
require_once 'content.php';

// 19.03.2016 reinstall eclipse on new notebook

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

	function GetPage()
	{
		// search for view name in GET or POST, else use "DisplayChild" from root entry 
		if (array_key_exists('view',$_GET))
			$view_name=$_GET['view'] ; 
		else if (array_key_exists('view',$_POST))
			$view_name=$_POST['view'] ; 
		else
			$view_name=$this->content->DisplayChild() ;

		
		$view_object=$this->content->GetElementByName($view_name) ;

		
		$view_visitor=new GetViewVisitor($this->settings, $this->sqlconnect,$this->form_interface, $this->dispatcher) ;
		return $view_object->Accept($view_visitor) ;
	}
}

$view=new ContentPageView() ;
echo $view->GetView() ;
?>