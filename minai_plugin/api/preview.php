<?php
// Prevent any output before JSON
ob_start();
require_once("../config.base.php");
require_once("../logger.php");
header('Content-Type: application/json');
$path = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
//require_once($path . "conf".DIRECTORY_SEPARATOR."conf_.php");

require_once("/var/www/html/HerikaServer/lib/postgresql.class.php"); 

$GLOBALS["db"] = new sql();

//save_original_herika_name();
$b_already_saved = (isset($GLOBALS["herika_name_backup"]) && (strlen($GLOBALS["herika_name_backup"]) > 0));
if (!$b_already_saved) {
    $herika = ($GLOBALS["HERIKA_NAME"] ?? '');
    if (strlen($herika) > 0) {
        if (($herika !== "The Narrator") && 
            ($herika !== "Player") && 
            ($herika !== "LLMFallback") && 
            (stripos($herika, "Narrator") === false) && 
            (stripos($herika, "actor") === false) && 
            (stripos($herika, "everyone") === false) && 
            (stripos($herika, "*") === false) && 
            (stripos($herika, "none") === false) ) {
                
            $GLOBALS["herika_name_backup"] = $herika;
            //error_log("[util] npc=$herika SAVED - exec trace" ); // debug
        }
    }
}
$GLOBALS["HERIKA_NAME"] = "The Narrator";

// Load required files
require_once("../util.php");
require_once("../contextbuilders.php");
require_once("../roleplaybuilder.php");
require_once("../utils/init_common_variables.php");

//error_log("[init_common_variables] target=".$GLOBALS["target"]." target_gender=".$GLOBALS["target_gender"]." HERIKA_NAME=".$GLOBALS["HERIKA_NAME"]." herika_gender=".$GLOBALS["herika_gender"]." ".__FILE__); //debug


// Set narrator name and load profile if needed

SetNarratorProfile();
try {
    // Get player name from query param or use default
    $playerName = $_GET['player'] ?? ($GLOBALS["PLAYER_NAME"] ?? "Player");
    
    // Get actor name from query param or use default
    $actorName = $_GET['actor'] ?? "";
    if (!strlen(trim($actorName)) > 0)
        $actorName = GetOnePartyMember(0, "Herika");
    else {
        $profilePath = GetActorConfigPath($actorName);
        if (!file_exists($profilePath)) {
            $actorName = GetOnePartyMember(0, "Herika");
        }
    }
    
    // Get second NPC name for NPC-to-NPC interaction
    $secondNpcName = $_GET['secondnpc'] ?? "";
    if (!strlen(trim($secondNpcName)) > 0) 
        $secondNpcName = GetOnePartyMember(1, "Inigo");
    else {
        $profilePath2 = GetActorConfigPath($secondNpcName);
        if (!file_exists($profilePath2)) {
            $secondNpcName = GetOnePartyMember(1, "Inigo");
        }
    }

    //error_log("preview: $actorName $secondNpcName");

    // Get player pronouns
    $playerPronouns = GetActorPronouns($playerName);

    // Get contexts and convert to first person
    $params = ['player_name' => $playerName, 'herika_name' => 'The Narrator'];
    $physDesc = convertToFirstPerson(callContextBuilder('physical_description', $params), $playerName, $playerPronouns);
    $arousalStatus = convertToFirstPerson(callContextBuilder('arousal', $params), $playerName, $playerPronouns);
    $survivalStatus = convertToFirstPerson(callContextBuilder('survival', $params), $playerName, $playerPronouns);
    $clothingStatus = convertToFirstPerson(GetUnifiedEquipmentContext($playerName), $playerName, $playerPronouns);
    $fertilityStatus = convertToFirstPerson(callContextBuilder('fertility', $params), $playerName, $playerPronouns);
    $tattooStatus = convertToFirstPerson(callContextBuilder('tattoos', $params), $playerName, $playerPronouns);
    // Add bounty context
    $bountyStatus = convertToFirstPerson(callContextBuilder('bounty', $params), $playerName, $playerPronouns);

    // Get nearby actors and locations
    $nearbyActors = array_filter(array_map('trim', explode('|', DataBeingsInRange2())));
    $possibleLocations = DataPosibleLocationsToGo();

    // Get recent context using configured value
    $contextMessages = $GLOBALS['roleplay_settings']['context_messages'];
    $contextDataHistoric = GetRecentContext("", $contextMessages) ?? [];
    $contextDataWorld = DataLastInfoFor("", -2) ?? [];
    $contextDataFull = array_merge($contextDataWorld, $contextDataHistoric);
    $mindState = convertToFirstPerson(callContextBuilder('mind_influence', $params), $playerName, $playerPronouns);
    $relationshipStatus = convertRelationshipStatus($actorName);
    $vitals = convertToFirstPerson(callContextBuilder('vitals', $params), $playerName, $playerPronouns);
    // Build the variable replacements as they would appear in the prompt
    $variableReplacements = [
        'PLAYER_NAME' => $playerName,
        'PLAYER_BIOS' => replaceVariables($GLOBALS["PLAYER_BIOS"] ?? "", ['PLAYER_NAME' => $playerName]),
        'NEARBY_ACTORS' => implode(", ", $nearbyActors),
        'NEARBY_LOCATIONS' => implode(", ", $possibleLocations),
        'RECENT_EVENTS' => implode("\n", array_map(function($ctx) { 
            return $ctx['content']; 
        }, array_slice($contextDataFull, -$GLOBALS['roleplay_settings']['context_messages']))),
        'PLAYER_SUBJECT' => $playerPronouns['subject'],
        'PLAYER_OBJECT' => $playerPronouns['object'],
        'PLAYER_POSSESSIVE' => $playerPronouns['possessive'],
        'HERIKA_DYNAMIC' => $GLOBALS["HERIKA_DYNAMIC"] ?? "",
        'PHYSICAL_DESCRIPTION' => $physDesc,
        'AROUSAL_STATUS' => $arousalStatus,
        'SURVIVAL_STATUS' => $survivalStatus,
        'CLOTHING_STATUS' => $clothingStatus,
        'FERTILITY_STATUS' => $fertilityStatus,
        'TATTOO_STATUS' => $tattooStatus,
        'BOUNTY_STATUS' => $bountyStatus,
        'HERIKA_PERS' => $GLOBALS["HERIKA_PERS"] ?? "",
        'MIND_STATE' => $mindState,
        'RELATIONSHIP_STATUS' => $relationshipStatus,
        'DEVICE_STATUS' => '',
        'VITALS' => $vitals
    ];

    // Get sections from roleplay settings
    $sections = $GLOBALS['roleplay_settings']['sections'];
    
    // Get the complete system prompt (which respects minai_context settings)
    // First perspective: Narrator (current settings)
    $systemPromptData = BuildSystemPrompt();
    $narratorSystemPrompt = $systemPromptData['content'] ?? "Error: Unable to generate system prompt";
    
    // Second perspective: Actor (using specified name)
    // Store original values to restore later
    $originalHerika = $GLOBALS["HERIKA_NAME"];
    $originalTarget = $GLOBALS["target"] ?? "Player";
    
    // Set values for actor perspective
    global $HERIKA_NAME;
    global $HERIKA_PERS;
    // this will be overridden by the actor profile
    $GLOBALS["HERIKA_NAME"] = "Could not load actor profile";
    $profilePath = GetActorConfigPath($actorName);
    $includeSuccess = include($profilePath);
    $GLOBALS["target"] = $GLOBALS["PLAYER_NAME"];
    
    // Reset context builder registry to ensure a clean state
    ContextBuilderRegistry::resetInstance();
    InitializeContextBuilders();
    
    // Generate system prompt from actor perspective
    if ($includeSuccess) {
        $actorSystemPromptData = BuildSystemPrompt();
        $actorSystemPrompt = $actorSystemPromptData['content'] ?? "Error: Unable to generate system prompt";
    } else {
        $actorSystemPrompt = "Error: Unable to load actor profile";
    }

    // Third perspective: NPC-to-NPC interaction   
    // Set values for NPC-to-NPC perspective
    $GLOBALS["target"] = $secondNpcName; // Set target to second NPC
    
    // Reset context builder registry to ensure a clean state
    ContextBuilderRegistry::resetInstance();
    InitializeContextBuilders();
    
    // Generate system prompt for NPC-to-NPC interaction
    if ($includeSuccess) {
        $npcToNpcSystemPromptData = BuildSystemPrompt();
        $npcToNpcSystemPrompt = $npcToNpcSystemPromptData['content'] ?? "Error: Unable to generate system prompt";
    } else {
        $npcToNpcSystemPrompt = "Error: Unable to load actor profile";
    }
    
    // Restore original values
    $GLOBALS["HERIKA_NAME"] = $originalHerika;
    $GLOBALS["target"] = $originalTarget;

    // Build the preview data
    $preview = [
        'variables' => $variableReplacements,
        'sections' => array_map(function($section) use ($variableReplacements) {
            $content = replaceVariables($section['content'], $variableReplacements);
            return [
                'header' => $section['header'],
                'content' => $content,
                'enabled' => $section['enabled']
            ];
        }, $sections),
        'prompts' => [
            'system_prompt' => replaceVariables($GLOBALS['roleplay_settings']['system_prompt'], $variableReplacements),
            'system_prompt_explicit' => replaceVariables($GLOBALS['roleplay_settings']['system_prompt_explicit'], $variableReplacements),
            'system_prompt_combat' => replaceVariables($GLOBALS['roleplay_settings']['system_prompt_combat'], $variableReplacements),
            'roleplay_system_prompt' => replaceVariables($GLOBALS['roleplay_settings']['roleplay_system_prompt'], $variableReplacements),
            'roleplay_system_prompt_explicit' => replaceVariables($GLOBALS['roleplay_settings']['roleplay_system_prompt_explicit'], $variableReplacements),
            'roleplay_system_prompt_combat' => replaceVariables($GLOBALS['roleplay_settings']['roleplay_system_prompt_combat'], $variableReplacements),
            'translation_request' => replaceVariables($GLOBALS['roleplay_settings']['translation_request'], $variableReplacements),
            'translation_request_explicit' => replaceVariables($GLOBALS['roleplay_settings']['translation_request_explicit'], $variableReplacements),
            'translation_request_combat' => replaceVariables($GLOBALS['roleplay_settings']['translation_request_combat'], $variableReplacements),
            'roleplay_request' => replaceVariables($GLOBALS['roleplay_settings']['roleplay_request'], $variableReplacements),
            'roleplay_request_explicit' => replaceVariables($GLOBALS['roleplay_settings']['roleplay_request_explicit'], $variableReplacements),
            'roleplay_request_combat' => replaceVariables($GLOBALS['roleplay_settings']['roleplay_request_combat'], $variableReplacements)
        ],
        'narrator_system_prompt' => $narratorSystemPrompt,
        'actor_system_prompt' => $actorSystemPrompt,
        'actor_name' => $actorName,
        'npc_to_npc_system_prompt' => $npcToNpcSystemPrompt,
        'second_npc_name' => $secondNpcName
    ];

    // Clear any previous output
    ob_clean();
    
    echo json_encode($preview, 
        JSON_PRETTY_PRINT | 
        JSON_UNESCAPED_UNICODE | 
        JSON_UNESCAPED_SLASHES | 
        JSON_INVALID_UTF8_SUBSTITUTE
    );
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 