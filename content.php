<?php 

// "Content" classes, based on GoF Composite design pattern   

abstract class Content
{
	abstract function DrawForm() ;
	abstract function AddChild(Content $Child) ; 
	abstract function DelChild($name) ;
	abstract function GetChildren() ;
	
	private $name ;
	
	function __construct($name) { $this->name=$name ; }
	function GetName() { return $this->name ; }
}

abstract class SimpleContent extends Content 
{ 
	function AddChild(Content $Child) {} 
	function DelChild($name) {}
	function GetChildren() { return array(); }
}

class StringContent extends SimpleContent
{
	function DrawForm() 
	{ 
		return $this->GetName()." <input type=\"text\" name=\"".$this->GetName()."\"/> " ;
	}
}

abstract class CompositeContent extends Content
{
	private $children ;
	
	function __construct($name) { $this->children=array() ; parent::__construct($name) ; }
	function AddChild(Content $Child) { $this->children[$Child->GetName()]=$Child ; }
	function DelChild($name) { unset($this->children[$name]) ; }
	function GetChildren() { return $this->children ; }
	
	function DrawForm_beforeChildren() { return '' ; }
	function DrawForm_beforeChild() { return '' ; }
	function DrawForm_afterChild() { return '' ; }
	function DrawForm_afterChildren() { return '' ; }

	function DrawForm()
	{
		$ret_str=$this->DrawForm_beforeChildren() ;
		
		foreach ($this->GetChildren() as $child)
		{
			$ret_str.=$this->DrawForm_beforeChild() ;
			$ret_str.=$child->DrawForm() ;
			$ret_str.=$this->DrawForm_afterChild() ;
		}
		$ret_str.=$this->DrawForm_afterChildren() ;
		return $ret_str ;
	}
}

class MasterTable extends CompositeContent
{
}


abstract class FormDecorator extends Content
{
	private $Child ;
	function __construct($name,$Child) { $this->Child=array() ; $this->Child[0]=$Child ; parent::__construct($name) ; }
	
	function GetChildren() { return $this->Child ; }
	function GetChild() { return $this->Child[0] ; }

	function AddChild(Content $Child) {} 
	function DelChild($name) {}
}

class FontDecorator extends FormDecorator
{
	function DrawForm()
	{
		return "<font color=".$this->GetName().">".$this->GetChild()->DrawForm()."</font>" ;
	}
}


$form=new MasterTable('Words') ;
$form->AddChild(new StringContent('Word')) ;
$form->AddChild(new FontDecorator("red",new StringContent('Part'))) ;

//print_r($form) ;
echo $form->DrawForm() ;
?>