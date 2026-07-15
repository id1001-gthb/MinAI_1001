<?php

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Authorization");
require_once("../logger.php");

$path = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
//require_once($path . "conf".DIRECTORY_SEPARATOR."conf_.php");
require_once("/var/www/html/HerikaServer/lib/postgresql.class.php"); 
$GLOBALS["db"] = new sql();
// Fix missing config.php warning
$pluginPath = "/var/www/html/HerikaServer/ext/minai_plugin";
if (!file_exists("{$pluginPath}/config.php")) {
    copy("{$pluginPath}/config.base.php", "{$pluginPath}/config.php");
}
require_once("..".DIRECTORY_SEPARATOR."config.php");
require_once("..".DIRECTORY_SEPARATOR."importDataToDB.php");
require_once("..".DIRECTORY_SEPARATOR."util.php");
require_once("..".DIRECTORY_SEPARATOR."db_utils.php");

$b_do_patch = false;
$b_do_clean = false;

$s_chim_version = getChimVersionFile(); // get CHIM version from .version_number.txt
$s_prev_version = getChimVersion(); //getConfOptionValue
if ($s_chim_version != $s_prev_version) {
    $b_do_patch = true;
    setChimVersion($s_chim_version);
} 

$s_min_ver = getMinaiVersionFile();
$s_min_prev_ver = getMinaiVersion();
if ($s_min_ver != $s_min_prev_ver) {
    $b_do_patch = true;
    setMinaiVersion($s_min_ver);
} 

$i_crt = time();
$i_dtime = $i_crt - getLastRun();
if ($i_dtime > 4096) {
    $b_do_patch = true;
    $b_do_clean = true;
    setLastRun($i_crt);
}

if ($b_do_patch) {

    $startScript = "/var/www/html/HerikaServer/ext/minai_plugin/utils/xtra/m_patch_all.sh";
    if (file_exists($startScript)) {
        $output = [];
        $retval = null;
        $res = exec($startScript, $output, $retval);
        $res = $res ? $res : "F";
        error_log("[api/main] exec {$startScript} res={$res} return code={$retval} output: " . print_r($output,true));
    } else 
        error_log("[api/main] file not found: {$startScript} ");
    
    // ENFORCE_ACTIONS_PROMPT
    try {

        $db = $GLOBALS['db'];
        
        $query = " UPDATE public.core_llm_connector SET metadata['remove_action_prompt'] = 'false'; ";
        $db->execQuery($query);        
                    
        $query = " UPDATE public.core_npc_master SET extended_data['ENFORCE_ACTIONS_PROMPT'] = 'true'; ";
        $db->execQuery($query);        

        $query = " UPDATE public.core_profiles SET metadata['ENFORCE_ACTIONS_PROMPT'] = 'true'; ";
        $db->execQuery($query);        
        
        error_log("[init] action prompts patch done. ");
        
    } catch (Exception $e) {
        $b_ok = false;
        error_log("[init] ERROR patching action prompts " . $e->getMessage());
    }                
    
}

if ($b_do_clean) {

    $startScript = "/var/www/html/HerikaServer/ext/minai_plugin/m_init.sh";
    if (file_exists($startScript)) {
        $output = [];
        $retval = null;
        $res = exec($startScript, $output, $retval);
        $res = $res ? $res : "F";
        error_log("[api/main] exec {$startScript} res={$res} return code={$retval} output: " . print_r($output,true));
    } else 
        error_log("[api/main] file not found: {$startScript} ");

    // CHIM action editor is incompatible with MinAI
    $s_filter_file = "/var/www/html/HerikaServer/functions/user_pref.json"; //__DIR__."/../../../functions/user_pref.json"; 
    if (is_file($s_filter_file)) {
        error_log("[api/main] found actions filter: s_filter_file "); // debug
        unlink($s_filter_file);
    }

    InitiateDBTables();
}

$requestMethod = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

// Determine the endpoint being accessed
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : null;

switch ($requestMethod) {
    case 'GET':
        handleGetRequest($endpoint);
        break;
        
    case 'POST':
        handlePostRequest($endpoint, $data);
        break;
        
    case 'PUT':
        handlePutRequest($endpoint, $data);
        break;
        
    case 'DELETE':
        handleDeleteRequest($endpoint, $data);
        break;
        
    case 'OPTIONS':
        // Handle preflight request (CORS-related)
        http_response_code(200);
        exit();
        
    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["message" => "Method Not Allowed"]);
        break;
}

// Handle GET requests
function handleGetRequest($endpoint) {
    if ($endpoint === 'index_payload') {
        // Example response for a GET request
        $nsfw = ((IsModEnabled("sexlab") || IsModEnabled("ostim")) && !$GLOBALS["disable_nsfw"]) ? "nsfw" : "sfw";
        echo json_encode(["message" => "GET request received", "data" => ["nsfw" => $nsfw]]);
    } else {
        echo json_encode(["message" => "GET endpoint not found"]);
    }
}

// Handle POST requests
function handlePostRequest($endpoint, $data) {
    if ($endpoint === 'reset_personalities') {
        $GLOBALS["db"]->execQuery("DROP TABLE IF EXISTS minai_x_personalities");
        unlink(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."xPersonalitiesDBImport".DIRECTORY_SEPARATOR."imported.txt");
        importXPersonalities();
        echo json_encode(["message" => "Success"]);
    } elseif ($endpoint === 'reset_scenes') {
        $GLOBALS["db"]->execQuery("DROP TABLE IF EXISTS minai_scenes_descriptions");
        unlink(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."sceneDescriptionsDBImport".DIRECTORY_SEPARATOR."imported.txt");
        importScenesDescriptions();
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["message" => "POST endpoint not found"]);
    }
}

// Handle PUT requests
function handlePutRequest($endpoint, $data) {
    if ($endpoint === 'example') {
        // Example response for a PUT request
        echo json_encode(["message" => "PUT request received", "data" => $data]);
    } else {
        echo json_encode(["message" => "PUT endpoint not found"]);
    }
}

// Handle DELETE requests
function handleDeleteRequest($endpoint, $data) {
    if ($endpoint === 'example') {
        // Example response for a DELETE request
        echo json_encode(["message" => "DELETE request received", "data" => $data]);
    } else {
        echo json_encode(["message" => "DELETE endpoint not found"]);
    }
}

