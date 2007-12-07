<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'asv_random_article';

$plugin['version'] = '1.0';
$plugin['author'] = 'Amit Varia';
$plugin['author_uri'] = 'http://www.amitvaria.com/';
$plugin['description'] = 'Generates a random article';

// Plugin types:
// 0 = regular plugin; loaded on the public web side only
// 1 = admin plugin; loaded on both the public and admin side
// 2 = library; loaded only when include_plugin() or require_plugin() is called
$plugin['type'] = 0; 


@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---
function asv_random_article($atts){
    global $pretext,$prefs, $thisarticle;
    extract($prefs);
    extract($pretext);
    
    extract(lAtts(array(
    'form' => 'default',
    'comment_count' =>'0',
        'limit' =>'1',
    ),$atts));
            
    $rs = safe_rows('*,unix_timestamp(Posted) as uPosted', 'textpattern','comments_count="$comment_count" and status in (4,5) order by rand() LIMIT '.$limit);
$articles ='';

    foreach ($rs as $r) {
        extract($r);
        populateArticleData($r);

    if(!empty($thisarticle)) {
        extract($thisarticle);

        // define the article form
        $article = fetch_form($form);

        $article = parse($article);

        unset($GLOBALS['thisarticle']);    

        $articles.=$article;
    }
    }

return $articles;
} 
# --- END PLUGIN CODE ---

?>
