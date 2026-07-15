<?php
// not to be included explicitly, must be included only via requireFiles Recursively() L 44
//error_log("-- globals -- " . __FILE__); // debug
$GLOBALS["checkpoint_globals_php"] = true;

if (!isset($GLOBALS["ENGINE_PATH"])) {
    $GLOBALS["ENGINE_PATH"] = $_SERVER['DOCUMENT_ROOT'].'/HerikaServer/'; // DOCUMENT_ROOT = /var/www/html
    //$GLOBALS["ENGINE_PATH"] = '/var/www/html/HerikaServer/';
    error_log("[globals] ENGINE_PATH=".$GLOBALS["ENGINE_PATH"]); // debug
}

if (!isset($GLOBALS["ENGINE_ROOT"])) {
    $GLOBALS["ENGINE_ROOT"] = $GLOBALS["ENGINE_PATH"];
}

if (!isset($GLOBALS["ENGINE_MINAI_PLUGIN_PATH"])) {
    $GLOBALS["ENGINE_MINAI_PLUGIN_PATH"] = $GLOBALS["ENGINE_PATH"]."ext/minai_plugin/";;
}
//-------------------------------------------------

//require_once($GLOBALS["ENGINE_PATH"] . "lib/l ogx.php"); // debug

//-------------------------------------------------

if (!isset($gameRequest)) {
    $gameRequest = [];
}

if (!isset($gameRequest[0])) {
    $gameRequest[0] = "";
    //$gameRequest[0]]["extra"]
}

if (!isset($gameRequest[3])) {
    $gameRequest[3] = "";
}

$GLOBALS["startTime"] = microtime(true);
$GLOBALS["FUNCTIONS_ARE_ENABLED"] = true;

//-------------------------------------------------
$GLOBALS["MINAI_ACTOR_VALUE_CACHE"] = [];

//$MINAI_ACTOR_VALUE_CACHE = [];
//-------------------------------------------------
// --- from old conf php if file missing:

if (!isset($GLOBALS["DBDRIVER"])) 
    $GLOBALS["DBDRIVER"] = 'postgresql';
if (!isset($GLOBALS["AUTOMATIC_DATABASE_BACKUPS"])) 
    $GLOBALS["AUTOMATIC_DATABASE_BACKUPS"] = false;

//if (!isset($GLOBALS[""])) $GLOBALS[""] = '';
if (!isset($GLOBALS["HERIKA_NAME"])) $GLOBALS["HERIKA_NAME"] = '';
if (!isset($GLOBALS["PLAYER_NAME"])) $GLOBALS["PLAYER_NAME"] = '';
if (!isset($GLOBALS["target"])) $GLOBALS["target"] = '';

//$DIARY_COOLDOWN='1200';
//$AUTO_DIARY_ENABLED=false;

$GLOBALS["MINAI_ACTION_PROMPT"] = '';
$GLOBALS["SEMAPHORES_TIMEOUT"] = 24; 
$GLOBALS["ENFORCE_ACTIONS_PROMPT"] = true;
$GLOBALS["PATCH_PROMPT_ENFORCE_ACTIONS"] = true;
$GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = '';
$GLOBALS["TIME_AWARENESS"] = false;

$GLOBALS["ORIGINAL_HERIKA_NAME"] = '';
$GLOBALS["ORIGINAL_HERIKA_NAME_SAVED"] = false;

$GLOBALS["herika_name_backup"] = '';

$GLOBALS["LLM_CONNECTOR_DEBUG"] = true;

if (!isset($GLOBALS["NPC_react_to_non_consensual_acts"])) 
    $GLOBALS["NPC_react_to_non_consensual_acts"] = true;

$GLOBALS["CHIM_NO_EXAMPLES"] = true;
//$GLOBALS["CHIM_DEBUG_LEVEL"] = 0;

//-------------------------------------------------
// Start metrics for this entry point 
require_once(__DIR__."/utils/metrics_util.php");
// $globalTimer = new MinAITimerScope('globals_php', 'MinAI');

require_once(__DIR__."/config.base.php");
require_once(__DIR__."/logger.php");
//require_once(__DIR__."/util .php");

$pluginPath = $GLOBALS["ENGINE_PATH"]."ext/minai_plugin";
if (!file_exists("$pluginPath/config.php")) {
    copy("$pluginPath/config.base.php", "$pluginPath/config.php");
}
require_once(__DIR__."/config.php");

//-------------------------------------------------
if ((isset($GLOBALS["action_prompts"]["normal_scene"])) &&
    (isset($GLOBALS["action_prompts"]["explicit_scene"]))) {
    if (!isset($GLOBALS["action_prompts_copy"])) {
        $GLOBALS["action_prompts_copy"] = $GLOBALS["action_prompts"];
        //error_log(" globals: making action_prompts copy ");
    }
}

//-------------------------------------------------

if (!isset($GLOBALS["current_party_members"])) {
    $GLOBALS["current_party_members"] = [ 'members' => [], 'names' => [] ];
}

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

if (!isset($all_fast_commands))
    $all_fast_commands = [];
if (!isset($fast_commands))
    $fast_commands = [];


//-------------------------------------------------

//error_log("-- globals -- ");
//Logger::warn($GLOBALS["HERIKA_NAME"]." ".$GLOBALS["ORIGINAL_HERIKA_NAME"]); // debug

$b_clean_narrator = ($GLOBALS["HIDE_NARRATOR_DIALOGUE"] ?? false) || ($GLOBALS["stop_narrator_context_leak"] ?? false);
$GLOBALS["HIDE_NARRATOR_DIALOGUE"] = $b_clean_narrator;
$GLOBALS["stop_narrator_context_leak"] = $b_clean_narrator;

$b_no_asterisk = ($GLOBALS['REMOVE_ASTERISKS_FROM_OUTPUT'] ?? true) || ($GLOBALS['strip_emotes_from_output'] ?? true) ;
$GLOBALS['strip_emotes_from_output'] = $b_no_asterisk;
$GLOBALS['REMOVE_ASTERISKS_FROM_OUTPUT'] = $b_no_asterisk;

/*
$GLOBALS["TTS_FALLBACK_FNCT"] = function($responseTextUnmooded, $mood, $responseText) {

    if (!isset($GLOBALS["db"]))
        $GLOBALS["db"] = new sql();
    require_once(__DIR__."/config.php");
    require_once(__DIR__."/util.php");
    
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
*/
