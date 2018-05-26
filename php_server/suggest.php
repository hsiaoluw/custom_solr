<?php
/**
 * Created by PhpStorm.
 * User: hsiao-lun
 * Date: 5/22/18
 * Time: 9:49 AM
 */
require_once('Apache/Solr/Service.php');
$suggest = isset($_REQUEST['suggest'])?$_REQUEST['suggest']:false;

if($suggest){
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/custom_solr/');
    if (get_magic_quotes_gpc() == 1)
    {
        $suggest = stripslashes($suggest);
    }
    try {
        $suggest_results = $solr->suggest($suggest);
    } catch (Apache_Solr_HttpTransportException $e) {
    } catch (Apache_Solr_InvalidArgumentException $e) {
    }
    $return_results="";
    foreach ($suggest_results->suggest->suggest->$suggest->suggestions as $suggest_list=>$value){
           $return_results = $return_results." ". $value->term;
    }
    echo $return_results;
}
?>