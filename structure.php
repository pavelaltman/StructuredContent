<?php
require_once 'content.php';

class StructureViewBuilder extends Builder 
{
	private $result ;
	private $level ;
	
	protected $output_interface ;

	function __construct($output_interface)
	{
		$this->output_interface=$output_interface ;
		$this->result="" ;
	}
	
	function Get() { return $this->result ; }
	
	function BuildElementStart($element)
	{
		for($i=0 ; $i< $this->level ; $i++)
			$this->result.="....." ;
		$this->result.="!__" ;
		$this->result.=$element->GetName() ;
		$this->result.=$this->output_interface->NewLine() ;
		$this->level++ ;
	}
	
	function BuildElementEnd($element) { $this->level-- ; }
}

// create settings
$settings=new Settings("sc_", "_content","_state","mainform") ;

// create MySqli connection
$db=new MySqliConnector('dollsfun.mysql.ukraine.com.ua','dollsfun_content','93hfkudn', 'dollsfun_content') ;

// create form interface and imlementation
$imp=new HtmlFormImp() ;
$form_interface=new FormInterface($imp) ;

echo '<p><a href="index.php">Content</a></p>' ;

$restorer=new ContentRestorer($settings,$db) ;
$content=$restorer->Restore() ;

$struct_view_builder=new StructureViewBuilder($form_interface) ;
$parser=new ContentParser($struct_view_builder) ;
$parser->Parse($content) ;

echo $struct_view_builder->Get() ;
?>