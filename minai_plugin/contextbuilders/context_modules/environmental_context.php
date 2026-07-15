<?php
/**
 * Environmental Context Builders
 * 
 * This file contains context builders related to the environment and surroundings
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../util.php");
require_once(__DIR__ . "/../system_prompt_context.php");
require_once(__DIR__ . "/../../environmentalContext.php");
require_once(__DIR__ . "/../../contextbuilders/weather_context.php");
require_once(__DIR__ . "/../../contextbuilders/dirtandblood_context.php");
require_once(__DIR__ . "/../../contextbuilders/exposure_context.php");
require_once("/var/www/html/HerikaServer/ext/minai_plugin/location_context_details.php");
require_once("/var/www/html/HerikaServer/lib/utils_game_timestamp.php");
require_once("/var/www/html/HerikaServer/ext/minai_plugin/contextbuilders/context_modules/character_context_size_dict.php");

/**
 * Helper function to validate and sanitize parameters for context builders
 * 
 * @param array $params Parameters to validate
 * @param array $required List of required parameter keys
 * @return array Validated and sanitized parameters with fallbacks if needed
 */
function ValidateEnvironmentParams($params, $required = ["herika_name", "player_name", "target"]) {
    $validated = [];
    
    // Check for required parameters
    foreach ($required as $key) {
        if (isset($params[$key])) {
            $validated[$key] = $params[$key];
        } else {
            // Try to use globals as fallback
            switch ($key) {
                case "herika_name":
                    $validated[$key] = isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : "";
                    break;
                case "player_name":
                    $validated[$key] = isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "";
                    break;
                case "target":
                    $validated[$key] = isset($GLOBALS["HERIKA_TARGET"]) ? 
                                      $GLOBALS["HERIKA_TARGET"] : 
                                      (isset($validated['player_name']) ? $validated['player_name'] : "");
                    break;
                default:
                    $validated[$key] = "";
            }
        }
    }
    
    // Add any other parameters that were in the original params
    foreach ($params as $key => $value) {
        if (!isset($validated[$key])) {
            $validated[$key] = $value;
        }
    }
    
    if (($params["target"] == strtolower($params["target"])) || 
        ($params['herika_name'] == strtolower($params['herika_name'])) || 
        ($params['player_name'] == strtolower($params['player_name'])) ) { // debug   
        error_log(" WARNING params: " . print_r($params, true) ." validated: " . print_r($validated, true) );    
    } 
    return $validated;
}

/**
 * Initialize environmental context builders
 */
function InitializeEnvironmentalContextBuilders() {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Register day/night state context builder
    $registry->register('day_night_state', [
        'section' => 'environment',
        'header' => 'Time of Day',
        'description' => 'Current time and day/night cycle information',
        'priority' => 5,
        'enabled' => isset($GLOBALS['minai_context']['day_night_state']) ? (bool)$GLOBALS['minai_context']['day_night_state'] : true,
        'builder_callback' => 'BuildDayNightStateContext'
    ]);
    
    // Register weather context builder
    $registry->register('weather', [
        'section' => 'environment',
        'header' => 'Weather',
        'description' => 'Current weather conditions',
        'priority' => 10,
        'enabled' => isset($GLOBALS['minai_context']['weather']) ? (bool)$GLOBALS['minai_context']['weather'] : true,
        'builder_callback' => 'BuildWeatherContext'
    ]);
    
    // Register moon phase context builder
    $registry->register('moon_phase', [
        'section' => 'environment',
        'header' => 'Moon Phase',
        'description' => 'Current phase of the moons',
        'priority' => 15,
        'enabled' => isset($GLOBALS['minai_context']['moon_phase']) ? (bool)$GLOBALS['minai_context']['moon_phase'] : true,
        'builder_callback' => 'BuildMoonPhaseContext'
    ]);
    
    // Register location context builder
    $registry->register('location', [
        'section' => 'environment',
        'header' => 'Current Location',
        'description' => 'Interior/exterior location information',
        'priority' => 20,
        'enabled' => isset($GLOBALS['minai_context']['location']) ? (bool)$GLOBALS['minai_context']['location'] : true,
        'builder_callback' => 'BuildLocationContext'
    ]);
    
    // Register nearby buildings context builder
    $registry->register('nearby_buildings', [
        'section' => 'environment',
        'header' => 'Nearby Points of Interest, Doors and Passages',
        'description' => 'Doors and passages in the vicinity',
        'priority' => 21,
        'enabled' => isset($GLOBALS['minai_context']['nearby_buildings']) ? (bool)$GLOBALS['minai_context']['nearby_buildings'] : true,
        'builder_callback' => 'BuildNearbyBuildingsContext'
    ]);
    
    // Register frostfall context builder (if the mod is enabled)
    $registry->register('frostfall', [
        'section' => 'environment',
        'header' => 'Temperature & Exposure',
        'description' => 'Temperature and exposure information from Frostfall',
        'priority' => 25,
        'enabled' => isset($GLOBALS['minai_context']['frostfall']) ? (bool)$GLOBALS['minai_context']['frostfall'] : true,
        'builder_callback' => 'BuildFrostfallContext'
    ]);
    
    // Register nearby characters context builder
    $registry->register('nearby_characters', [
        'section' => 'environment',
        'header' => 'Nearby Characters',
        'description' => 'Characters in close proximity',
        'priority' => 35,
        'enabled' => isset($GLOBALS['minai_context']['nearby_characters']) ? (bool)$GLOBALS['minai_context']['nearby_characters'] : true,
        'builder_callback' => 'BuildNearbyCharactersContext'
    ]);
    
    // Register NPC relationships context builder
    $registry->register('npc_relationships', [
        'section' => 'environment',
        'header' => 'Character Relationships',
        'description' => 'NPC relationship to the player',
        'priority' => 40,
        'enabled' => isset($GLOBALS['minai_context']['npc_relationships']) ? (bool)$GLOBALS['minai_context']['npc_relationships'] : true,
        'builder_callback' => 'BuildNPCRelationshipsContext'
    ]);
}

/**
 * Build the day/night state context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted day/night context
 */
function BuildDayNightStateContext($params) {
    /* original code:
    $character = $params['player_name'];
    
    // Try to get detailed date information first
    $locationContext = GetCurrentLocationContext($character);
    if (!empty($locationContext['date'])) {
        return "The current time in Skyrim is " . $locationContext['date'] . ".";
    }
    
    // Fall back to basic day state if detailed date isn't available
    $dayState = GetActorValue($character, "dayState");
    if (empty($dayState)) {
        return "";
    }
    
    return "It is " . $dayState . ".";
    -- end original code */
    return get_datetime_for_prompt_explained(0,true,true,true);
}

/**
 * Build the weather context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted weather context
 */
function BuildWeatherContext($params) {
    $player_name = $params['player_name'];
    
    // Get the weather context
    $weatherContext = GetWeatherContext();
    
    // Check if character is indoors
    if (IsEnabled($player_name, "isInterior") && !empty($weatherContext)) {
        return "Outside, the weather is: " . $weatherContext;
    }
    
    return $weatherContext;
}

/**
 * Build the moon phase context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted moon phase context
 */
function BuildMoonPhaseContext($params) {
    $params = ValidateEnvironmentParams($params);
    $character = $params["target"];
    $utilities = new Utilities();
    
    // Get raw moon data
    $moonPhase = GetActorValue($character, "moonPhase");
    $isNight = IsEnabled($character, "isNight");
    $moonCount = GetActorValue($character, "moonCount");
    
    // Check if we have all required data
    if (empty($moonPhase) || empty($moonCount)) {
        return "";
    }
    
    // Build the formatted string
    $text = "The " . $moonCount;
    
    // Are/will be based on time of day
    if ($isNight) {
        $text .= " are";
    } else {
        $text .= " will be";
    }
    
    // Moon phase description
    switch (intval($moonPhase)) {
        case 0:
            $text .= " full";
            break;
        case 1:
            $text .= " wanning gibbious";
            break;
        case 2:
            $text .= " third quarter";
            break;
        case 3:
            $text .= " wanning crescent";
            break;
        case 4:
            $text .= " in new moon";
            break;
        case 5:
            $text .= " waxing crescent";
            break;
        case 6:
            $text .= " first quarter";
            break;
        case 7:
            $text .= " waxing gibbious";
            break;
        default:
            return ""; // Invalid phase, don't return anything
    }
    
    $text .= " tonight.";
    
    return $text;
}

/**
 * Get description for a location keyword
 * 
 * @param string $keyword The location keyword
 * @return string Description of the location type
 */
function GetLocationKeywordDescription($keyword) {

    $descriptions = [
        // Settlement Types
        'city' => 'a major urban center',
        'town' => 'a small urban settlement',
        'village' => 'a small rural settlement',
        'settlement' => 'a populated area',
        
        // Military & Defensive
        'fort' => 'a fortified military structure',
        'castle' => 'a large fortified residence',
        'keep' => 'a fortified tower or stronghold',
        'tower' => 'a tall defensive structure',
        'barracks' => 'military housing quarters',
        'palace' => 'a grand residence of nobility',
       
        // Religious
        'temple' => 'a place of worship',
        'temple_of_kynareth' => 'a temple dedicated to Kynareth',
        'temple_of_talos' => 'a temple dedicated to Talos',
        'temple_of_arkay' => 'a temple dedicated to Arkay',
        'temple_of_dibella' => 'a temple dedicated to Dibella',
        'temple_of_mara' => 'a temple dedicated to Mara',
        'temple_of_zenithar' => 'a temple dedicated to Zenithar',
        'temple_of_stendarr' => 'a temple dedicated to Stendarr',
        'temple_of_julianos' => 'a temple dedicated to Julianos',
        'temple_of_akatosh' => 'a temple dedicated to Akatosh',
        
        // Commercial
        'tavern' => 'a drinking establishment',
        'shop' => 'a general store',
        'general_store' => 'a general goods store',
        'clothing_store' => 'a clothing and apparel shop',
        'jewelry_store' => 'a jewelry and gems shop',
        'book_store' => 'a bookstore',
        'potion_store' => 'an alchemy shop',
        'scroll_store' => 'a scroll and spell shop',
        'weapon_store' => 'a weapons shop',
        'armor_store' => 'an armor shop',
        'food_store' => 'a food and provisions shop',
        'furniture_store' => 'a furniture shop',
        'black_market' => 'an illicit trading location',
        
        // Residential
        'house' => 'a residential dwelling',
        'farm' => 'an agricultural property',
        'mill' => 'a grain processing facility',
        'stable' => 'a horse stable',
        'smithy' => 'a blacksmith workshop',
        'forge' => 'a metalworking facility',
        'smelter' => 'a metal smelting facility',
        'tannery' => 'a leather working facility',
        'fishery' => 'a fishing facility',
        'brewery' => 'a beer brewing facility',
        'winery' => 'a wine production facility',
        'meadery' => 'a mead brewing facility',
        'bakery' => 'a bread baking facility',
        'butcher_shop' => 'a meat processing shop',
        
        // Guild & Faction
        'thieves_guild' => 'the Thieves Guild headquarters',
        'dark_brotherhood' => 'the Dark Brotherhood sanctuary',
        'companions_guild' => 'the Companions headquarters',
        'college_of_winterhold' => 'the College of Winterhold',
        
        // Dungeon Types
        'dungeon' => 'an underground complex',
        'ruin' => 'an ancient ruined structure',
        'nordic_ruin' => 'an ancient Nordic ruin',
        'dwemer_ruin' => 'an ancient Dwemer ruin',
        'cave' => 'a natural cave system',
        'tomb' => 'an ancient burial site',
        'crypt' => 'an underground burial chamber',
        'barrow' => 'an ancient Nordic burial mound',
        
        // Enemy Lairs
        'dragon_lair' => 'a dragon\'s territory',
        'giant_camp' => 'a giant\'s encampment',
        'vampire_lair' => 'a vampire\'s lair',
        'werewolf_lair' => 'a werewolf\'s lair',
        'falmer_lair' => 'a Falmer settlement',
        'bandit_camp' => 'a bandit encampment',
        'military_camp' => 'a military encampment',
        
        // Industrial
        'mine' => 'a mining facility',
        
        // Law Enforcement
        'prison' => 'a prison facility',
        'jail' => 'a local jail',

        // cleared status
        'location_clearable' => 'a location with possible enemy presence that needs to be cleared',
        'location_iscleared' => '<safe_location>classified now as a safe location where most or all enemies have been annihilated</safe_location>', 
        'location_notcleared' => '<unsafe_location>classified as a dangerous location where you have to be prepared for combat, enemies are nearby</unsafe_location>',
        
        // Navigation
        'lighthouse' => 'a coastal navigation aid'
    ];
    
    return isset($descriptions[$keyword]) ? $descriptions[$keyword] : 'location about which not much is known';
}

/**
 * Build the location context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted location context
 */
function BuildLocationContext($params) {
    $params = ValidateEnvironmentParams($params);
    $character = $params['player_name'];
    $utilities = new Utilities();
    
    $context = "";
    $b_cache = (rand(0, 3) == 0);
    // Get hold information
    $currentHold = ucwords(GetActorValue($character, "currentHold", true, $b_cache));
    $hasHold = (!empty($currentHold));

    // Get location information - prefer location over cell
    $currentLocation = ucwords(GetActorValue($character, "currentLocation", true, $b_cache));
    $hasLocation = (!empty($currentLocation));

    if (!$hasLocation) {
        // Try cell if no location
        $currentCell = ucwords(GetActorValue($character, "CurrentCell"));
        if (!empty($currentCell)) {
            $currentLocation = $currentCell;
            $hasLocation = true;
        } else {
            // Last resort: try GetCurrentLocationContext
            $locationContext = GetCurrentLocationContext($character);
            if (!empty($locationContext['current'])) {
                $currentLocation = ucwords($locationContext['current']);
                $hasLocation = true;
                // Add hold from locationContext if we don't have it
                if ((!$hasHold) && (!empty($locationContext['hold']))) {
                    $currentHold = ucwords($locationContext['hold']);
                    $hasHold = true;
                }
            }
        }
    }
    
    if ($hasLocation) {
        //error_log(" BuildLocationContext _{$currentLocation}_"); //debug
        $s_loc_extra = GetLocationDetails($currentLocation);
        if ($currentLocation == "MIas Palace") $currentLocation = "Mia's Palace";
        if (strlen($s_loc_extra) > 0) {
            $context .= "Current Location: " . $currentLocation . ", " . $s_loc_extra . ".\n";
        } else {
            $context .= "Current Location: " . $currentLocation . ".\n";
        }
        
    }
    
    // Hold
    if ($hasHold) {
        $context .= "Current Hold: " . $currentHold . ".\n";
    }
    
    // Get location keywords and format them
    $locationKeywords = GetActorValue($character, "locationKeywords");
    if (!empty($locationKeywords)) {
        $keywords = explode("~", $locationKeywords);
        $keywords = array_filter($keywords); // Remove empty entries
        if (count($keywords) > 0) {
            $descriptions = array_map(function($keyword) {
                return GetLocationKeywordDescription($keyword);
            }, $keywords);
            $context .= "This is " . implode(", ", $descriptions) . ".\n";
        }
    }
    
    // Check if interior
    if (IsEnabled($character, "isInterior")) {
        $context .= "We are indoors, out of the weather and elements.";
    }
    
    return $context;
}

/**
 * Build the frostfall context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted frostfall context
 */
function BuildFrostfallContext($params) {
    $params = ValidateEnvironmentParams($params);
    $character = $params["target"];
    $utilities = new Utilities();
    
    if (!IsEnabled($character, "hasFrostfall")) {
        return "";
    }
    
    $context = "";
    
    // Temperature
    $temperature = GetActorValue($character, "temperature");
    if (!empty($temperature)) {
        $context .= "The temperature is " . $temperature . ". ";
    }
    
    // Weather severity
    $weatherSeverity = GetActorValue($character, "weatherSeverity");
    if (!empty($weatherSeverity)) {
        $context .= $weatherSeverity . " ";
    }
    
    // Shelter status
    if (IsEnabled($character, "isSheltered")) {
        $context .= $character . " is sheltered by things overhead. ";
    }
    
    // Wetness level
    $wetnessLevel = GetActorValue($character, "wetnessLevel");
    if (!empty($wetnessLevel)) {
        $context .= $character . " is " . $wetnessLevel . ". ";
    }
    
    // Exposure level
    $exposureLevel = GetActorValue($character, "exposureLevel");
    if (!empty($exposureLevel)) {
        $context .= $exposureLevel . " ";
    }
    
    // Baseline exposure
    $baselineExposure = GetActorValue($character, "baselineExposure");
    if (!empty($baselineExposure)) {
        $context .= $baselineExposure . " ";
    }
    
    // Warmth rating
    $warmthRating = GetActorValue($character, "warmthRating");
    if (!empty($warmthRating)) {
        $context .= $character . " is dressed " . $warmthRating . ". ";
    }
    
    // Coverage rating
    $coverageRating = GetActorValue($character, "coverageRating");
    if (!empty($coverageRating)) {
        $context .= $character . " " . $coverageRating . " ";
    }
    
    return $context;
}


/**
 * Build the nearby characters context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted nearby characters context
 */
function BuildNearbyCharactersContext($params) {
    $params = ValidateEnvironmentParams($params);
    $herika_name = $params['herika_name'];
    $player_name = $params['player_name'];
    $target = $params["target"];
    
    if ($herika_name == "The Narrator") {
        $herika_name = $player_name;
    }
    $localActors = DataBeingsInRange2();
    $b_empyt1 = (empty($localActors));
    
    // add missing companions
    $arr_party2 = GetCurrentPartyMembers();
    if (count($arr_party2['names']) > 0) {
        $arr_party = $arr_party2['names'];
        $b_empyt2 = false;
        //error_log("party2: ".print_r($arr_party2,true)); // debug
        //error_log("party: ".print_r($arr_party,true)); // debug
/*
    foreach ($partyInfo['names'] as $memberName) {
        if (strtolower($memberName) === strtolower($characterName)) {
            return true;
        }
    }

 --- GLOBALS[CACHE_PEOPLE] 
|Lydia|Anja Iceheart|Sofie|Helen|Derkeethus|Thonar Silver-Blood|Sylgja|Ogol|Jorunn|Medea|Annekke Crag-Jumper|
Jeanine|Celestine|Felicia|Moon River|Juniper|Herika|Kelsa Iceheart|
Falmer Slave Nymph (hostile)|Alva|Sceolang|Falmer Slave Nymph (hostile)|
Hercules the Dog|Golldir|Falmer Slave Nymph (hostile)|Daphnne|Charlotte|Akomi|Camilla Valerius|Carrot|Faendal|Ina|Lucifer|Serana|Falmer Slave Nymph (hostile)|Aeter| [string] 


*/        
        
    } else  {
        $arr_party = [];
        $b_empyt2 = true;
        //error_log("[environmental_context] crt party is empty. ".__FILE__." ".__LINE__); // debug 
    }
    
    
    if ($b_empyt1 && $b_empyt2) {
        error_log("[environmental_context] party is empty (both). ".__FILE__." ".__LINE__); // debug 
        return "";
    }
    
    $characters = explode("|", $localActors);
    // Remove any empty entries and trim each character name
    $characters = array_filter(array_map('trim', $characters), function($item) {
        return !empty($item);
    });

    $ic1 = count($characters);
    $ic2 = count($arr_party);
    
    // Ensure herika_name, player_name and target are included without duplicates
    if (!$b_empyt2)
        $characters = array_unique_caseinsensitive(array_merge($characters, $arr_party));

    $characters = array_unique_caseinsensitive(array_merge($characters, array_filter([$herika_name, $player_name, $target])));

    
            
    // Remove parentheses from character names
    $characters = array_map(function($name) {
        return trim(trim($name, '()'));
    }, $characters);

    $characters = array_filter(array_map('trim', $characters), function($item) {
        return !empty($item);
    });

    $ic3 = count($characters);
    //error_log("[environmental_context] nearby: $ic1 $ic2 $ic3 ".print_r($characters,true)." - debug ".__FILE__." ".__LINE__); // debug 
    /*
    */
    $is_nsfw = !($GLOBALS['disable_nsfw'] ?? true);
    
    // If we have characters after cleaning, create the formatted list
    if (count($characters) > 0) {
        // Define attributes to fetch in batch - use lowercase for array keys
        $attributes = ['race', 'gender', 'faction', 'sitstate', 'sleepstate', 'dirtandblood', 'scene','inCombatState'];
        $flags = ['IsSneaking', 'IsSwimming', 'IsOnMount', 'inCombat', 'isEncumbered', 'hostiletoplayer', 'isNaked', 'IsBleedingOut'];
        
        // Correctly call the batch functions from global scope
        $actorValues = \BatchGetActorValues($characters, $attributes);
        $actorFlags = \BatchIsEnabled($characters, $flags);
        
        $contextLines = [];
        
        foreach ($characters as $character) {
            if (strlen(trim($character))<1) continue;
            $charKey = strtolower($character);
            $line = $character;
            $s_race = "";
            $s_gender = $actorValues[$charKey]['gender'] ?? '';
            $s_attribute = get_people_attribute($character);
            $b_restrained = ($s_attribute == 'restrained');
            $b_scene = IsInScene($character) || $b_restrained;
            $b_naked = $actorFlags[$charKey]['isnaked'] ?? false;
            $s_race = $actorValues[$charKey]['race'] ?? '';
                
            // Get race if available
            if (strlen($s_race) > 0) {
                //$s_gender = $actorValues[$charKey]['gender']; //GetGender($character);
                $s_child =  (IsChildActor($character)) ? " child" : ""; 
                $line .= " ({$s_race} {$s_gender}{$s_child})";
            } else {
                if (strlen($s_gender) > 0)
                    $line .= " ({$s_gender})";
            }
            if (!$b_restrained) {
                if (strlen($s_attribute) > 0) {
                    $line .= " ({$s_attribute})";
                }
            }
            // Add faction info if available
            if (isset($actorValues[$charKey]['faction']) && !empty($actorValues[$charKey]['faction'])) {
                $line .= " - " . $actorValues[$charKey]['faction'];
            }

            // Add hostility flag
            if (isset($actorFlags[$charKey]['hostiletoplayer']) && $actorFlags[$charKey]['hostiletoplayer']) {
                $line .= " - hostile to outsiders";
            }

            if (isset($actorFlags[$charKey]['isencumbered']) && $actorFlags[$charKey]['isencumbered']) {
                $line .= " - encumbered";
            }

            if (isset($actorFlags[$charKey]['isbleedingout']) && $actorFlags[$charKey]['isbleedingout']) {
                $line .= " - bleeding";
            }

            // Add hygiene information 
            $b_dirt = ($GLOBALS['minai_context']['dirt_and_blood'] ?? false);
            if ($b_dirt && isset($actorValues[$charKey]['dirtandblood']) && (!empty($actorValues[$charKey]['dirtandblood']))) {
                $hygiene = $actorValues[$charKey]['dirtandblood'];
                
                //if (stripos($hygiene, "Clean") !== false) {
                    //$line .= " - clean";
                //} else
                if (stripos($hygiene, "Dirt4") !== false) {
                    $line .= " - filthy";
                } elseif (stripos($hygiene, "Dirt3") !== false) {
                    $line .= " - very dirty";
                } elseif (stripos($hygiene, "Dirt2") !== false) {
                    $line .= " - dirty";
                } elseif (stripos($hygiene, "Dirt1") !== false) {
                    $line .= " - slightly dirty";
                } elseif (stripos($hygiene, "Blood4") !== false) {
                    $line .= " - covered in blood";
                } elseif (stripos($hygiene, "Blood3") !== false) {
                    $line .= " - bloody";
                } elseif (stripos($hygiene, "Blood2") !== false) {
                    $line .= " - blood-spattered";
                } elseif (stripos($hygiene, "Blood1") !== false) {
                    $line .= " - blood-stained";
                } elseif (stripos($hygiene, "Bathing") !== false) {
                    $line .= " - bathing";
                }
                
                // Add scent information
                if (stripos($hygiene, "Lavender") !== false) {
                    $line .= " - lavender scented";
                } elseif (stripos($hygiene, "Blue") !== false) {
                    $line .= " - fresh scented";
                } elseif (stripos($hygiene, "Red") !== false) {
                    $line .= " - rose scented";
                } elseif (stripos($hygiene, "DragonsTongue") !== false) {
                    $line .= " - spicy scented";
                } elseif (stripos($hygiene, "Purple") !== false) {
                    $line .= " - jazbay scented";
                } elseif (stripos($hygiene, "Superior") !== false) {
                    $line .= " - luxuriously scented";
                }
            }

            if ($b_naked || $b_scene) {
                if (strlen($s_gender) > 0) {
                    if (!IsCreature($character)) {
                        $line .= " - naked";
                    }
                    if ($is_nsfw) {
                        if ($s_gender == 'male') {
                            $line .= GetPenisSizeShort($character);
                            $line .= GetPenisSizeDetails($character, $s_race, true);
                            
                            $arousalThreshold = intval(GetActorValue($GLOBALS['PLAYER_NAME'], "arousalForSex")); // arousalForSex arousalForHarass
                            $arousal = intval(GetActorValue($character, "arousal"));
                            if ($b_scene) {
                                $line.= ", in erection";
                            } elseif ($arousal >= intval($arousalThreshold * 0.8)) {
                                $line.= ", in erection";
                            } elseif ($arousal <= intval($arousalThreshold * 0.2))  {
                                $line.= ", flaccid now"; 
                            }
                        }
                    }
                } else { // no gender yet, let's look for names
                    if ($is_nsfw && $b_scene) {
                        if ($s_gender != 'female') {
                            //$sx .= GetPenisSizeShort($character);
                            $sx = GetPenisSizeDetails($character, '', true);
                            if (strlen($sx)>0)
                                $line .= " - having erected" . $sx;
                        }
                    }
                }
            }

            // ---------------------- 

            if ($b_scene) { //IsInScene($character) 
                //error_log(" in scene: $character - dbg ");
                $line .= " - having sex now";
                if ($s_gender == 'female') {
                    $s_fertile = GetFertilityContextShort($character, false, true);
                    if (strlen($s_fertile) > 0)
                        $line .= " - " . $s_fertile; 
                }
            } else {

                // Movement and combat states
                if (isset($actorFlags[$charKey]['issneaking']) && $actorFlags[$charKey]['issneaking']) {
                    $line .= " - sneaking";
                }
                
                if (isset($actorFlags[$charKey]['isswimming']) && $actorFlags[$charKey]['isswimming']) {
                    $line .= " - swimming";
                }
                
                if (isset($actorFlags[$charKey]['isonmount']) && $actorFlags[$charKey]['isonmount']) {
                    $line .= " - on horseback";
                }
                
                if (isset($actorFlags[$charKey]['isincombat']) && $actorFlags[$charKey]['isincombat']) {
                    $line .= " - in combat";
                }
                //inCombatState
                if (isset($actorValues[$charKey]['inCombatState']) && !empty($actorValues[$charKey]['inCombatState']) && intval($actorValues[$charKey]['inCombatState']) == 2) {
                    $line .= " - searching for enemies";
                }

                // Add character state
                if (isset($actorValues[$charKey]['sitstate']) && !empty($actorValues[$charKey]['sitstate']) && intval($actorValues[$charKey]['sitstate']) == 3) {
                    $line .= " - sitting";
                }
                
                // Check if character is sleeping
                if (isset($actorValues[$charKey]['sleepstate']) && !empty($actorValues[$charKey]['sleepstate']) && $actorValues[$charKey]['sleepstate'] != "awake") {
                    $line .= " - " . $actorValues[$charKey]['sleepstate'];
                }
                
            }
            if ($s_gender == 'female') {
                $s_fertile = GetFertilityContextShort($character, true, false);
                if (strlen($s_fertile) > 0)
                $line .= " - " . $s_fertile; 
            }
            
            $contextLines[] = $line;
        }
        $n_cl = count($contextLines);
        if ($n_cl > 0) {
            $s_cl = implode("\n", $contextLines); 
            return $s_cl;
        }
    }
    
    return "";
}

/**
 * Build the NPC relationships context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted NPC relationships context
 */
function BuildNPCRelationshipsContext($params) {
    $params = ValidateEnvironmentParams($params);
    $character = $params["target"];
    $player_name = $params['player_name'];
    $utilities = new Utilities();
    
    // If the target is the player, no need for this context
    if ($character == $player_name) {
        return "";
    }
    
    $context = "";
    
    // Check if NPC is bribed
    if (IsEnabled($character, "isBribed")) {
        $context .= $character . " seems smug around " . $player_name . ". ";
    }
    
    // Check if NPC is intimidated
    if (IsEnabled($character, "isIntimidated")) {
        $context .= $character . " seems anxious and a little frightened around " . $player_name . ". ";
    }
    
    return $context;
}

/**
 * Build the nearby buildings context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted nearby buildings context
 */
function BuildNearbyBuildingsContext($params) {
    $player_name = $params['player_name'];
    $locationContext = GetCurrentLocationContext($player_name);
    
    // Check if we have buildings information
    if (empty($locationContext['buildings']) || count($locationContext['buildings']) == 0) {
        return "";
    }
    
    $context = "";
    
    foreach ($locationContext['buildings'] as $building) {
        if (!empty($building['name'])) {
            $context .= "- " . $building['name'];
            
            if (!empty($building['destination'])) {
                $context .= " (" . $building['destination'] . ")";
            }
            
            $context .= "\n";
        }
    }
    
    return $context;
}