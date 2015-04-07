<?php 

// Content classes, based on GoF "Composite" design pattern   

abstract class Content
{
	abstract function DrawForm() ;
	abstract function AddChild(Content $Child) ; 
	abstract function DelChild($name) ;
	abstract function &GetChildren() ;
	abstract function ReplaceChild($name,$newchild) ;
	
	
	private $name ;
	
	function __construct($name) { $this->name=$name ; }
	function GetName() { return $this->name ; }
}

abstract class SimpleContent extends Content 
{ 
	function AddChild(Content $Child) {} 
	function DelChild($name) {}
	function &GetChildren() { return array(); }
	function ReplaceChild($name,$newchild) {}
	
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
	private $composer ;
	
	function __construct($name) { $this->children=array() ; parent::__construct($name) ; }
	function AddChild(Content $Child) { $this->children[$Child->GetName()]=$Child ; }
	function DelChild($name) { unset($this->children[$name]) ; }
	function &GetChildren() { return $this->children ; }
	function ReplaceChild($name,$newchild) 
	{ 
			$this->children[$name]=$newchild ;
	}
	
	function SetComposer($composer) { $this->composer=$composer ; }
	function ReCompose() { $this->composer->Compose($this) ; }
	
	function DrawForm()
	{
		$ret_str='' ;
		foreach ($this->GetChildren() as $child)
			$ret_str.=$child->DrawForm() ;
		return $ret_str ;
	}
}

class MasterTable extends CompositeContent
{
}


// GoF "Decorator" class family 
abstract class FormDecorator extends Content
{
	private $Child, $childkey ;
	function __construct($name,$Child,$childkey) 
	{ 
		$this->Child=array() ; 
		$this->childkey= $childkey ; 
		$this->Child[$childkey]=$Child ; 
		parent::__construct($name) ; 
	}
	
	function &GetChildren() { return $this->Child ; }
	function GetChild() { return $this->Child[$this->childkey] ; }

	function AddChild(Content $Child) {} 
	function DelChild($name) {}
	function ReplaceChild($name,$newchild) { $this->Child[$this->childkey]=$newchild ; }
	
}

class FontDecorator extends FormDecorator
{
	function DrawForm()
	{
		return "<font color=".$this->GetName().">".$this->GetChild()->DrawForm()."</font>" ;
	}
}

class FieldSetDecorator extends FormDecorator
{
	function DrawForm()
	{
		return "<fieldset>".$this->GetChild()->DrawForm()."</fieldset>" ;
	}
}

class ParagrafDecorator extends FormDecorator
{
	function DrawForm()
	{
		return "<p>".$this->GetChild()->DrawForm()."</p>" ;
	}
}



// GoF "Strategy" Composer of form layout
abstract class FormComposer 
{
	abstract function Compose($form) ;
} 

class HtmlFormComposer extends FormComposer
{
	function Compose($form)
	{
		foreach ($form->GetChildren() as $key => $child)
		{
			$pdecor=new ParagrafDecorator("",$child,$key) ;
			if ($child->GetName()=="Part")
				$form->ReplaceChild($key,new FontDecorator("red",$pdecor,$key)) ;
			else 
				$form->ReplaceChild($key,$pdecor) ;
				
				
		}
	}	
	
}


$form=new MasterTable('Words') ;
$form->AddChild(new StringContent('Word')) ;
$form->AddChild(new StringContent('Part')) ;

print_r($form) ;

$composer=new HtmlFormComposer ;
$form->SetComposer($composer) ;
$form->ReCompose() ;

print_r($form) ;

echo $form->DrawForm() ;
?>