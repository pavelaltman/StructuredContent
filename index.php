<?php
require_once 'content.php';

class ContentView extends PageView
{
	function GetCommandsArray()
	{
		return array("CommandEditContent" => new CommandEditContent($this->settings, $this->sqlconnect, $this->content),
		             "CommandInsertContent" => new CommandInsertContent($this->settings, $this->sqlconnect, $this->content),
		             "CommandDeleteContent" => new CommandDeleteContent($this->settings, $this->sqlconnect, $this->content)) ;
		
	}

	function GetPage()
	{
		// create form builder
		$form_builder=new FormBuilder($this->settings,$this->sqlconnect,$this->form_interface,
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
		$parser->Parse($this->content) ;
		
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
			$row_parser->Parse($this->content) ;
			$view.=$row_builder->Get() ;
		}

		$view.=$this->form_interface->Table_end() ;
		
		return $view ;
	}
}

$view=new ContentView() ;
echo $view->GetView() ;
?>