<?php
// not to be included explicitly, must be included only via requireFilesRecursively() L 2101

// Start metrics for this entry point
require_once("utils/metrics_util.php");
minai_start_timer('context_php', 'MinAI');

// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
  return;
}

require_once("config.php");
require_once("util.php");
require_once("contextbuilders.php");
require_once("mind_influence.php");
require_once("environmentalContext.php");
require_once("contextbuilders/system_prompt_context.php");
require_once("utils/prompt_slop_cleanup.php");

minai_start_timer("contextProcessing", "context_php");


// Cache target actor
$GLOBALS["target"] = GetTargetActor();
$GLOBALS["target_gender"] = GetGender($GLOBALS["target"]); //Is Female($GLOBALS["target"]) ? "female" : "male";
$GLOBALS["target_pronouns"] = GetActorPronouns($GLOBALS["target"]);


// if context.php is required before head[] assignment
//requireFilesRecursively(__DIR__.DIRECTORY_SEPARATOR."ext".DIRECTORY_SEPARATOR,"context.php");

if (!isset($GLOBALS['head'])) {
	
	if (!empty($GLOBALS["OGHMA_HINT"])) {

		$GLOBALS['head'][] = array('role' => 'system', 'content' =>  
			strtr($GLOBALS["PROMPT_HEAD"] . "\n\n".
			$GLOBALS["HERIKA_PERS"]."\n\n". 
			$GLOBALS["dynamicBiography"]."\n\n" .  //$dynamicBiography . "\n\n" . 
			$GLOBALS["OGHMA_HINT"]."\n\n". 
			$GLOBALS["COMMAND_PROMPT"]."\n\n".
			$GLOBALS["actionsList"]."\n\n". //$actionsList.
			$GLOBALS["nearbySections"]."\n\n". //$nearbySections.
			$GLOBALS["paralinguisticTagsPrompt"]."\n\n". //$paralinguisticTagsPrompt.
			$GLOBALS["rumorsText"], // "\n{$rumorsText}\n",
			["#PLAYER_NAME#"=>$GLOBALS["PLAYER_NAME"],"#HERIKA_NAME#"=>$GLOBALS["HERIKA_NAME"]])

		);
		//avoid reinjecting command prompt that we have already appended
		$GLOBALS["COMMAND_PROMPT"] = "";
	} else {
		$GLOBALS['head'][] = array('role' => 'system', 'content' =>  
			strtr(
			$GLOBALS["PROMPT_HEAD"]."\n\n".
			$GLOBALS["HERIKA_PERS"]."\n\n". 
			$GLOBALS["dynamicBiography"]."\n\n" .  
			$GLOBALS["COMMAND_PROMPT"]."\n\n".
			$GLOBALS["actionsList"]."\n\n". 
			$GLOBALS["nearbySections"]."\n\n". 
			$GLOBALS["paralinguisticTagsPrompt"]."\n\n". 
			$GLOBALS["rumorsText"], 
			["#PLAYER_NAME#"=>$GLOBALS["PLAYER_NAME"],
			 "#HERIKA_NAME#"=>$GLOBALS["HERIKA_NAME"]])
		);
		//avoid reinjecting command prompt that we have already appended
		$GLOBALS["COMMAND_PROMPT"] = "";
	} 
	
	$GLOBALS["COMMAND_PROMPT"] = "";
	error_log("[context.php] head assigned. - exec trace "); // debug
}

//error_log("->functions ctx: " . implode(' . ', $GLOBALS["ENABLED_ FUNCTIONS"]));
/*
if (isset($GLOBALS["ENABLED_ FUNCTIONS"]) && (count($GLOBALS["ENABLED_ FUNCTIONS"])>0)) {
	//$s_ef = implode(' . ', $GLOBALS["ENABLED_ FUNCTIONS"];
	if (count($GLOBALS["ENABLED_ FUNCTIONS"]) < count($GLOBALS["ENABLED_ FUNCTIONS_COPY"])) {
		if (
			//(!in_array('ExtCmdIncreaseArousal',$GLOBALS["ENABLED_ FUNCTIONS"])) && 
			//(!in_array('ExtCmdDecreaseArousal',$GLOBALS["ENABLED_ FUNCTIONS"])) && 
			(!in_array('ExtCmdGiveItem',$GLOBALS["ENABLED_ FUNCTIONS"])) && 
			(!in_array('ExtCmdTakeItem',$GLOBALS["ENABLED_ FUNCTIONS"])) && 
			(!in_array('ExtCmdTrade',$GLOBALS["ENABLED_ FUNCTIONS"])) && 
			(!in_array('ExtCmdStartLooting',$GLOBALS["ENABLED_ FUNCTIONS"])) && 
			(!in_array('ExtCmdStopLooting',$GLOBALS["ENABLED_ FUNCTIONS"])) && 
			(!in_array('ExtCmdFollow',$GLOBALS["ENABLED_ FUNCTIONS"]))  
		){ 	// broken functions
			$GLOBALS["ENABLED_ FUNCTIONS"] = $GLOBALS["ENABLED_ FUNCTIONS_COPY"];
			error_log("Warning: functions replaced from copy. ");
		}
	}
}
*/


//---------------------------------------------------------------------------
// Slop cleanup:
// Delete useless information.
// Values are case insensitive.
// Values should ordered from longer first to shortest last. 
//---------------------------------------------------------------------------

$str_to_clean_list = [ // all these are deleted from output
	'## Snow Fox (far away)',  
	'## Rabbit (far away)',
	'## Snake (far away)',
	'## Deer (far away)',
	'## Goat (far away)',
	'## Cow (far away)',
	'## Fox (far away)',
	'2 Snow Fox (far away)', 
	'Snow Fox (far away)', 
	'2 Rabbit (far away)',
	'Rabbit (far away)',
	'Snake (far away)',
	'2 Deer (far away)',
	'Deer (far away)',
	'2 Goat (far away)',
	'Goat (far away)',
	//'2 Wolf (far away)',
	//'Wolf (far away)',
	'2 Cow (far away)',
	'Cow (far away)',
	'2 Fox (far away)',
	'Fox (far away)',
	
	'2 Frost Troll (dead)',
	'Frost Troll (dead)',
	'2 Frostbite Spider (dead)',
	'Frostbite Spider (dead)',
	'2 Giant Youngling (dead)',
	'Giant Youngling (dead)',
	'2 Giantess (dead)',
	'Giantess (dead)',
	'2 Giant (dead)',
	'Giant (dead)',
	'2 Wolf (dead)',
	'Wolf (dead)',
	'2 Bear (dead)',
	'Bear (dead)',
	'2 Snow Bear (dead)',
	'Snow Bear (dead)',
	'2 Deer (dead)',
	'Deer (dead)',
	'2 Goat (dead)',
	'Goat (dead)',
	'2 Cow (dead)',
	'Cow (dead)',
	'2 Fox (dead)',
	'Fox (dead)',

	// 	
	'(hint:)' 
];

$targets_to_clean_list = [ // all these are deleted from targets list
	'2 Snow Fox (far away),', 
	'Snow Fox (far away),', 
	'2 Rabbit (far away),',
	'Rabbit (far away),',
	'Snake (far away),',
	'2 Deer (far away),',
	'Deer (far away),',
	'2 Goat (far away),',
	'Goat (far away),',
	//'2 Wolf (far away),',
	//'Wolf (far away),',
	'2 Cow (far away),',
	'Cow (far away),',
	'2 Fox (far away),',
	'Fox (far away),',
	
	'2 Frost Troll (dead),',
	'Frost Troll (dead),',
	'2 Frostbite Spider (dead),',
	'Frostbite Spider (dead),',
	'2 Giant Youngling (dead),',
	'Giant Youngling (dead),',
	'2 Giantess (dead),',
	'Giantess (dead),',
	'2 Giant (dead),',
	'Giant (dead),',
	'2 Wolf (dead),',
	'Wolf (dead),',
	'2 Bear (dead),',
	'Bear (dead),',
	'2 Snow Bear (dead),',
	'Snow Bear (dead),',
	'2 Deer (dead),',
	'Deer (dead),',
	'2 Goat (dead),',
	'Goat (dead),',
	'2 Cow (dead),',
	'Cow (dead),',
	'2 Fox (dead),',
	'Fox (dead),'
	
	
];

//---------------------------------------------------------------------------
// Multiple replacements:
// Dictionary is parsed first to last for replacements. 
// 'key' => 'value'
// Any key found is replaced with value. 
// Keys are case sensitive.
// Keys should ordered from longer first to shortest last. 
//---------------------------------------------------------------------------

$replacements_dictionary = [ // hardwired for now, probably better as an external resource

	//# HISTORIC DIALOGUE AND EVENTS IN CHRONOLOGICAL ORDER
	'HISTORIC DIALOGUE AND EVENTS IN CHRONOLOGICAL ORDER' => 'DIALOGUE HISTORY and RECENT EVENTS in chronological order',
	//# NEARBY ACTORS/NPC IN THE SCENE 
	'NEARBY ACTORS/NPC IN THE SCENE' => 'NEARBY CHARACTERS IN THE SCENE',

    //Player:The Narrator:	
    $GLOBALS["PLAYER_NAME"].":The Narrator:" => $GLOBALS["PLAYER_NAME"].":",
	'Snow Fox (hostile)' => 'Snow Fox', 
	'Rabbit (hostile)' => 'Rabbit', 
	'Snake (hostile)' => 'Snake', 
	'2 Deer (hostile)' => '2 Deer',
	'Deer (hostile)' => 'Deer',
	'2 Goat (hostile)' => '2 Goat', 
	'Goat (hostile)' => 'Goat', 
	'Cow (hostile)' => 'Cow', 
	'Fox (hostile)' => 'Fox', 
	'Rat (hostile)' => 'Rat',

	// cleanup:
	'  ' => ' ',
	', ,' => ',',
	', .' => '.',
	',.' => '.',
	'),' => ',',
	',,' => ','
];            


function CustomLineProcess($contextLine="", $s2clean_list, $repl_dictionary) {
// clean context element 
	$s_res = "";
	if (strlen(trim($contextLine)) > 0) {
		$s_clean1 = str_ireplace($s2clean_list, [' '], $contextLine);
		$s_clean2 = strtr($s_clean1, $repl_dictionary);
		$s_res = $s_clean2;
	}
	return $s_res;
}

function CustomContextProcess($contextData, $str2clean_list, $replace_dictionary) {
// clean context array elements 
    if (!is_array($contextData)) {
        return $contextData;
    }

    if (!is_array($str2clean_list)) {
		if (strlen($str2clean_list)<1)
			return $contextData;
    }

    if (!is_array($replace_dictionary)) {
        return $contextData;
    } else {
		if (count($replace_dictionary)<1)
			return $contextData;
	}

	$i = 0;
	
	$cleaned_res = [];
	foreach ($contextData as $entry) {
        if (!isset($entry['content'])) {
            continue;
        }
		
        $originalContent = $entry['content'];	
		$s_clean = CustomLineProcess($originalContent, $str2clean_list, $replace_dictionary);
		$entry['content'] = $s_clean;
		$cleaned_res[] = $entry;
		
		$i = $i + 1;
	}

	return $cleaned_res;
}

function CustomFunctionsProcess($contextData, $str2clean_list, $replace_dictionary) {
// clean functions
    if (!is_array($contextData)) {
        return $contextData;
    }

    if (!is_array($str2clean_list)) {
		if (strlen($str2clean_list)<1)
			return $contextData;
    }

    if (!is_array($replace_dictionary)) {
        return $contextData;
    } else {
		if (count($replace_dictionary)<1)
			return $contextData;
	}
	
	$i = 0;
	
	$cleaned_res = [];
	foreach ($contextData as $entry) {
        if (!isset($entry['description'])) {
            continue;
        }
		
        $originalContent = $entry['description'];	

		$s_clean = CustomLineProcess($originalContent, $str2clean_list, $replace_dictionary);
		$entry['description'] = $s_clean;
		$cleaned_res[] = $entry;
		
		$i = $i + 1;
	}
	return $cleaned_res;
}

Function CustomCleanTargets($clean_corpses=false, $clean_far_away=false, $clean_hostile_rabbits=false) {
// clean targets list
	if (isset($GLOBALS["FUNCTION_PARM_INSPECT"]) && ($clean_corpses || $clean_far_away) ) {
		$s_x = implode(",", $GLOBALS["FUNCTION_PARM_INSPECT"]);

		foreach ($GLOBALS["FUNCTION_PARM_INSPECT"] as $ix => $s_target) {
			//$s_x .= $s_target.",";
			if ($clean_corpses && stripos($s_target,'(dead)')) {
				unset($GLOBALS["FUNCTION_PARM_INSPECT"][$ix]);
			}
			if ($clean_far_away && stripos($s_target,'(far away)')) {
				unset($GLOBALS["FUNCTION_PARM_INSPECT"][$ix]);
			}
			if ($clean_hostile_rabbits && stripos($s_target,'(hostile)')) {
				if (stripos($s_target,'rabbit ') || 
					stripos($s_target,'horse ') || 
					stripos($s_target,'deer ') || 
					stripos($s_target,'goat ') || 
					stripos($s_target,'elk ') || 
					stripos($s_target,'cow ') || 
					stripos($s_target,'cat ') || 
					stripos($s_target,'fox ') || 
					
					stripos($s_target,'rat ') 
				) {
					unset($GLOBALS["FUNCTION_PARM_INSPECT"][$ix]);
				}
			}
			//$s_y .= $s_target.",";
		}
		$s_y = implode(",", $GLOBALS["FUNCTION_PARM_INSPECT"]);
	}
}


//--------------------------------------------------------------
// context replacements:
//--------------------------------------------------------------

if (isset($GLOBALS['head'])) { // clean system (head) prompt
	if (is_array($GLOBALS['head'])) {
		$a_x = CustomContextProcess($GLOBALS['head'], $str_to_clean_list, $replacements_dictionary); 
		$GLOBALS['head'] = $a_x; 
	}
	
	//warn about relationship
	if (stripos($GLOBALS['head'][0]['content'],'rival, foe')) {
			error_log(" - WARNING - relationship. npc: " . ($GLOBALS["HERIKA_NAME"] ?? "?") );
	}

	//warn about placeholder
	if (strpos($GLOBALS['head'][0]['content'],'PLAYER_NAME')) {
			error_log(" - WARNING - unsolved PLAYER_NAME placeholder in prompt. npc: " . ($GLOBALS["HERIKA_NAME"] ?? "?") );
	}
	
}	

if (isset($GLOBALS["contextDataFull"])) { // clean context array parsing all elements
	$GLOBALS['contextDataFull'] = CustomContextProcess($GLOBALS['contextDataFull'], $str_to_clean_list, $replacements_dictionary); 
} else 
	error_log("[context.php] ERROR contextDataFull not defined! ".__FILE__." ".__LINE__); // error

if (isset($GLOBALS["FUNCTIONS_ARE_ENABLED"]) && $GLOBALS["FUNCTIONS_ARE_ENABLED"]) { // clean function descriptions (targets)

	CustomCleanTargets(true, true, true);
	/*
	if (isset($GLOBALS["FUNCTION_PARM_INSPECT"])) {
		$s_x = implode(",", $GLOBALS["FUNCTION_PARM_INSPECT"]);
		$s_y = str_ireplace($targets_to_clean_list, [','], $s_x);
		$GLOBALS["FUNCTION_PARM_INSPECT"] = explode(",",$s_y);
	}
	*/
	//if (isset($GLOBALS["FUNCTIONS"])) {
	//	$GLOBALS["FUNCTIONS"] = CustomFunctionsProcess($GLOBALS["FUNCTIONS"], $str_to_clean_list, $replacements_dictionary);
	//}
	
}

if (isset($GLOBALS["TTS_FFMPEG_FILTERS"]["tempo"])) {
	$s_tempo = $GLOBALS["TTS_FFMPEG_FILTERS"]["tempo"];  
	error_log("TTS_FFMPEG_FILTERS {$s_tempo} - exec trace " .__FILE__." ".__LINE__); // debug

	if (stripos($s_tempo,"atempo=0.") !== false ) {
		$GLOBALS["TTS_FFMPEG_FILTERS"]["tempo"] = 'atempo=0.95'; 
	} else {
		if (stripos($s_tempo,"atempo=1.") !== false ) { //='atempo=1.45';
			$GLOBALS["TTS_FFMPEG_FILTERS"]["tempo"] = 'atempo=1.05'; 
		}
	}
}

//--------------------------------------------------------------

// Clean up context
$locaLastElement=[];
$narratorElements=[];
$sexInfoElements=[];
$physicsInfoElements=[];

foreach ($GLOBALS["contextDataFull"] as $n=>$ctxLine) {
    if (strpos($ctxLine["content"],"#SEX_SCENARIO")!==false) {
        preg_match('/#ID_(\d+)/', $ctxLine["content"], $matches);
        if (!empty($matches)) {
          $threadId = $matches[1];
        } else {
          $threadId = "other";
        }

        if (!isset($locaLastElement[$threadId])) {
          $locaLastElement[$threadId] = []; // Initialize as an array if it doesn't exist
        }
        array_push($locaLastElement[$threadId], $n);
    }
    if ($GLOBALS["stop_narrator_context_leak"] && $GLOBALS["HERIKA_NAME"] != "The Narrator") {
        if (strpos($ctxLine["content"],"The Narrator:")!==false && strpos($ctxLine["content"],"(talking to")!==false) {
            $narratorElements[]=$n;
        }
    }
    if (strpos($ctxLine["content"],"#SEX_INFO")!==false) {
        $sexInfoElements[]=$n;
    }
    if (strpos($ctxLine["content"],"#PHYSICS_INFO")!==false) {
        $physicsInfoElements[]=$n;
    }
}

// Remove all references to sex scene, and only keep the last one.
// Add support for multithread scenes to keep scene descriptions for all threads
foreach ($locaLastElement as $thredId => $threadCtxLines) {
  if(is_array($threadCtxLines) && !empty($threadCtxLines)) {
    // try to find context #SEX_SCENARIO scene among currently running scenes
    $scene = getScene("", $thredId);
    // We want to keep last context line from 'other' category(if any), or for active scenes. If scene stopeed and not playing anymore we don't want to put it into context
    if($thredId === "other" || isset($scene)) {
      array_pop($threadCtxLines);
    }
    foreach ($threadCtxLines as $n) {
      unset($GLOBALS["contextDataFull"][$n]); 
    }
  }
}

// Cleanup narrator context for non-narrator actors
foreach ($narratorElements as $n) {
    unset($GLOBALS["contextDataFull"][$n]); 
}

// Remove all references to sex scene info, and only keep the last one.
array_pop($sexInfoElements);
foreach ($sexInfoElements as $n) {
    unset($GLOBALS["contextDataFull"][$n]); 
}

// Remove all references to physics / collision info, and only keep the last three.
array_pop($physicsInfoElements);
array_pop($physicsInfoElements);
array_pop($physicsInfoElements);
foreach ($physicsInfoElements as $n) {
    unset($GLOBALS["contextDataFull"][$n]); 
}

/*$nullValues = [];
foreach ($GLOBALS["contextDataFull"] as $n=>$ctxLine) {
    minai_log("info", "Checking ({$n}) {$ctxLine["content"]}");
    if (!$ctxLine["content"] || $ctxLine["content"] == null || $ctxLine["content"]  == "") {
        $nullValues[] = $n;
        minai_log("info", "Found null value in context ({$n})");
    }
}
foreach ($nullValues as $n) {
    minai_log("info", "Unsetting null value $n");
    unset($GLOBALS["contextDataFull"][$n]); 
}*/


// Cleanup self narrator dialogue to avoid contaminating general context
if ($GLOBALS["minai_processing_input"]) {
    minai_log("info", "Cleaning up player input");
    DeleteLastPlayerInput();
}

// Clean up slop text patterns
minai_start_timer('cleanupSlop', 'contextProcessing');

if (isset($GLOBALS["enable_prompt_slop_cleanup"]) && $GLOBALS["enable_prompt_slop_cleanup"]) {
    $GLOBALS["contextDataFull"] = cleanupSlop($GLOBALS["contextDataFull"]);
}
minai_stop_timer('cleanupSlop');

// Re-index the array after removing elements
//$GLOBALS["contextDataFull"] = array_values($GLOBALS["contextDataFull"]);

$arr_prefix = [
    'role' => 'user', 
    'content' => "<DIALOGUE_HISTORY_and_RECENT_EVENTS># DIALOGUE HISTORY and RECENT EVENTS are recorded in the following messages: "
]; 

/*$arr_suffix = [
    'role' => 'assistant', 
    'content' => " </DIALOGUE_HISTORY_and_RECENT_EVENTS> "
];*/ 

$n_elements = array_unshift($GLOBALS["contextDataFull"], $arr_prefix);
//$GLOBALS["contextDataFull"][] = $arr_suffix;

$s_line = $GLOBALS["contextDataFull"][$n_elements-1]['content'];
$GLOBALS["contextDataFull"][$n_elements-1]['content'] = $s_line . "\n </DIALOGUE_HISTORY_and_RECENT_EVENTS> "; 
 
minai_stop_timer('contextProcessing');

// Update the system prompt (0th entry) with our optimized version
UpdateSystemPrompt();

require "/var/www/html/HerikaServer/ext/minai_plugin/command_prompt_custom.php";

//error_log("-- context php -- ENFORCE_ACTIONS_PROMPT=".$GLOBALS["ENFORCE_ACTIONS_PROMPT"]); // debug

$s_minp = trim($GLOBALS["MINAI_ACTION_PROMPT"] ?? '');

if (strlen($s_minp) > 0) {
	//$GLOBALS["contextDataFull"][] = array('role' => 'user', 'content' => $s_minp); // not last entry, not effective
	$GLOBALS["ENFORCE_ACTIONS_PROMPT"] = true;
	$GLOBALS["PATCH_PROMPT_ENFORCE_ACTIONS"] = true;
	$GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = $s_minp; // ."\n". $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"];

    setConfOption("_minai_action_prompt", $s_minp);

} else {
	error_log("[context php] {$GLOBALS["HERIKA_NAME"]} NO PROMPT! ENFORCE_ACTIONS_PROMPT=".$GLOBALS["ENFORCE_ACTIONS_PROMPT"]); // debug
}

minai_stop_timer('context_php');
// minai_stop_timer('Pre-LLM');
