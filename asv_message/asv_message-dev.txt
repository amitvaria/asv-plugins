function asv_message($atts)
{
	global $thiscomment;

    extract(lAtts(array(
        'striptags' => 'n',
        'maxwords'  => -1,
		'wraptag'		=> 'p',
		'class' 	=> '',

    ),$atts));

	assert_comment();
	$thismessage = $thiscomment['message'];
	
	if ($striptags != 'n'){
		$thismessage = strip_tags($thismessage );
	}
	
	if ($maxwords >= 0){	
		$wrds = explode(' ', $thismessage );
		if(count($wrds) > $maxwords){
			$thismessage = '';
			for($i=0; $i<$maxwords; $i++) {
				$thismessage .= $wrds[$i].' ';
			}
			$thismessage = $thismessage .'&#8230;';
		} 
	}
	
	return doTag($thismessage, $wraptag, $class) ;
}