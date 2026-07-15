<?php
// not to be included explicitly, must be included only via requireFilesRecursively() L 2050 before head[]
//error_log("-- context_pre -- ");

if ($gameRequest[0] == 'region') {
    terminate();
}

//$GLOBALS["ENFORCE_ACTIONS_  PROMPT"] = true;
save_original_herika_name();

//-------------------------------------------------

//require_once($GLOBALS["ENGINE_PATH"]."lib/l ogx.php"); // debug ???
require_once(__DIR__."/util_chim.php"); 


//-------------------------------------------------

//$GLOBALS["BOOK_EVENT_ALWAYS_NARRATOR"] = true;
//$GLOBALS["BOOK_EVENT_FULL"] = false;

//-------------------------------------------------

  
get_player_data();
get_narrator_data();
get_NPC_data($GLOBALS["HERIKA_NAME"]);

if ((IsRadiant()) || (IsSexActive())) {
	//error_log(" Radiant - exec trace "); //debug
	$GLOBALS["BORED_EVENT_SERVERSIDE"] = false; // MinAI radiant will suspend CHIM bored ss event
	$GLOBALS["RANDOM_NARATION"] = false;
}

if ( (!isset($GLOBALS["CACHE_PEOPLE_ARRAY"])) || (count($GLOBALS["CACHE_PEOPLE_ARRAY"]) < 1)  ) {

    $sourceString = trim($GLOBALS["CACHE_PEOPLE"]); // The source string from the global variable
    $cleanString = trim($sourceString, '|');

    if (strlen($cleanString)>1) {
        $npcs = []; // Initialize the result array

        // 1. Explode the string by the delimiter '|'
        $parts = explode('|', $cleanString);

        foreach ($parts as $part) {
            // 2. Trim whitespace and skip empty strings 

            // (handles empty results from leading/trailing pipes)
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            // 3. Parse the NPC Name and Attribute
            // We look for the last occurrence of ' (' to separate the name from the attribute.
            // We also verify the string ends with ')' to ensure it is a valid attribute block.

            $openParenPos = strrpos($part, ' (');

            if (($openParenPos !== false) && (substr($part, -1) === ')')) {
                // Attribute found: Extract name and attribute
                $name = trim(substr($part, 0, $openParenPos));
                
                // Extract the substring between ' (' and ')'
                // Start index is position of '(' + 2 (to skip space and '(')
                // Length calculation is handled by passing -1 to substr to remove the trailing ')'
                $attribute = trim(substr($part, $openParenPos + 2, -1));
                
                $npcs[$name] = $attribute;
            } else {
                // No attribute found: Use empty string
                $npcs[$part] = '';
            }
        } // --- end for
        if (count($npcs) > 0) {
            $GLOBALS["CACHE_PEOPLE_ARRAY"] = $npcs;
        }
        unset($parts);
        unset($npcs);
    }
}


if (isset($GLOBALS["CACHE_PEOPLE_ARRAY"])) {

    if (isset($GLOBALS["CACHE_POSIBLE_INSPECT_TARGETS"])) {    
        $parts = [];
        $b_packed = false;
        if (isset($GLOBALS["CACHE_POSIBLE_INSPECT_TARGETS"][1])) { // packed
            $parts = $GLOBALS["CACHE_POSIBLE_INSPECT_TARGETS"][1];
            $b_packed = false;
        } else { // unpacked
            $parts = $GLOBALS["CACHE_POSIBLE_INSPECT_TARGETS"][0] ?? [];
            $b_packed = false;
        }
        
        $npcs = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            if ((strlen($part)>1) && is_numeric($part[0]) && ($part[1] == ' ')) {
                $part = substr($part, 2);
            }

            $openParenPos = strrpos($part, ' (');

            if (($openParenPos !== false) && (substr($part, -1) === ')')) {
                // Attribute found: Extract name and attribute
                $name = trim(substr($part, 0, $openParenPos));
                // Extract the substring between ' (' and ')'
                // Start index is position of '(' + 2 (to skip space and '(')
                // Length calculation is handled by passing -1 to substr to remove the trailing ')'
                $attribute = trim(substr($part, $openParenPos + 2, -1));
                
                $npcs[$name] = $attribute;
            } else {
                // No attribute found: Use empty string
                $npcs[$part] = '';
            }
        } // --- end foresch
        if (count($npcs) > 0) {
            $GLOBALS["CACHE_PEOPLE_ARRAY"] = array_merge($GLOBALS["CACHE_PEOPLE_ARRAY"], $npcs);
        }        
        unset($npcs);
        unset($parts);
    }

    //log 0("\n --- GLOBALS[CACHE_PEOPLE_ARRAY] ");
    //log 1($GLOBALS["CACHE_PEOPLE_ARRAY"]);
}

