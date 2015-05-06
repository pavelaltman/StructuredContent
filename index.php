<?php
require_once 'content.php';

// create settings
$settings=new Settings("sc_", "_content","_state","mainform") ;

// create MySqli connection
$db=new MySqliConnector('dollsfun.mysql.ukraine.com.ua','dollsfun_content','93hfkudn', 'dollsfun_content') ;

// create form interface and imlementation
$imp=new HtmlFormImp() ;
$form_interface=new FormInterface($imp) ;

$view=new View($settings,$db,$form_interface) ;

echo '<p><a href="structure.php">Structure</a></p>' ;
echo $view->GetView() ;

/* temp code to update smth
 $defs=$db->QueryObjectIterator("select * from sc_Definitions") ;
 for ($defs->First() ; !$defs->IsDone() ; $defs->Next())
 {
 $row=$defs->Current() ;

 $db->SimpleQuery("update sc_Words set Topics=".$row->topic_id." where sc_Words.Id=".$row->Words) ;
 }
*/

?>