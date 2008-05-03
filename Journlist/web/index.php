<?php
    
    include_once("journlist/helper/db_connect.php");
    
    $db = new DB();
    $db->connect();
    
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title></title>
    </head>
    <body>
        <?php
           
        ?>
        <h1>Welcome to Journlist</h1>
    </body>
</html>

<?php

    $db->close();
    
?>
            
