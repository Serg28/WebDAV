<?php
include ('Client.php');
include ('Config.php');
include ('Config/YaDisk.php');
include ('Result.php');


 use dvcarrot\WebDAV\Config\YaDisk as Config;
    use dvcarrot\WebDAV\Client;
    $config = new Config('login', 'pass');
    $client = new Client($config);
    //$result = $client->propfind('backups');
    //echo $config->hostname;
    $client->get_file('backups/test1.zip');
   //var_dump($config->hostname.'backups/test1.zip');
?>
