<?php

if (!isset($GLOBALS["ENGINE_PATH"])) {
    $GLOBALS["ENGINE_PATH"] = $_SERVER['DOCUMENT_ROOT'].'/HerikaServer/'; // DOCUMENT_ROOT = /var/www/html
    //$GLOBALS["ENGINE_PATH"] = '/var/www/html/HerikaServer/';
    error_log("[globals] ENGINE_PATH=".$GLOBALS["ENGINE_PATH"]." ".__FILE__." ".__LINE__); // debug
}

if (!isset($GLOBALS["ENGINE_ROOT"])) {
    $GLOBALS["ENGINE_ROOT"] = $GLOBALS["ENGINE_PATH"];
}

if (!isset($GLOBALS["ENGINE_MINAI_PLUGIN_PATH"])) {
    $GLOBALS["ENGINE_MINAI_PLUGIN_PATH"] = $GLOBALS["ENGINE_PATH"]."ext/minai_plugin/";;
}

$enginePath = $GLOBALS["ENGINE_PATH"];
$pluginPath = $GLOBALS["ENGINE_PATH"]."ext/minai_plugin/";

//require_once($enginePath . "conf/conf_.php");
require_once($enginePath . "lib/logger.php");
require_once("/var/www/html/HerikaServer/lib/postgresql.class.php"); 
//require_once($enginePath."lib/l ogx.php"); // debug

require_once($enginePath . "lib/core/api_badge.class.php");
require_once($enginePath . "lib/core/tts_connector.class.php");
require_once($enginePath . "lib/core/player.class.php");
require_once($enginePath . "lib/core/npc_master.class.php");
require_once($enginePath . "lib/core/narrator.class.php");
require_once($enginePath . "lib/relationship_manager.php");

//if (!isset($GLOBALS["db"])) $GLOBALS["db"] = new sql();

function get_relationship($npcName, $targetName) {

    if (($npcName == $targetName) || ($npcName == 'The Narrator') || ($targetName == 'The Narrator'))
        return "";

    if ($targetName == $GLOBALS["PLAYER_NAME"])
        $sRes = RelationshipManager::getPlayerRelationship($npcName) ?? '';
    else
        $sRes = RelationshipManager::getRelationship($npcName, $targetName) ?? '';

} 


/**
     * Get relationship with Player specifically
     * Convenience method for common use case
    public static function getPlayerRelationship($npcName) {
        return self::getRelationship($npcName, 'Player');
    }


     */
    
function get_player_data(){

    if (!class_exists('Player')) {
        error_log("[get_player_data] player class not defined!"); 
        return -1;
    }
    $b_cached = false; 
    $b_ok = false;
    try {
    
        if (isset($GLOBALS["minai_cache_playerdata"])) {
            $allPlayerData = $GLOBALS["minai_cache_playerdata"];
            $player_name_chk = $allPlayerData["player_name"] ?? '-empty-';
            $b_cached = ($player_name_chk == $GLOBALS["PLAYER_NAME"]);
            $b_ok = $b_cached;
        }
        
        if (!$b_cached) {
            $obj_player = new Player();
            $allPlayerData = $obj_player->getAll();
            $player_name_chk = $allPlayerData["player_name"] ?? '-empty-';

            $b_ok = ($player_name_chk == $GLOBALS["PLAYER_NAME"]);
            if ($b_ok)
                $GLOBALS["minai_cache_npcdata"] = $allPlayerData;
        }
        
        if ($b_ok) {
            //$playerName = $allPlayerData['player_name'] ?? 'Unknown';
            $GLOBALS["PLAYER_APPEARANCE"] = $allPlayerData['appearance'] ?? '';
            $GLOBALS["PLAYER_SPEECH_STYLE"] = $allPlayerData['speech_style'] ?? '';
            $GLOBALS["PLAYER_BIOS"] = $allPlayerData['bio'] ?? '';
            //$bioKnownByAll = ($allPlayerData['bio_known_by_all'] ?? 'false') === 'true';
        } else {
           error_log("[get_player_data] Name mismatch! player_name_chk={$player_name_chk} / ".$GLOBALS["PLAYER_NAME"]." ".__FILE__);
           return -1;
        }
         
        //unset($obj_player);
        //unset($allPlayerData);
    } catch (Exception $e) {
        error_log("[get_player_data] Could not load player data from core_player: " . $e->getMessage());
    }
    
    return 1;
}

function get_narrator_data() {

    if (!class_exists('Narrator')) {
        error_log("[get_narrator_data] Narrator class not defined!"); 
        return -1;
    }

    $b_cached = false; 
    $b_ok = false;
    $npcName = 'The Narrator';
    
    try {

        if (isset($GLOBALS["minai_cache_npcdata"][$npcName])) {
            $npc_data = $GLOBALS["minai_cache_npcdata"][$npcName];
            $npc_name_chk = $npc_data["npc_name"] ?? '-empty-';
            $b_cached = ($npc_name_chk == $npcName);
            $b_ok = $b_cached;
        }

        if (!$b_cached) {
            //$narrator = new Narrator();
            //$narratorData = $narrator->getNarratorData();
            $obj_npc = new Narrator();
            $npc_data = $obj_npc->getNarratorData();
            
            $npc_name_chk = $npc_data["npc_name"] ?? '-empty-';
            $b_ok = ($npc_name_chk == $npcName);
            if ($b_ok)
                $GLOBALS["minai_cache_npcdata"][$npcName] = $npc_data;
        }

        if ($b_ok) {
            if ($npc_data) {
                Logger::info("[get_narrator_data] {$npcName} data loaded!");
            } else {
               error_log("[get_narrator_data] Could not load {$npcName} data!".__FILE__);
               return -1;
            }
        } else {
           error_log("[get_narrator_data] Name mismatch! npcName={$npcName} npc_name_chk={$npc_name_chk} ".__FILE__);
           return -1;
        }
    } catch (Exception $e) {
        error_log("Could not load player data from core_player: " . $e->getMessage());
    }

    return 1;
}

function get_NPC_data($npcName){
	// cache npc data "minai_cache_npcdata"
    if (strlen(trim($npcName)) < 1) {
        error_log("[get_NPC_data] Empty name! ( {$npcName} ) "); 
        return -1;
    }
    
    if (!class_exists('NpcMaster')) {
        error_log("[get_NPC_data] NpcMaster class not defined!"); 
        return -1;
    }

    if ($npcName == 'The Narrator') {
        return get_narrator_data();
    }

    $b_cached = false; 
    $b_ok = false;
    
    try {

        if (isset($GLOBALS["minai_cache_npcdata"][$npcName])) {
            $npc_data = $GLOBALS["minai_cache_npcdata"][$npcName];
            $npc_name_chk = $npc_data["npc_name"] ?? '-empty-';
            $b_cached = ($npc_name_chk == $npcName);
            $b_ok = $b_cached;
        }

        if (!$b_cached) {
        
            $obj_npc = new NpcMaster();
            $npc_data = $obj_npc->getByName($npcName);
            $npc_name_chk = $npc_data["npc_name"] ?? '-empty-';
            $b_ok = ($npc_name_chk == $npcName);
            if ($b_ok)
                $GLOBALS["minai_cache_npcdata"][$npcName] = $npc_data;
        }

        if ($b_ok) {
            if ($npc_data) {
                //$npc_name_chk = $npc_data["npc_name"];
                /*
                $GLOBALS['HERIKA_BACKGROUND']  = $npc_data['npc_static_bio'] ?? '';
                $GLOBALS['OGHMA_KNOWLEDGE']    = $npc_data['oghma_knowledge_tags'] ?? '';
                $GLOBALS['HERIKA_PERSONALITY'] = $npc_data['personality'] ?? '';
                $GLOBALS['HERIKA_OCCUPATION']  = $npc_data['occupation'] ?? '';
                $GLOBALS['HERIKA_APPEARANCE']  = $npc_data['appearance'] ?? '';
                $GLOBALS['HERIKA_SKILLS']      = $npc_data['skills'] ?? '';
                $GLOBALS['HERIKA_SPEECHSTYLE'] = $npc_data['speechstyle'] ?? '';
                $GLOBALS['HERIKA_GOALS']       = $npc_data['goals'] ?? '';
                $GLOBALS['HERIKA_PERS'] 	   = $npc_data['core'] ?? ''; //

                unset($GLOBALS['HERIKA_RELATIONSHIPS']);
                */
                Logger::info("[get_NPC_data] {$npcName} data loaded.");
            } else {
               error_log("[get_NPC_data] Could not load {$npcName} data!".__FILE__);
               return -1;
            }
        } else {
           error_log("[get_NPC_data] Name mismatch! npcName={$npcName} npc_name_chk={$npc_name_chk} ".__FILE__);
           return -1;
        }
    } catch (Exception $e) {
        error_log("Could not load player data from core_player: " . $e->getMessage());
    }
    
    return 1;
}

?>