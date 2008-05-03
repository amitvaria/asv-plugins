<?php
    
    include("/Users/amitvaria/Sites/Journlist/journlist/config.php");    
    
    class DB {
        var $link;
        
        function connect() {
            $this->link = mysql_connect("localhost", "root", "Gujuman29") or die('no db');
            mysql_select_db("journlist");
        }
        
        function close() {
            mysql_close($this->link);
        }
        
    }


?>
