<?php 

// work in progress 

function GetPenisSizeDetails($char_name='', $race_name='', $b_short=true) {
	$s_res = "";
	//['npc name'][ [short desc], [long desc] ]

	$s_name = strtolower(trim($char_name));
	$s_race = strtolower(trim($race_name));
	if (strlen($s_name) > 0) {
		$size_dictionary = [
		//'name' => ["short","long"],
		//"" => ["",""],
		"_" => ["",""]
		];
		
		$ix = $b_short ? 0 : 1; 
		if (isset($size_dictionary[$s_name][$ix])) {
			$s_res = $size_dictionary[$s_name][$ix];
			//error_log(" name found : $s_name - $s_res"); // debug
		}	
	} 
	if ((strlen($s_res) < 1) && (strlen($s_race) > 0)) {
		$size_dictionary = [
		//'name' => ["short","long"],
		"khajiit" => ["barbed with hardened spines for pain and stimulation","Like many felines, Khajiit penis is barbed and has hardened spines that stimulate the female for ovulation. This conformation can cause discomfort or pain to females or, if the organ is well handled, intense pleasure. "],
		"orc" => ["firm with knobby tip and thick base with stimulation horn for clit","The Orc penis is a green, firm yet flexible shaft with smooth ridges and a knobby tip that expands and then nearly doubles in width as the shaft is fully inserted. At the thickened base, the dong has an additional horn designed for double stimulation of the female clitoris or perineum. "],
		//"" => ["",""],
		"_" => ["",""]
		];
		
		$ix = $b_short ? 0 : 1; 
		if (isset($size_dictionary[$s_race][$ix])) {
			$s_res = $size_dictionary[$s_race][$ix];
			//error_log(" race found : $s_race - $s_res"); // debug
		}	
		
	} 
	if (strlen($s_res) > 0) {
		if ($b_short) {
			$s_res = ", ".$s_res;
		} else {
			$s_res = "\n".$s_res."\n";
		}
	} 
	return $s_res;
}

