<?php
// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');
// The Apache Solr Client library should be on the include path
// which is usually most easily accomplished by placing in the
// same directory as this script ( . or current directory is a default
// php include path entry in the php.ini)
require_once('Apache/Solr/Service.php');
$limit = 10;
$page  = isset($_REQUEST['page'])? $_REQUEST['page'] : 0;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;

$s = isset($_REQUEST['s'])? $_REQUEST['s'] : "ori";
if ($s == "ori"){ $sort_method="score desc";}
else                      { $sort_method="pageRankFile desc";}
$results = false;

if ($query)
{


    // create a new solr service instance - host, port, and corename
    // path (all defaults in this example)
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/custom_solr/');
    // if magic quotes is enabled then stripslashes will be needed
    if (get_magic_quotes_gpc() == 1)
    {
        $query = stripslashes($query);
    }
    $final_query = "\"".$query."\"";
    // in production code you'll always want to use a try /catch for any
    // possible exceptions emitted by searching (i.e. connection
    // problems or a query parsing error)
    $additionalParameters = array(
        'sort' => $sort_method,
        'fl' => array('id', 'title', 'og_description', 'og_url')
    );

    try
    {

        $results = $solr->search($final_query, $page*$limit, $limit, $additionalParameters);
    }
    catch (Exception $e)
    {
        // in production you'd probably log or email this error to an admin
        // and then show a special message to the user but for this example
        // we're going to show the full exception
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Engine With PageRank</title>
    <link rel="stylesheet" type="text/css" href="client.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<div class="centerall" align="center">
    <div class="jumbotron text-center">
        <form accept-charset="utf-8" method="get">
            <label for="q">Search:</label>
            <input id="q" name="q" type="text" list="candidate" autocomplete="off" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" onkeyup="send_suggest()"/>
            <datalist id="candidate"></datalist>
            <button type="submit" class="btn btn-default"> <span class="glyphicon glyphicon-search"></span> </button>
            <input type="radio" name="s" id="s" value="ori" <?php if(!isset($s)|| isset($s)&&($s=="ori"))  echo checked; ?>> Solr Original
            <input type="radio" name="s" id="s" value="pagerank" <?php  if(isset($s)&&($s=="pagerank")) echo checked;?>> PageRank
        </form>
    </div>
    <script>
        function send_suggest() {
            var suggest_q = $("#q").val();
            $.get("suggest.php?suggest="+suggest_q, function(data, status){
                var res = data.split(" ");
                $("#candidate").empty();
                res.forEach(
                    function (element) {
                            element.replace("."," ");
                            $("#candidate").append("<option value=\""+element+"\">");
                    });
            });
        }
    </script>
<?php



if ($results)
{
    include 'SpellCorrector.php';
    $correct = SpellCorrector::correct($query);
    $total = (int) $results->response->numFound;
    $start = min(max(1, $results->response->start+1), $total);
    $end = min($start+$limit-1, $total);
    //echo $correct;
    if (!($correct==$query)) { ?>
       <div> Did you mean?<?php echo '<a href="client.php?q='.$correct.'&s='.$s.'">'.$correct.'</a>'; ?></div>
    <?php } ?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>

        <?php
        // iterate result documents
        foreach ($results->response->docs as $doc)
        {
            foreach ($doc as $field => $value) {
                if($field == 'title')  $title = $value;
                if($field == 'og_url') $src   = $value;
                if($field == 'og_description') $des = $value;
            }
            ?>
                <div class="row">
                <div class="card_style" style="text-align: left; width: 100rem;">
                    <p class="search_title"><a href="<?php echo $src;?>"> <?php echo $title;?></a></p>
                    <p class="url_small"><?php echo $src;?></p>
                    <p class="description"><?php echo $des;?></p>
                </div>
                </div>
            <?php
        }
        ?>

    <?php
}
?>
<?php

    if($results)
     {   echo '<div class="page_search" align="center">';
         if($page>0)                            echo '<a href="client.php?page=' .($page-1). '&q='.$query.'&s='.$s.'"'.'  class="search">&laquo; Previous</a>';
         if(($page+1)* $limit < $total)         echo '<a href="client.php?page=' .($page+1). '&q='.$query.'&s='.$s.'"'.'  class="search">Next &raquo;</a>';
         echo '</div >';
     }
?>
</div>
</body>
</html>