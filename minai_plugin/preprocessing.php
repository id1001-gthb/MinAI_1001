<?php
// not to be included explicitly, must be included only via requireFilesRecursively() L 185 after globals php
//error_log("-- preprocessing -- " . __FILE__); // debug
$GLOBALS["checkpoint_preprocessing_php"] = true;

// Start metrics for this entry point
require_once(__DIR__."/utils/metrics_util.php");


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

//--------------------------------------

$arr_fast_commands = $GLOBALS["fast_commands"] ?? ["addnpc","addbgnpc","updateprofile","updateprofile_narrator","diary","diary_narrator",
    "diary_player","_quest","setconf","request","_speech","infoloc","infonpc","infonpc_close",
    "infoaction","status_msg","delete_event","itemfound","_questdata","_uquest","location","_questreset","chat","bleedout","waitstart","waitstop",
    "util_location_name","util_faction_name","spellcast","npcspellcast","updateprofiles_batch_async","core_profile_assign","switchrace","combatbark",
    "util_location_npc","enable_bg","region","named_cell","snqe","named_cell_static","player_menu_tts_prefetch","player_menu_tts_play",
    "physics_raw"]; // raw VR contact/gaze telemetry from client plugins: log-only unless an extension opts in by renaming it in preprocessing



//"combatbark",

if (isset($GLOBALS["external_fast_commands"])) {
    $arr_fast_commands = array_merge($arr_fast_commands, $GLOBALS["external_fast_commands"]);
}

// Check for exact matches against fast commands
if (isset($GLOBALS["gameRequest"]) && in_array($GLOBALS["gameRequest"][0], $arr_fast_commands)) {
    $GLOBALS["minai_skip_processing"] = true;
    //error_log("Skip fast-request: " . $GLOBALS["gameRequest"][0]); // debug
} else {
    $GLOBALS["minai_skip_processing"] = false;
    //error_log("Processing Non-Fast request: " . $GLOBALS["gameRequest"][0]); // debug
}

// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
    return;
}

// minai_start_timer('CHIM');
// minai_start_timer('Pre-LLM', 'CHIM');
minai_start_timer('preprocessing_php', 'MinAI');

// Initialize common variables
require_once(__DIR__."/utils/init_common_variables.php");
//error_log("[init_common_variables] target=".$GLOBALS["target"]." target_gender=".$GLOBALS["target_gender"]." HERIKA_NAME=".$GLOBALS["HERIKA_NAME"]." herika_gender=".$GLOBALS["herika_gender"]." ".__FILE__); //debug

if ((!isset($GLOBALS["action_prompts"]["normal_scene"])) ||
    (!isset($GLOBALS["action_prompts"]["explicit_scene"])) ||
    (empty($GLOBALS["action_prompts"]))) {
    $GLOBALS["action_prompts"] = $GLOBALS["action_prompts_copy"]; 
    error_log("WARNING in preprocessing: CHIM made an attempt to disable MinAI action_prompts! ");
}

require_once(__DIR__."/util.php");
require_once(__DIR__."/contextbuilders.php");
require_once(__DIR__."/roleplaybuilder.php");

save_original_herika_name();

// Check for banned phrases in gameRequest[3]
/*
$banned_phrases = ["Thank you for watching", "Thanks for watching", "Thank you very much for watching"];
if (isset($GLOBALS["gameRequest"][3])) {
    $message = strtolower($GLOBALS["gameRequest"][3]);
    foreach ($banned_phrases as $phrase) {
        if (stripos($message, strtolower($phrase)) !== false) {
            error_log("MinAI: Aborting request due to banned phrase: " . $phrase);
            die("Banned phrase detected: " . $phrase);
        }
    }
}
*/

// This is a hack to get around CHIM eating "diary" requests for the player in the DLL
if (isset($GLOBALS["gameRequest"][0]) && $GLOBALS["gameRequest"][0] == "minai_diary") {
    minai_log("info", "Diary request detected for {$GLOBALS["HERIKA_NAME"]}");
    $GLOBALS["gameRequest"][0] = "diary";
}
// Check to see if this is a profile update request
if (isset($GLOBALS["gameRequest"][0]) && $GLOBALS["gameRequest"][0] == "minai_updateprofile") {
    minai_log("info", "Profile update request detected for {$GLOBALS["HERIKA_NAME"]}");
    $GLOBALS["gameRequest"][0] = "updateprofile";
}

if (isset($GLOBALS["gameRequest"][0]) && $GLOBALS["gameRequest"][0] == "minai_updateprofile_player") {
    minai_log("info", "Profile update request detected for {$GLOBALS["HERIKA_NAME"]}");
    $GLOBALS["gameRequest"][0] = "updateprofile";
    SetNarratorProfile();
}

if (isset($GLOBALS["gameRequest"][0]) && $GLOBALS["gameRequest"][0] == "minai_diary_player") {
    minai_log("info", "Diary request detected for {$GLOBALS["HERIKA_NAME"]}");
    $GLOBALS["gameRequest"][0] = "diary";
    SetNarratorProfile();
}

interceptRoleplayInput();

minai_stop_timer('preprocessing_php');
