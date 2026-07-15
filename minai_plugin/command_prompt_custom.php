<?php
// Avoid processing for fast / storage events

/*
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
    return;
}

require_once("/var/www/html/HerikaServer/ext/minai_plugin/util.php"); 
require_once("/var/www/html/HerikaServer/ext/minai_plugin/utils/sex_utils.php");

$GLOBALS["target"] = GetTargetActor();
$GLOBALS["target_gender"] = GetGender($GLOBALS["target"]); //Is Female($GLOBALS["target"]) ? "female" : "male";
$GLOBALS["target_pronouns"] = GetActorPronouns($GLOBALS["target"]);
$target = $GLOBALS["target"];
*/

if ((!isset($GLOBALS["action_prompts"]["normal_scene"])) ||
    (!isset($GLOBALS["action_prompts"]["explicit_scene"])) ||
    (empty($GLOBALS["action_prompts"]))) {
    $GLOBALS["action_prompts"] = $GLOBALS["action_prompts_copy"];    
    error_log("WARNING - command_prompt_custom: CHIM made an attempt to disable MinAI action_prompts! ");
}

$b_sex = IsSexActiveSpeaker();

if ($b_sex) { // speaker should be in scene to use explicit prompt, otherwise a spectator would answer like a participant
    $GLOBALS["MINAI_ACTION_PROMPT"] = ($GLOBALS["action_prompts"]["explicit_scene"] ?? ' explicit scene ');
} else {
    $GLOBALS["MINAI_ACTION_PROMPT"] = ($GLOBALS["action_prompts"]["normal_scene"] ?? ' normal scene ');
}

if (IsEnabled($GLOBALS["PLAYER_NAME"], "isSinging")) {
    $GLOBALS["MINAI_ACTION_PROMPT"] = ($GLOBALS["action_prompts"]["singing"] ?? ' singing ');
} elseif (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    $mindState = GetMindInfluenceState($GLOBALS["PLAYER_NAME"]);
    $mindPrompt = GetMindInfluencePrompt($mindState, IsExplicitScene() ? "explicit" : (IsEnabled($GLOBALS["PLAYER_NAME"], "inCombat") ? "combat" : "default"));
    
    if (IsSexSceneDetected()) {
        $GLOBALS["MINAI_ACTION_PROMPT"] = ($GLOBALS["action_prompts"]["self_narrator_explicit"]);
        if ($mindPrompt) {
            $GLOBALS["MINAI_ACTION_PROMPT"] .= " " . $mindPrompt;
        }
    } else {
        $GLOBALS["MINAI_ACTION_PROMPT"] = ($GLOBALS["action_prompts"]["self_narrator_normal"]);
        if (IsEnabled($GLOBALS["PLAYER_NAME"], "inCombat")) {
            $GLOBALS["MINAI_ACTION_PROMPT"] .= " {$GLOBALS["PLAYER_NAME"]} is currently in combat. You MUST factor this into your response.";
        }
        if ($mindPrompt) {
            $GLOBALS["MINAI_ACTION_PROMPT"] .= " " . $mindPrompt;
        }
    }
} 

$GLOBALS["MINAI_ACTION_PROMPT"] = ExpandPromptVariables($GLOBALS["MINAI_ACTION_PROMPT"]);

if (isset($GLOBALS["enforce_short_responses"]) && $GLOBALS["enforce_short_responses"]) {
    $GLOBALS["MINAI_ACTION_PROMPT"] .= "\n<speech_style><important_rule>
- You MUST respond with no more than three sentences, preferably two, and no more than 40 words. Be concise.</important_rule></speech_style> ";
} /* else {
    $GLOBALS["MINAI_ACTION_PROMPT"] .= "\n<speech_style><important_rule>
- You MUST respond with no more than five sentences and no more than 75 words. 
- Avoid pink prose.</important_rule></speech_style> ";
} */

if (isset($GLOBALS["enforce_single_json"]) && $GLOBALS["enforce_single_json"]) {
    $GLOBALS["MINAI_ACTION_PROMPT"] .= " \nImportant: Provide only ONE single JSON response object per interaction. If multiple actions are desired, choose the most immediately relevant/important action and save additional actions for subsequent interactions. The response must be a single valid JSON object containing the character's next action or dialogue. ";
}

//error_log("[command_promp_custom] ". $GLOBALS["MINAI_ACTION_PROMPT"] . __FILE__." ".__LINE__); // debug
