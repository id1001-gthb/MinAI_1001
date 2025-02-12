<?php

header('Content-Type: application/json');

$pluginPath = "/var/www/html/HerikaServer/ext/minai_plugin";
if (!file_exists("$pluginPath/config.php")) {
    copy("$pluginPath/config.base.php", "$pluginPath/config.php");
}

// Define the directory where the config file is located
$configFile = '../config.php';

// Read config data from the file (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    include($configFile);

    // Prepare the response by extracting all $GLOBALS values
    $configData = array(
        "PROMPT_HEAD_OVERRIDE" => $GLOBALS["PROMPT_HEAD_OVERRIDE"],
        "use_narrator_profile" => $GLOBALS["use_narrator_profile"],
        "enforce_short_responses" => $GLOBALS["enforce_short_responses"],
        "stop_narrator_context_leak" => $GLOBALS["stop_narrator_context_leak"],
        "devious_narrator_eldritch_voice" => $GLOBALS["devious_narrator_eldritch_voice"],
        "devious_narrator_telvanni_voice" => $GLOBALS["devious_narrator_telvanni_voice"],
        "force_voice_type" => $GLOBALS["force_voice_type"],
        "self_narrator" => $GLOBALS["self_narrator"],
        "disable_nsfw" => $GLOBALS["disable_nsfw"],
        "restrict_nonfollower_functions" => $GLOBALS["restrict_nonfollower_functions"],
        "always_enable_functions" => $GLOBALS["always_enable_functions"],
        "force_aiff_name_to_ingame_name" => $GLOBALS["force_aiff_name_to_ingame_name"],
        "commands_to_purge" => $GLOBALS["commands_to_purge"],
        "events_to_ignore" => $GLOBALS["events_to_ignore"],
        "use_defeat" => $GLOBALS["use_defeat"],
        "disable_worn_equipment" => $GLOBALS["disable_worn_equipment"],
        "xtts_server_override" => $GLOBALS["xtts_server_override"],
        "strip_emotes_from_output" => $GLOBALS["strip_emotes_from_output"],
        "realnames_support" => $GLOBALS["realnames_support"],
        "input_delay_for_radiance" => $GLOBALS["input_delay_for_radiance"],
        "voicetype_fallbacks" => $GLOBALS["voicetype_fallbacks"],
        "use_llm_fallback" => $GLOBALS["use_llm_fallback"],
    );

    // Return the config data as JSON
    echo json_encode($configData);
}

// Update config data and write it back to the config.php file (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Manually build the new config content string
    $newConfig = "<?php\n";
    $newConfig .= "\$GLOBALS['PROMPT_HEAD_OVERRIDE'] = \"" . ($input['PROMPT_HEAD_OVERRIDE']) . "\";\n";
    $newConfig .= "\$GLOBALS['use_narrator_profile'] = " . ($input['use_narrator_profile'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['enforce_short_responses'] = " . ($input['enforce_short_responses'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['stop_narrator_context_leak'] = " . ($input['stop_narrator_context_leak'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['devious_narrator_eldritch_voice'] = \"" . ($input['devious_narrator_eldritch_voice']) . "\";\n";
    $newConfig .= "\$GLOBALS['devious_narrator_telvanni_voice'] = \"" . ($input['devious_narrator_telvanni_voice']) . "\";\n";
    $newConfig .= "\$GLOBALS['self_narrator'] = " . ($input['self_narrator'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['force_voice_type'] = " . ($input['force_voice_type'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['disable_nsfw'] = " . ($input['disable_nsfw'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['restrict_nonfollower_functions'] = " . ($input['restrict_nonfollower_functions'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['always_enable_functions'] = " . ($input['always_enable_functions'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['force_aiff_name_to_ingame_name'] = " . ($input['force_aiff_name_to_ingame_name'] ? 'true' : 'false') . ";\n";

    // Write arrays using the desired Array() format
    $newConfig .= "\$GLOBALS['commands_to_purge'] = " . buildArrayString($input['commands_to_purge']) . ";\n";
    $newConfig .= "\$GLOBALS['events_to_ignore'] = " . buildArrayString($input['events_to_ignore']) . ";\n";
    
    $newConfig .= "\$GLOBALS['use_defeat'] = " . ($input['use_defeat'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['disable_worn_equipment'] = " . ($input['disable_worn_equipment'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['xtts_server_override'] = \"" . ($input['xtts_server_override']) . "\";\n";
    $newConfig .= "\$GLOBALS['strip_emotes_from_output'] = " . ($input['strip_emotes_from_output'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['realnames_support'] = " . ($input['realnames_support'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['input_delay_for_radiance'] = " . intval($input['input_delay_for_radiance']) . ";\n";

    // Write the voicetype_fallbacks array using the Array() format
    $newConfig .= "\$GLOBALS['voicetype_fallbacks'] = " . buildAssociativeArrayString($input['voicetype_fallbacks']) . ";\n";

    // Add the new use_llm_fallback option
    $newConfig .= "\$GLOBALS['use_llm_fallback'] = " . ($input['use_llm_fallback'] ? 'true' : 'false') . ";\n";

    // Save the new config to the config.php file
    $success = (file_put_contents($configFile, $newConfig) !== false);

    // Send response
    echo json_encode(['status' => $success?'success':'error']);
}

// Function to build a string for indexed arrays using Array("value1", "value2") format
function buildArrayString($array) {
    $arrayString = 'Array(';
    $values = array_map(function($value) {
        return '"' . ($value) . '"';
    }, $array);
    $arrayString .= implode(', ', $values);
    $arrayString .= ')';
    return $arrayString;
}

// Function to build a string for associative arrays using Array("key" => "value") format
function buildAssociativeArrayString($array) {
    $arrayString = 'Array(';
    $elements = [];
    foreach ($array as $key => $value) {
        $elements[] = '"' . ($key) . '" => "' . addslashes($value) . '"';
    }
    $arrayString .= implode(', ', $elements);
    $arrayString .= ')';
    return $arrayString;
}
?>
