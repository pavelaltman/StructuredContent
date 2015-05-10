<?php

/* package "Command"
 * contains GoF Command class, POSTCommand and Dispatcher classes
 * to implement commands from POST request
 */


// GoF Command class
abstract class Command
{
	function Execute() {}
}


// Command from POST request 
class POSTCommand extends Command
{
	protected $value,   // value from POST request 
	          $suffix ; // suffix after command name in request

	function SetValue($value) { $this->value=$value ; }
	function SetSuffix($suffix) { $this->suffix=$suffix ; }
}


// dispatcher knows only command names and has command objects to Execute()
// it works like menu
class Dispatcher
{
	private $commands_array ; // array of named Commands ;

	function __construct($arr) { $this->commands_array=$arr ; }
	function GetCommand($name) { return $this->commands_array[$name] ; }

	// Executes command if command name exist in POST request
	function ExecuteFromPOST()
	{
		// find substring "Command" in POST and store key in $com_str
		foreach($_POST as $key => $value)
			if (strpos($key,'Command')!==false)
				$com_str=$key ;

		// find command in commands array
		foreach($this->commands_array as $name => $command)
			if (strpos($com_str, $name)!==false)
			{
				// obtain command object
				$command=$this->GetCommand($name) ;
				
				// set command vaue
				$command->SetValue($_POST[$com_str]) ;

				// calculate and set suffix
				$post_len=strlen($com_str) ;
				$com_len=strlen($name) ;
				$command->SetSuffix(substr($com_str,$com_len,$post_len-$com_len)) ;
				
				$command->Execute() ;
			}
	}
}
?>