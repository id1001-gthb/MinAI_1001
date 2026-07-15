<?php
// not to be included explicitly, must be included only via requireFilesRecursively() after context_pre.php L 2101
//error_log("-- context -- " . __FILE__); // debug
$GLOBALS["checkpoint_context_php"] = true;

// Start metrics for this entry point
require_once(__DIR__."/utils/metrics_util.php");
minai_start_timer('context_php', 'MinAI');

// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
  return;
}

require_once(__DIR__."/config.php");
require_once(__DIR__."/util.php");
require_once(__DIR__."/contextbuilders.php");
require_once(__DIR__."/mind_influence.php");
require_once(__DIR__."/environmentalContext.php");
require_once(__DIR__."/contextbuilders/system_prompt_context.php");
require_once(__DIR__."/utils/prompt_slop_cleanup.php");


minai_start_timer("contextProcessing", "context_php");

// Initialize common variables
require_once(__DIR__."/utils/init_common_variables.php");
//error_log("[init_common_variables] target=".$GLOBALS["target"]." target_gender=".$GLOBALS["target_gender"]." HERIKA_NAME=".$GLOBALS["HERIKA_NAME"]." herika_gender=".$GLOBALS["herika_gender"]." ".__FILE__); //debug

// Cache target actor
//$GLOBALS["target"] = GetTargetActor();
//$GLOBALS["target_gender"] = GetGender($GLOBALS["target"]); //Is Female($GLOBALS["target"]) ? "female" : "male";
//$GLOBALS["target_pronouns"] = GetActorPronouns($GLOBALS["target"]);

//-------------------------------------------------

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


//--------------------------------------------------------------

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

//require(__DIR__."/command_prompt_custom.php");

//error_log("-- context php -- ENFORCE_ACTIONS_PROMPT=".$GLOBALS["ENFORCE_ACTIONS_PROMPT"]); // debug
/*
$s_minp = trim($GLOBALS["MINAI_ACTION_PROMPT"] ?? '');
if (strlen($s_minp) > 0) {
	//$GLOBALS["TEMPLATE_DIALOG"] .= "\n".$s_minp;
	//$GLOBALS["contextDataFull"][] = array('role' => 'user', 'content' => $s_minp); // not last entry, not effective
	
	//$GLOBALS["ENFORCE_ACTIONS_PROMPT"] = true;
	//$GLOBALS["PATCH_PROMPT_ENFORCE_ACTIONS"] = true;
	//$GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = $s_minp; // ."\n". $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"];

  //setConfOption("_minai_action_prompt", $s_minp);

} else {
	if ($GLOBALS["HERIKA_NAME"] !== 'The Narrator')
		error_log("[context php] {$GLOBALS["HERIKA_NAME"]} NO PROMPT! ENFORCE_ACTIONS_PROMPT=".$GLOBALS["ENFORCE_ACTIONS_PROMPT"]); // debug
}
*/
minai_stop_timer('context_php');
// minai_stop_timer('Pre-LLM');
