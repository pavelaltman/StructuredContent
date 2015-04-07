<?php 

// Content classes, based on GoF "Composite" design pattern   

abstract class Content
{
	abstract function DrawFormElement() ;
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
	private $size ;

	function GetSize() { return $this->size ; } 
	function __construct($name,$size) { $this->size=$size ; parent::__construct($name) ; }
	function DrawFormElement() 
	{ 
		return $this->GetName()." <input type=\"text\" name=\"".$this->GetName()."\" size=\"".$this->GetSize()."\"> " ;
	}
}

abstract class CompositeContent extends Content
{
	private $children ;
	private $composer ;
	
	function __construct($name,$children) { $this->children=$children ; parent::__construct($name) ; }
	function AddChild(Content $Child) { $this->children[$Child->GetName()]=$Child ; }
	function DelChild($name) { unset($this->children[$name]) ; }
	function &GetChildren() { return $this->children ; }
	function ReplaceChild($name,$newchild) 
	{ 
			$this->children[$name]=$newchild ;
	}
	
	function SetComposer($composer) { $this->composer=$composer ; }
	function ReCompose() { $this->composer->Compose($this) ; }
	
	function DrawFormElement()
	{
		$ret_str='' ;
		foreach ($this->GetChildren() as $child)
			$ret_str.=$child->DrawFormElement() ;
		return $ret_str ;
	}
	
	
}

class MasterTable extends CompositeContent
{
}

class MultiDetailTable extends CompositeContent
{
}

class AttributeTable extends CompositeContent
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
	function DrawFormElement()
	{
		return "<font color=".$this->GetName().">".$this->GetChild()->DrawFormElement()."</font>" ;
	}
}

class FieldSetDecorator extends FormDecorator
{
	function DrawFormElement()
	{
		return "<fieldset>".$this->GetChild()->DrawFormElement()."</fieldset>" ;
	}
}

class ParagrafDecorator extends FormDecorator
{
	function DrawFormElement()
	{
		return "<p>".$this->GetChild()->DrawFormElement()."</p>" ;
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
/*
  			$pdecor=new ParagrafDecorator("",$child,$key) ;
			if ($child->GetName()=="Part")
				$form->ReplaceChild($key,new FontDecorator("red",$pdecor,$key)) ;
			else 
				$form->ReplaceChild($key,$pdecor) ;
*/				
				
		}
	}	
	
}


$form=new MasterTable('Words',array(
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


//$form->AddChild(new StringContent('Word')) ;
//$form->AddChild(new StringContent('Part')) ;

// print_r($form) ;

$composer=new HtmlFormComposer ;
$form->SetComposer($composer) ;
$form->ReCompose() ;

print_r($form) ;

echo $form->DrawFormElement() ;
?>