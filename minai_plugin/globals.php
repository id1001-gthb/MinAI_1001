<?php
// not to be included explicitly, must be included only via requireFiles Recursively()
// Start metrics for this entry point
require_once("utils/metrics_util.php");
// $globalTimer = new MinAITimerScope('globals_php', 'MinAI');

require_once("config.base.php");
require_once("logger.php");
require_once("util.php");

$pluginPath = "/var/www/html/HerikaServer/ext/minai_plugin";
if (!file_exists("$pluginPath/config.php")) {
    copy("$pluginPath/config.base.php", "$pluginPath/config.php");
}
require_once("config.php");

if ((isset($GLOBALS["action_prompts"]["normal_scene"])) &&
    (isset($GLOBALS["action_prompts"]["explicit_scene"]))) {
    if (!isset($GLOBALS["action_prompts_copy"])) {
        $GLOBALS["action_prompts_copy"] = $GLOBALS["action_prompts"];
        //error_log(" globals: making action_prompts copy ");
    }
}

$MINAI_ACTION_PROMPT = '';
$GLOBALS["SEMAPHORES_TIMEOUT"] = 51; 
$GLOBALS["ENFORCE_ACTIONS_PROMPT"] = true;
$GLOBALS["PATCH_PROMPT_ENFORCE_ACTIONS"] = true;
$GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = '';

//error_log("-- globals -- ");
//Logger::warn($GLOBALS["HERIKA_NAME"]." ".$GLOBALS["ORIGINAL_HERIKA_NAME"]); // debug

$b_clean_narrator = ($GLOBALS["HIDE_NARRATOR_DIALOGUE"] ?? false) || ($GLOBALS["stop_narrator_context_leak"] ?? false);
$GLOBALS["HIDE_NARRATOR_DIALOGUE"] = $b_clean_narrator;
$GLOBALS["stop_narrator_context_leak"] = $b_clean_narrator;

$b_no_asterisk = ($GLOBALS['REMOVE_ASTERISKS_FROM_OUTPUT'] ?? true) || ($GLOBALS['strip_emotes_from_output'] ?? true) ;
$GLOBALS['strip_emotes_from_output'] = $b_no_asterisk;
$GLOBALS['REMOVE_ASTERISKS_FROM_OUTPUT'] = $b_no_asterisk;




$GLOBALS["TTS_FALLBACK_FNCT"] = function($responseTextUnmooded, $mood, $responseText) {

    if (!isset($GLOBALS["db"]))
        $GLOBALS["db"] = new sql();
    require_once("config.php");
    require_once("util.php");
    if ($GLOBALS["HERIKA_NAME"] == "Player")
        return;
    if (!isset($GLOBALS["speaker"]))
        $GLOBALS["speaker"] = $GLOBALS["HERIKA_NAME"];
    $race = str_replace(" ", "", strtolower(GetActorValue($GLOBALS["speaker"], "Race")));
    $gender = strtolower(GetActorValue($GLOBALS["speaker"], "Gender"));
    if ($gender.$race) {
        $fallback = $GLOBALS["voicetype_fallbacks"][$gender.$race];
    }
    if (!isset($fallback)) {
        minai_log("info", "Warning: Could not find fallback for {$GLOBALS["speaker"]}: {$gender}{$race}. Using last resort fallback: malecommoner");
        $fallback = "malecommoner";
    }
    minai_log("info", "Voice type fallback to {$fallback} for {$GLOBALS["speaker"]}");
    $GLOBALS["TTS"]["FORCED_VOICE_DEV"] = $fallback;
    $GLOBALS["TTS"]["MELOTTS"]["voiceid"] = $fallback;
    
    if(isset($GLOBALS["TTS_IN_USE"])) {
        return $GLOBALS["TTS_IN_USE"]($responseTextUnmooded, $mood, $responseText);
    }
    else {
        minai_log("info", "Not retrying, No TTS function enabled");
    }
    return null;
};


$GLOBALS["external_fast_commands"] = [
    // Events that set $MUST_DIE=true in customintegrations.php
    "minai_init",             // Initialization event
    "storecontext",           // Store custom context
    "registeraction",         // Register custom action
    "updatethreadsdb",        // Update threads database
    "storetattoodesc",        // Store tattoo description
    "minai_storeitem",        // Store single item
    "minai_storeitem_batch",  // Store multiple items
    "minai_diary",            // Diary request
    "minai_updateprofile"     // Profile update request
    // "minai_clearinventory"    // Clear inventory
];

if (!isset($GLOBALS["NPC_react_to_non_consensual_acts"])) 
    $GLOBALS["NPC_react_to_non_consensual_acts"] = true;

$GLOBALS["CHIM_NO_EXAMPLES"] = true;
$GLOBALS["CHIM_DEBUG_LEVEL"] = 0;

if (IsRadiant()) {
	//error_log(" Radiant - exec trace "); //debug
	$GLOBALS["BORED_EVENT_SERVERSIDE"] = false; // MinAI radiant will suspend CHIM bored sside event
  $GLOBALS["RANDOM_NARATION"] = false;
} 

