<?php
/**
 * Core Context Builders
 * 
 * This file contains the basic context builders for the system prompt
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../util.php");
require_once(__DIR__ . "/../../util_chim.php"); 
require_once(__DIR__ . "/../system_prompt_context.php");

/**
 * Initialize core context builders
 */
function InitializeCoreContextBuilders() {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Register personality context builder
    $registry->register('personality', [
        'section' => 'character',
        'header' => '#herika_name# core personality', //'Personality', // 'header' => 'PROFILE', ???
        'description' => 'Core personality description',
        'priority' => 5, // High priority - should be first in character section
        'enabled' => isset($GLOBALS['minai_context']['personality']) ? (bool)$GLOBALS['minai_context']['personality'] : true,
        'builder_callback' => 'BuildPersonalityContext'
    ]);
    
    // Register player background context builder
    $registry->register('player_background', [
        //'section' => 'interaction',
        'section' => 'character',
        'header' => 'Description of #player_name#',
        'description' => 'NPC perspective of the player',
        'priority' => 7,
        'enabled' => isset($GLOBALS['minai_context']['player_background']) ? (bool)$GLOBALS['minai_context']['player_background'] : true,
        'builder_callback' => 'BuildPlayerBackgroundContext'
    ]);

    // Register basic interaction context builder
    $registry->register('interaction', [
        'section' => 'interaction',
        'description' => 'Basic information about who the character is interacting with',
        'priority' => 10, // High priority - should be first in interaction section
        'enabled' => isset($GLOBALS['minai_context']['interaction']) ? (bool)$GLOBALS['minai_context']['interaction'] : true,
        'builder_callback' => 'BuildInteractionContext'
    ]);

    // Register dynamic state context builder
    $registry->register('dynamic_state', [
        'section' => 'character',
        'header' => 'Current State',
        'description' => 'Dynamic state information for the character',
        'priority' => 15, // Just after personality but before most other attributes
        'enabled' => isset($GLOBALS['minai_context']['dynamic_state']) ? (bool)$GLOBALS['minai_context']['dynamic_state'] : true,
        'builder_callback' => 'BuildDynamicStateContext'
    ]);
    
    // Register combat context builder
    $registry->register('combat', [
        'section' => 'interaction',
        'header' => 'Combat Status',
        'description' => 'Information about current combat situation',
        'priority' => 15, // High priority in interaction section
        'enabled' => isset($GLOBALS['minai_context']['combat']) ? (bool)$GLOBALS['minai_context']['combat'] : true,
        'builder_callback' => 'BuildCombatContext'
    ]);
    
    // Register current task context builder
    $registry->register('current_task', [
        'section' => 'interaction',
        'header' => 'Current Task',
        'description' => 'Information about the current task or objective',
        'priority' => 25,
        'enabled' => isset($GLOBALS['minai_context']['current_task']) ? (bool)$GLOBALS['minai_context']['current_task'] : true,
        'builder_callback' => 'BuildCurrentTaskContext'
    ]);
    
    // Oghma context builder
    $registry->register('oghma_infinium', [
        'section' => 'misc',
        'header' => 'Oghma Infinium Lore',
        'description' => 'Lore Information from the Oghma Infinium.',
        'priority' => 30,
        'enabled' => true,
        'builder_callback' => 'BuildOghmaInfiniumContext'
    ]);

    // Rumors context builder
    $registry->register('rumors_and_gossips', [
        'section' => 'misc',
        'header' => 'Rumors and gossips',
        'description' => 'Rumors and gossips.',
        'priority' => 35,
        'enabled' => true,
        'builder_callback' => 'BuildRumorsContext'
    ]);
    
}

//---------------------------------------    

function StrCleanBullets($s_input = ""){
    $s_res = strtr($s_input,[
        '** ' => ' ',
        '* ' => ' ',
    ]);
    return $s_res;
}


/**
 * Build the Oghma Infinium context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted Oghma Infinium context
 */
function BuildOghmaInfiniumContext($params) {
    
    if (isset($GLOBALS["OGHMA_HINT"]) && (!empty($GLOBALS["OGHMA_HINT"])) ) {
        //error_log("oghma minai: ". ($GLOBALS["OGHMA_HINT"] ?? "") . " - dbg");        
        return $GLOBALS["OGHMA_HINT"];
    } else 
        return "";
}

function BuildRumorsContext($params) {
    
    if (isset($GLOBALS["rumorsText"]) && (!empty($GLOBALS["rumorsText"])) ) {
        //error_log("oghma minai: ". ($GLOBALS["OGHMA_HINT"] ?? "") . " - dbg");        
        return $GLOBALS["rumorsText"];
    } else 
        return "";
}


/**
 * Build the personality context
 * 
 * @param array $params Parameters including herika_name, player_name, target, is_self_narrator
 * @return string Formatted personality context
 */
function BuildPersonalityContext($params) {
    $herika_name = $params['herika_name'];
    $is_self_narrator = isset($params['is_self_narrator']) ? $params['is_self_narrator'] : false;
    $player_name = $params['player_name'] ?? "";
    $target = $params["target"] ?? "";


    if ($herika_name == $target) {
        return "";
    }
    
    get_NPC_data($herika_name);
    if (isset($GLOBALS["minai_cache_npcdata"][$herika_name]))
        $npc_data = $GLOBALS["minai_cache_npcdata"][$herika_name];
    else 
        return "";
    
    // Get the personality from global variables

    $HRK_PERS 	     = $npc_data['core'] ?? "You are {$herika_name}. ";
    $HRK_BACKGROUND  = trim($npc_data['npc_static_bio'] ?? '');
    $HRK_PERSONALITY = trim($npc_data['personality'] ?? '');
    $HRK_SPEECHSTYLE = trim($npc_data['speechstyle'] ?? '');
    $HRK_APPEARANCE  = trim($npc_data['appearance'] ?? '');
    $HRK_SKILLS      = trim($npc_data['skills'] ?? '');
    $HRK_OCCUPATION  = trim($npc_data['occupation'] ?? '');
    $HRK_GOALS       = trim($npc_data['goals'] ?? '');

    $HRK_RELATIONSHIP = "";// trim(get_relationship($herika_name, $target));
    
    //$OGHMA_KNOWLEDGE_TAGS = $npc_data['oghma_knowledge_tags'] ?? '';

    $herika_pers = $HRK_PERS;
    //if (str_starts_with($herika_pers,'Roleplay as ')) {
    //    $herika_pers = substr($herika_pers, 11);
    //}
        
    if ($HRK_BACKGROUND > "") { // core_npc_master.npc_static_bio
        $herika_pers .= "\n\n<personality_core>\n## {$herika_name} personality core \n" . $HRK_BACKGROUND . "\n</personality_core>\n";
    }
    if ($HRK_PERSONALITY > "") { // core_npc_master.personality 
        $herika_pers .= "\n\n<personality_main_traits>\n## {$herika_name} personality main traits, behavioral patterns \n" . $HRK_PERSONALITY . "\n</personality_main_traits>\n";
    }
    if ($HRK_SPEECHSTYLE > "") { // core_npc_master.speechstyle
        $herika_pers .= "\n\n<speech_style>\n## {$herika_name} speech style \n" . $HRK_SPEECHSTYLE . "\n</speech_style>\n";
    }
    if ($HRK_APPEARANCE > "") { // core_npc_master.appearance
        $herika_pers .= "\n\n<personality_appearance>\n## {$herika_name} appearance \n" . $HRK_APPEARANCE . "\n</personality_appearance>\n";
    }
    if ($HRK_SKILLS > "") { // core_npc_master.skills
        $herika_pers .= "\n\n<personality_skills>\n## {$herika_name} skills \n" . StrCleanBullets($HRK_SKILLS) . "\n</personality_skills>\n";
    }
    if ($HRK_OCCUPATION > "") { // core_npc_master.occupation
        $herika_pers .= "\n\n<personality_occupation>\n## {$herika_name} occupation \n" . $HRK_OCCUPATION . "\n</personality_occupation>\n";
    }
    if ($HRK_GOALS > "") { // core_npc_master.goals
        $herika_pers .= "\n\n<personality_goals>\n## {$herika_name} goals\n" . StrCleanBullets($HRK_GOALS) . "\n</personality_goals>\n";
    }
    if ($HRK_RELATIONSHIP > "") { // former core_npc_master.relationships
        $herika_pers .= "\n\n<personality_relationships>\n## {$herika_name} relationships, social connections\n" . StrCleanBullets($HRK_RELATIONSHIP) . "\n</personality_relationships>\n";
    }
    // ------------------
    //HERIKA_DYNAMIC
    //if (isset($GLOBALS['HERIKA_DYNAMIC']) && (trim($GLOBALS['HERIKA_DYNAMIC']) > "")) { // HERIKA_DYNAMIC
    //    $herika_pers .= "\n\n<personality_dynamic>\n## Current updated personality\n" . StrCleanBullets(trim($GLOBALS['HERIKA_DYNAMIC'])) . 
    //    "\n</personality_dynamic>\n";
    
 //PHP Parse error:  Unclosed '{' on line 143 does not match ')' in /var/www/html/HerikaServer/ext/minai_plugin/contextbuilders/context_modules/core_context.php on line 198 [19:17:17 12.07.26] [error]   
    //}
    
    //$GLOBALS["dynamicBiography"] - $middle_term_memory middle_term_enabled
    $b_mtmemory = $GLOBALS["MIDDLE_TERM_MEMORY_ENABLED"] ?? false;
    if ($b_mtmemory && isset($GLOBALS['middle_term_memory']) && (trim($GLOBALS['middle_term_memory']) > "")) { // 
        $herika_pers .= "\n\n<middle_term_memory>\n## Middle term memory\n" . (trim($GLOBALS['middle_term_memory'])) . "\n</middle_term_memory>\n";
    }
    
    // ------------------
    if (isset($GLOBALS['HERIKA_SEX_PERSONALITY']) && (trim($GLOBALS['HERIKA_SEX_PERSONALITY']) > "")) { // addXPersonality
        $herika_pers .= "\n\n<personality_sexual_behavior>\n" . trim($GLOBALS['HERIKA_SEX_PERSONALITY']) . "\n</personality_sexual_behavior>\n";
    }
    // ------------------
    if (isset($GLOBALS["PROFILE_PROMPT"]) && (trim($GLOBALS['PROFILE_PROMPT']) > "")) { // core_profiles.prompt
        $herika_pers .= "\n\n<personality_group_details>\n## Group related details\n" . trim($GLOBALS["PROFILE_PROMPT"]) . "\n</personality_group_details>\n";
    }
    
    return trim($herika_pers);
}

/**
 * Build the combat context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted combat context
 */
function BuildCombatContext($params) {
    $target = $params["target"];
    $ret = "";
    
    // Add combat information if available
    $inCombat = GetActorValue($target, "inCombat");
    if ($inCombat === "true") {
        $ret .= "{$target} is currently engaged in battle!\n";
        
        // Add combat allies if any
        $allies = GetActorValue($target, "combatAllies", true);
        if (!empty($allies)) {
            $allies = explode('~', $allies);
            // Remove target and narrator from allies list (case insensitive)
            $allies = array_filter($allies, function($ally) use ($target) {
                return strcasecmp(trim($ally), trim($target)) !== 0 && strcasecmp(trim($ally), "The Narrator") !== 0;
            });

            if (!empty($allies)) {
                $ret .= "{$target} is fighting alongside: " . implode(', ', $allies) . "\n";
            }
            else {
                $ret .= "{$target} is fighting alone!\n";
            }
        }
        
        // Add combat targets if any
        $targets = GetActorValue($target, "combatTargets", true);
        if (!empty($targets)) {
            $targets = explode('~', $targets);
            // Remove narrator from targets list (case insensitive)
            $targets = array_filter($targets, function($t) {
                return strcasecmp(trim($t), "The Narrator") !== 0;
            });
            
            if (!empty($targets)) {
                $ret .= "{$target} is fighting against: " . implode(', ', $targets) . "\n";
            }
        }
    }
    
    return $ret;
}

/**
 * Build the basic interaction context
 * 
 * @param array $params Parameters including herika_name, player_name, target, is_self_narrator
 * @return string Formatted interaction context
 */
function BuildInteractionContext($params) {
    $herika_name = $params['herika_name'];
    // Only display this once.
    if ($GLOBALS["HERIKA_NAME"] != $herika_name) {
        return "";
    }
    $target = $params["target"];
    $is_self_narrator = isset($params['is_self_narrator']) ? $params['is_self_narrator'] : false;
    $player_name = isset($params['player_name']) ? $params['player_name'] : "";
    
    $ret = "";
    // Only check trespassing for player, follower, or narrator interactions
    if ($target === $player_name || $GLOBALS["HERIKA_NAME"] === "The Narrator" || IsFollower($target)) {
        if (IsEnabled($player_name, "isTrespassing")) {
            $ret .= "{$target} is currently trespassing in this location.\n";
        }
    }

    if ($is_self_narrator) {
        $ret .= "You are {$player_name}'s inner voice, providing thoughts, perspective, and advice directly to them.";
    }
    else {
        $ret .= "{$herika_name} currently interacting with {$target}."; // could be 2 NPCs interacting
        if (function_exists('DataRetrieveLastTimeTalk')) {
            $s_last_talk = DataRetrieveLastTimeTalk($herika_name, $target);
            if ($s_last_talk > "")
                $ret .= "\n{$s_last_talk}";
        }
    }

    return $ret;
}

/**
 * Build the player achievements
 * 
 * @player_name
 * @return string Formatted player achievements
 */
function BuildPlayerAchievementsContext($playername) {
    $s_res = "";
    $b_titles = boolval($GLOBALS['minai_context']['player_titles'] ?? false);
    if ($b_titles && (strlen(trim($playername)) > 0)) { 
        
        //--- member of -----------------------------------------------
        $s_fac = "";
        // The Circle
        $bx = IsInFaction($playername, "The Circle");
        if ($bx) 
            $s_fac .= "- member of the inner circle of The Companions, Lycanthropy is mandatory condition for membership\n";
        else {
            // The Companions
            if (IsInFaction($playername, "The Companions"))
                $s_fac .= "- member of The Companions\n";
        }
        // Nightingales
        $bx = IsInFaction($playername, "Nightingales");
        if ($bx) 
            $s_fac .= "- member of the Nightingale Trinity (higher echelon of the Thieves Guild, dedicated to the service of Nocturnal)\n";
        else {
            //Thieves' Guild
            if (IsInFaction($playername, "Thieves' Guild"))
                $s_fac .= "- skilled thief, member of Thieves' Guild\n";
        }
        
        // College of Winterhold - Arch-Mage, also known as Archmagus or Archmagister, the leader of the Mages Guild known as College of Winterhold. 
        $bx = IsInFaction($playername, "College of Winterhold Arch-Mage Faction");
        if ($bx) 
            $s_fac .= "- Arch-Mage, the leader of the Mages Guild known as College of Winterhold\n";
        else {
            if (IsInFaction($playername, "College of Winterhold"))
                $s_fac .= "- mage, member of College of Winterhold\n";
        }
        
        // Greybeards
        if (IsInFaction($playername, "Greybeards"))
            $s_fac .= "- recognized as The Dragonborn, member of Greybeards\n";

        // Bards College
        if (IsInFaction($playername, "Bards College"))
            $s_fac .= "- presumed (debatable) skilled bard, member of Bards College\n";

        // Blood-Kin of the Orcs
        if (IsInFaction($playername, "Blood-Kin of the Orcs"))
            $s_fac .= "- Blood-Kin of the Orcs, unlimited access to orc settlements\n";

        // The Dawnguard
        if (IsInFaction($playername, "The Dawnguard"))
            $s_fac .= "- vampire hunter, member of The Dawnguard\n";

        // Thirsk Hall Riekling Tribe
        if (IsInFaction($playername, "Thirsk Hall Riekling Tribe"))
            $s_fac .= "- chief of Thirsk Hall Riekling Tribe\n";

        // Dark Brotherhood
        if (IsInFaction($playername, "Dark Brotherhood"))
            $s_fac .= "- professional assassin, member of the Dark Brotherhood\n";

        // Tribunal Temple
        if (IsInFaction($playername, "Tribunal Temple"))
            $s_fac .= "- member of Tribunal Temple (heretical Dunmeri faction devoted to worship of the Tribunal, the former living gods Almalexia, Sotha Sil, and Vivec)\n";

        // Imperial Legion
        if (IsInFaction($playername, "Imperial Legion"))
            $s_fac .= "- member of Imperial Legion\n";
        
        // Stormcloaks
        if (IsInFaction($playername, "Stormcloaks"))
            $s_fac .= "- member of Stormcloaks\n";
        
        // Volkihar Vampire Clan
        if (IsInFaction($playername, "Volkihar Vampire Clan"))
            $s_fac .= "- vampire, member of Volkihar Vampire Clan\n";
        
        // Blades
        if (IsInFaction($playername, "Blades"))
            $s_fac .= "- member of the Blades\n";

        // Vigilant of Stendarr For Player
        if (IsInFaction($playername, "Vigilant of Stendarr For Player"))
            $s_fac .= "- member of Vigilant of Stendarr\n";

        // Riften Fishery Faction
        if (IsInFaction($playername, "Riften Fishery Faction"))
            $s_fac .= "- exceptional fisherman, member of Riften Fishery Guild\n";

        // Coven of Namira
        if (IsInFaction($playername, "Coven of Namira"))
            $s_fac .= "- cannibal, member of Coven of Namira\n";

        if (strlen($s_fac) > 0) {
            $s_fac = "\n### {$playername}'s affiliations: \n" . $s_fac . "\n";
        }

        //--- champion of ---------------------------------------------
        $s_champ = "";
        $s_keywords = trim(GetActorValue($playername, "champion_achievement", true));
        if (!empty($s_keywords)) {
            $keywords = explode("~", $s_keywords);
            $keywords = array_filter($keywords); // Remove empty entries
            $nk = count($keywords);
            if ($nk > 0) {
                for ($k = 0; $k < $nk; $k++) {
                    $s_k = $keywords[$k];
                    $s_champ .= "- Champion of {$s_k}\n";
                }                
            }
            if (strlen($s_champ) > 0) 
                $s_champ = "\n### {$playername}'s daedric deeds: \n" . $s_champ . "\n";
        }
        
        //--- agent of ------------------------------------------------
        $s_agent = ""; 
        $s_keywords = trim(GetActorValue($playername, "agent_achievement", true));
        if (!empty($s_keywords)) {
            $keywords = explode("~", $s_keywords);
            $keywords = array_filter($keywords); // Remove empty entries
            $nk = count($keywords);
            if ($nk > 0) {
                for ($k = 0; $k < $nk; $k++) {
                    $s_k = $keywords[$k];
                    if ($s_k == 'Dibella') 
                        $s_k = $s_k . ' - when he talks to them, women feel an irresistible attraction.';
                    $s_agent .= "- Agent of {$s_k}\n";
                }                
            }
            if (strlen($s_agent) > 0) 
                $s_agent = "\n### {$playername}'s divine blessings: \n" . $s_agent . "\n";
        }
        
        //--- thane of ------------------------------------------------
        $s_thane = "";
        // Get thane keywords and format them
        $s_keywords = trim(GetActorValue($playername, "thane_achievement", true));
        if (!empty($s_keywords)) {
            $keywords = explode("~", $s_keywords);
            $keywords = array_filter($keywords); // Remove empty entries
            $nk = count($keywords);
            if ($nk > 0) {
                for ($k = 0; $k < $nk; $k++) {
                    $s_hold = $keywords[$k];
                    $sl_hold = strtolower($s_hold);
                    if ($sl_hold == 'whiterun')  //Whiterun and Lydia was assigned as his housecarl
                        $s_thane .= "- Thane of {$s_hold} with Lydia as housecarl.\n";
                    elseif (($sl_hold == 'rift') ||($sl_hold == 'the rift'))  
                        $s_thane .= "- Thane of {$s_hold} with Iona as housecarl.\n";
                    elseif ($sl_hold == 'hjaalmarch')  
                        $s_thane .= "- Thane of {$s_hold} with Valdimar as housecarl.\n"; //Thane of Hjaalmarch	Valdimar
                    elseif ($sl_hold == 'eastmarch')  
                        $s_thane .= "- Thane of {$s_hold} with Calder as housecarl.\n"; // Thane of Eastmarch	- Calder
                    elseif ($sl_hold == 'falkreath')  
                        $s_thane .= "- Thane of {$s_hold} with Rayya as housecarl.\n"; // Thane of Falkreath	- Rayya
                    elseif ($sl_hold == 'haafingar')  
                        $s_thane .= "- Thane of {$s_hold} with Jordis the Sword-Maiden as housecarl.\n"; // Thane of Haafingar	- Jordis the Sword-Maiden
                    elseif (($sl_hold == 'pale') || ($sl_hold == 'the pale'))  
                        $s_thane .= "- Thane of {$s_hold} with Gregor as housecarl.\n"; // Thane of the Pale - Gregor
                    elseif (($sl_hold == 'reach') || ($sl_hold == 'the reach'))  
                        $s_thane .= "- Thane of {$s_hold} with Argis the Bulwark as housecarl.\n"; // Thane of the Reach - Argis the Bulwark
                    else 
                        $s_thane .= "- Thane of {$s_hold}\n";
                }                
            }
            if (strlen($s_thane) > 0) 
                $s_thane = "\n### {$playername}'s recognition and prestige: \n" . $s_thane . "\n";
        }

        //-------------------------------------------------------------
        if (strlen($s_thane) > 0) {
            $s_res .= "\n\n".$s_thane;
        }
        if (strlen($s_fac) > 0) {
            $s_res .= $s_fac;
        }
        if (strlen($s_champ) > 0) {
            $s_res .= $s_champ;
        }
        if (strlen($s_agent) > 0) {
            $s_res .= $s_agent;
        }
    }
    //error_log("achievements: $s_res ");
    return $s_res;
}

/**
 * Build the player background context
 * 
 * @param array $params Parameters including herika_name, player_name, target, is_self_narrator
 * @return string Formatted player background context
 */
function BuildPlayerBackgroundContext($params) {
    $player_name = $params['player_name'];
    $herika_name = $params['herika_name'];
    $is_self_narrator = isset($params['is_self_narrator']) ? $params['is_self_narrator'] : false;
    
    // Include player background if interacting with the player or in self_narrator mode
    if ($herika_name != $player_name && $GLOBALS["HERIKA_NAME"] != "The Narrator") {
        return "";
    }
    
    // Get player bio from global variables
    $player_bio = isset($GLOBALS["PLAYER_BIOS"]) ? $GLOBALS["PLAYER_BIOS"] : "";
    $player_bio = str_replace("#PLAYER_NAME#", $player_name, $player_bio);
    if (empty($player_bio)) {
        if ($is_self_narrator) {
            return "You are the embodiment of {$player_name}'s thoughts, representing their subconscious perspective of the world around them.";
        }
        return "";
    }
    
    // Add additional context for self_narrator mode
    if ($is_self_narrator) {
        return "As {$player_name}'s inner voice, you understand the following about them:\n\n" . trim($player_bio);
    }
    $player_bio = $player_bio . BuildPlayerAchievementsContext($player_name);

    return trim($player_bio);
}

/**
 * Build the dynamic state context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted dynamic state context
 */
function BuildDynamicStateContext($params) {
    $herika_name = $params['herika_name'];
    $target = isset($params["target"]) ? $params["target"] : "";
    
    // Only show dynamic state for the character speaking
    if ($herika_name == $target) {
        return "";
    }
    
    // Get dynamic state from global variables
    $dynamic_state = ($GLOBALS["HERIKA_DYNAMIC"] ?? "" );
    // Replace "The Narrator" with player name if in self-narrator mode
    if (isset($params['is_self_narrator']) && $params['is_self_narrator']) {
        $player_name = $params['player_name'];
        $dynamic_state = str_replace("The Narrator", $player_name, $dynamic_state);
    }
    
    if (empty($dynamic_state)) {
        return "";
    }
    // Strip out any excluded dynamic state entries
    $exclusions = [
        "Updated Character Profile",
        "Updated Character Sheet",
        "Updated Character Sheet"
    ];
    
    // Split into lines and filter out any containing excluded phrases
    $lines = explode("\n", $dynamic_state);
    $filtered_lines = array_filter($lines, function($line) use ($exclusions) {
        foreach ($exclusions as $exclude) {
            if (stripos($line, $exclude) !== false) {
                return false;
            }
        }
        return true;
    });
    $dynamic_state = implode("\n", $filtered_lines);
    return trim($dynamic_state);
}

/**
 * Build the current task context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted current task context
 */
function BuildCurrentTaskContext($params) {
    $herika_name = $params['herika_name'];
    $target = isset($params["target"]) ? $params["target"] : "";
    // Only show current task for the character speaking
    if ($herika_name == $target) {
        return "";
    }
    $current_task = null;
    if (isset($GLOBALS["CURRENT_TASK"]) && $GLOBALS["CURRENT_TASK"]) {
        if (IsFollower($herika_name) || $GLOBALS["HERIKA_NAME"]=="The Narrator") {
            $current_task=DataGetCurrentTask();
            if (empty($current_task)) {
                $current_task="No active quests right now.";
            }
            if (!is_array($current_task)) {
                $current_task = explode(".", $current_task);
            }
            $current_task = array_map('trim', $current_task);
        }
    }
    
    if (!$current_task) {
        return "";
    }
    
    return implode("\n", $current_task);
}