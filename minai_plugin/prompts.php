<?php
// not to be included explicitly, must be included only via requireFilesRecursively() - is included from prompts in main folder
//error_log("-- prompts -- ");

//Avoid processing for fast / storage events 
// DON'T DO, enabling this has unexpected will inject unwanted types in eventlog
//if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
//    return;
//}

if (!($GLOBALS["checkpoint_globals_php"] ?? false)){
    require_once(__DIR__."/globals.php");
}

if (!($GLOBALS["checkpoint_preprocessing_php"] ?? false)){
    require_once(__DIR__."/preprocessing.php");
}

minai_start_timer("prompts_php", "MinAI");

include(__DIR__."/config.php");

if ((isset($GLOBALS["action_prompts"]["normal_scene"])) &&
    (isset($GLOBALS["action_prompts"]["explicit_scene"]))) {
    if (!isset($GLOBALS["action_prompts_copy"])) {
        $GLOBALS["action_prompts_copy"] = $GLOBALS["action_prompts"];
        //error_log(" prompts: making action_prompts copy "); // debug
    }
}
require_once(__DIR__."/util.php");
require_once(__DIR__."/sexPrompts.php");
require_once(__DIR__."/customintegrations.php");
require_once(__DIR__."/functions/deviousnarrator.php");

$inb = "<instruction>(";
$ine = ".)</instruction>";

// Custom command / third party integrations support
// Done here, as this is mounted early in main.php
ProcessIntegrations();
$cleanedMessage = GetCleanedMessage();
$enforceLength = "You MUST Respond with no more than two sentences.";

$i_random = rand(1, 11); // to lower the probability of some cues

$GLOBALS["PROMPTS"]["radiant"] = [
    "cue"=>[
        //"write dialogue for {$GLOBALS["HERIKA_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} " ////'write' prefix lead to double answers, TEMPLATE_DIALOG already has a "Write ..." => the result is "write dialogue ... Write next line"
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking about a relevant topic mentioned in DIALOGUE HISTORY{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking about a RECENT EVENT{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking about an intriguing RECENT EVENT{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking about an intriguing topic mentioned in DIALOGUE HISTORY{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking about a topic that was not mentioned in DIALOGUE HISTORY and RECENT EVENTS{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} "
    ],
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} starts a dialogue with {$GLOBALS["target"]} about a relevant topic.", 
    ]
];

if ($i_random == 1) {
    array_push($GLOBALS["PROMPTS"]["radiant"]["cue"],
		"{$inb}{$GLOBALS["HERIKA_NAME"]} tell a story related to a relevant topic mentioned in DIALOGUE HISTORY and RECENT EVENTS{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} tell a joke related to a relevant topic mentioned in DIALOGUE HISTORY and RECENT EVENTS{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} remember a dream related to a relevant topic mentioned in DIALOGUE HISTORY and RECENT EVENTS{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}"
    );
}

$GLOBALS["PROMPTS"]["minai_force_rechat"] = [
    "cue"=>[
        "{$inb}{$GLOBALS["HERIKA_NAME"]} responds to {$GLOBALS["target"]}{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		//-----------------
		"{$inb}{$GLOBALS["HERIKA_NAME"]} speak to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} share a related fact or piece of knowledge{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} reacts to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} ask the interlocutor to elaborate further{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} speak to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} challenge interlocutor viewpoint{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} expresses curiosity about the current topic{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} summarize the key points of the discussion{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} add their insights to the conversation{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} reacts to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} add humor to lighten the conversation{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} responds to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} follow the conversation and express their own thoughts{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} reacts to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} makes a personal remark{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} share an opinion with the interlocutor{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} reacts to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} tell a joke related to current conversation topic{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} responds to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} add a personal point of view regarding conversation topic{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} contribute with personal expertise regarding conversation topic{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} reacts to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} interject and add their own opinion{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		//"{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking to {$GLOBALS["target"]}{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		//----------------- argumentative
		"{$inb}{$GLOBALS["HERIKA_NAME"]} responds to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} ask a question related to conversation topic{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} responds to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} ask interlocutor to give arguments{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} responds to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} ask interlocutor to provide more details{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} responds to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} ask interlocutor to explain what was said{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} reacts to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} ask interlocutor if what said is true{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} reacts to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} ask group opinion about what was said{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		//"{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking to {$GLOBALS["target"]}{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		//----------------- antagonistic 
		"{$inb}{$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} doubt about what was said{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} contradict the interlocutor{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} speak to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} mock the interlocutor{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} deride the interlocutor's opinion{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} speak to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} show disdain for the interlocutor's opinion{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		//-----------------
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is talking to {$GLOBALS["target"]}{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}  " //
    ],
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} responds to {$GLOBALS["target"]} about the ongoing conversation.",
    ]
];

if (($i_random == 3) || ($i_random == 7)) {
    array_push($GLOBALS["PROMPTS"]["minai_force_rechat"]["cue"],
		//----------------- story
		"{$inb}{$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} tell a story related to the current topic{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
		"{$inb}{$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["target"]}. {$GLOBALS["HERIKA_NAME"]} remember a dream they had related to the current topic{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}"
    );
}

$GLOBALS["PROMPTS"]["radiantsearchinghostile"]= [
    "cue"=>[
        //"write dialogue for {$GLOBALS["HERIKA_NAME"]} who is responding in a hostile, and concerned manner. {$GLOBALS["TEMPLATE_DIALOG"]}  $enforceLength" //lead to double answers 
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking in in a hostile and concerned manner{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} $enforceLength "
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is currently searching the area for hostiles, and asks who is there? ",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is currently searching the area for hostiles, and starts threatening what he's going to do when he finds them ",
    ]
];
$GLOBALS["PROMPTS"]["radiantsearchingfriend"]= [
    "cue"=>[
        //"write dialogue for {$GLOBALS["HERIKA_NAME"]} who is responding in a concerned manner.{$GLOBALS["TEMPLATE_DIALOG"]}  $enforceLength"
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking in a concerned manner{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} $enforceLength"
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is currently searching the area for hostiles, and starts a dialogue with their ally {$GLOBALS["target"]} about this topic ",
    ]
];
$GLOBALS["PROMPTS"]["radiantcombathostile"]= [
    "cue"=>[
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking in a hostile and combative manner{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} $enforceLength "
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is engaged in deadly combat with {$GLOBALS["target"]} and taunts them ",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is engaged in deadly combat with {$GLOBALS["target"]} and trash-talks them ",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is engaged in deadly combat with {$GLOBALS["target"]} and boasts about what they will do after {$GLOBALS["HERIKA_NAME"]} has defeated them ",
    ]
];
$GLOBALS["PROMPTS"]["radiantcombatfriend"]= [
    "cue"=>[
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking in a tense, serious manner{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} $enforceLength"
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is teamed up with {$GLOBALS["target"]} in deadly combat against someone and talks about the battle ",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is teamed up with {$GLOBALS["target"]} in deadly combat against someone and asks for help ",
    ]
];

if ($GLOBALS["gameRequest"][0] == "minai_combatendvictory" || $GLOBALS["gameRequest"][0] == "info_minai_combatendvictory") {
    $narratePrompt = "The Narrator: {$cleanedMessage}";
    $GLOBALS["PROMPTS"]["minai_combatendvictory"]= [
        "cue"=>[
            "{$inb}{$GLOBALS["HERIKA_NAME"]} comments about foes defeated{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$inb}{$GLOBALS["HERIKA_NAME"]} curses the defeated enemies{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$inb}{$GLOBALS["HERIKA_NAME"]} insults the defeated enemies with anger{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$inb}{$GLOBALS["HERIKA_NAME"]} makes a joke about the defeated enemies{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$inb}{$GLOBALS["HERIKA_NAME"]} makes a comment about the type of enemies that was defeated{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}",
            "{$inb}{$GLOBALS["HERIKA_NAME"]} notes something peculiar about last enemy defeated{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
        "player_request"=>[$narratePrompt],
        "extra"=>["dontuse"=>(time()%10!=0)]   //10% chance
    ];
}

if ($GLOBALS["gameRequest"][0] == "minai_bleedoutself" || $GLOBALS["gameRequest"][0] == "info_minai_bleedoutself") {
    $narratePrompt = "The Narrator: {$cleanedMessage}";
    $GLOBALS["PROMPTS"]["minai_bleedoutself"]= [
        "cue"=>[
            "{$inb}{$GLOBALS["HERIKA_NAME"]} is afraid and calls out for help after being badly wounded{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
            "{$inb}{$GLOBALS["HERIKA_NAME"]} panics and cries out in pain after being badly wounded{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
            "{$inb}{$GLOBALS["HERIKA_NAME"]} panics and screams in pain after being badly wounded{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
            "{$inb}{$GLOBALS["HERIKA_NAME"]} is terrified and begs for help after being injured.{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
            "{$inb}{$GLOBALS["HERIKA_NAME"]} expresses their resolve after being badly wounded hiding the pain{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        ],
        //"extra"=>["dontuse"=>(time()%10!=0)],   //10% chance
        "player_request"=>[$narratePrompt]
    ];
}

$GLOBALS["PROMPTS"]["goodmorning"]=[
    "cue"=>[
        (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] ? // ??? both are the same?
            "{$inb}{$GLOBALS["HERIKA_NAME"]} comment about {$GLOBALS["PLAYER_NAME"]}'s time asleep{$ine} {$GLOBALS["TEMPLATE_DIALOG"]})" : 
            "{$inb}{$GLOBALS["HERIKA_NAME"]} comment about {$GLOBALS["PLAYER_NAME"]}'s time asleep{$ine} {$GLOBALS["TEMPLATE_DIALOG"]})"
        )
    ],
    "player_request"=>[
        (ShouldUseDeviousNarrator() ? 
            (($questState = intval(GetActorValue($GLOBALS['PLAYER_NAME'], "deviouslyAccessibleGlobal"))) && 
             ($telvanniScore = ($questState % 10)) > 0 ? 
                (($eyescore = GetActorValue($GLOBALS['PLAYER_NAME'], "deviouslyAccessibleEyeScore")) > 10 ? 
                    "The Narrator: {$GLOBALS["PLAYER_NAME"]} woke up from sleeping. She had an intensely pleasurable dream in which she uncontrollably climaxed repeatedly. She looks content and relaxed." :
                    ($eyescore > 0 ? 
                        "The Narrator: {$GLOBALS["PLAYER_NAME"]} woke up from sleeping. She had a dream of constant stimulation without release. She looks aroused and frustrated." :
                        "The Narrator: {$GLOBALS["PLAYER_NAME"]} woke up from sleeping. She had a humiliating and degrading dream in which she was raped and humiliated. She looks ashamed and humiliated."
                    )
                ) : "{$GLOBALS["PLAYER_NAME"]} wakes up from sleeping. ahhhh"
            ) : "{$GLOBALS["PLAYER_NAME"]} wakes up from sleeping. ahhhh"
        )
    ],
    "extra" => (!empty($GLOBALS["RPG_COMMENTS"]) && in_array("sleep", $GLOBALS["RPG_COMMENTS"])) ? [] : ["dontuse" => true]
];

if (IsFollower($GLOBALS["HERIKA_NAME"])) {
    $GLOBALS["PROMPTS"]["minai_combatenddefeat"] = [
        "cue"=>[
            "{$inb}{$GLOBALS["HERIKA_NAME"]} laments after having been defeated in combat{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ]
    ];
} else {
    $GLOBALS["PROMPTS"]["minai_combatenddefeat"] = [
        "cue"=>[
            "{$inb}{$GLOBALS["HERIKA_NAME"]} gloats about defeating {$GLOBALS["target"]} in combat and boasts about what they will do next{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}"
        ]
    ];
}


//require_once(__DIR__."/prompts/chim_prompts.php");
include(__DIR__."/prompts/chim_prompts.php");

function SetInputPrompts($prompt) {
    minai_log("info", "Overriding input prompts for combat for {$GLOBALS["HERIKA_NAME"]}");
    $GLOBALS["PROMPTS"]["inputtext"] = $prompt;
    $GLOBALS["PROMPTS"]["inputtext_s"] = $prompt;
    $GLOBALS["PROMPTS"]["ginputtext"] = $prompt;
}

// Override default prompts for combat dialogue
if (isset($GLOBALS["gameRequest"]) && in_array(strtolower($GLOBALS["gameRequest"][0]), ["inputtext","inputtext_s","ginputtext"])) {
    $inCombat = IsEnabled($GLOBALS["HERIKA_NAME"], "inCombat");
    $hostile = IsEnabled($GLOBALS["HERIKA_NAME"], "hostileToPlayer");
    $combatPrompt = [
        "cue"=>[
            "{$GLOBALS["TEMPLATE_ACTION"]} {$inb}{$GLOBALS["HERIKA_NAME"]} is currently engaged in deadly combat and replies to {$GLOBALS["PLAYER_NAME"]}{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}"
        ]
        // Prompt is implicit
    ];
    $hostilePrompt = [
        "cue"=>[
            "{$GLOBALS["TEMPLATE_ACTION"]} {$inb}{$GLOBALS["HERIKA_NAME"]} is currently hostile to {$GLOBALS["PLAYER_NAME"]} and replies in a hostile manner to {$GLOBALS["PLAYER_NAME"]}{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}"
        ]
        // Prompt is implicit
    ];
    $hostileCombatPrompt = [
        "cue"=>[
            "{$GLOBALS["TEMPLATE_ACTION"]} {$inb}{$GLOBALS["HERIKA_NAME"]} is currently engaged in deadly combat against {$GLOBALS["PLAYER_NAME"]} and replies in a hostile manner to {$GLOBALS["PLAYER_NAME"]}{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}"
        ]
        // Prompt is implicit
    ];
    if ($hostile && $inCombat)
        SetInputPrompts($hostileCombatPrompt);
    elseif ($inCombat)
        SetInputPrompts($combatPrompt);
    elseif ($hostile)
        SetInputPrompts($hostilePrompt);
}

if (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    // Only set diary prompt if one is provided
    if (isset($GLOBALS["action_prompts"]["player_diary"]) && !empty($GLOBALS["action_prompts"]["player_diary"])) {
        // Get mind influence state and prompts
        $mindState = GetMindInfluenceState($GLOBALS["PLAYER_NAME"]);
        $mindPrompt = GetMindInfluencePrompt($mindState);

        $diaryPrompt = ExpandPromptVariables($GLOBALS["action_prompts"]["player_diary"]);
        
        // Add mind influence context if any exists
        if (!empty($mindPrompt)) {
            $diaryPrompt .= " " . $mindPrompt . " Write your diary entry reflecting your current mental state.";
        }

        $GLOBALS["PROMPTS"]["diary"] = [
            "cue"=>[$diaryPrompt],
            "extra"=>["force_tokens_max"=>0]
        ];
    }
}
else {
    // Only set diary prompt if one is provided
    if (isset($GLOBALS["action_prompts"]["follower_diary"]) && !empty($GLOBALS["action_prompts"]["follower_diary"])) {
        $GLOBALS["PROMPTS"]["diary"] = [
            "cue"=>[ExpandPromptVariables($GLOBALS["action_prompts"]["follower_diary"])],
            "extra"=>["force_tokens_max"=>0]
        ];
    }
}


if (isset($GLOBALS["minai_processing_input"]) && $GLOBALS["minai_processing_input"]) {
    error_log("PROCESSING INPUT");
    $cue = [];

    if ($GLOBALS["using_self_narrator"]) {
        $pronouns = GetActorPronouns($GLOBALS["PLAYER_NAME"]);
        $cue[] = "<instruction>Write a response as {$GLOBALS["PLAYER_NAME"]} thinking to {$pronouns["object"]}self about {$pronouns["object"]} most recent thought.</instruction> ";
    }
    $GLOBALS["PROMPTS"]["inputtext"] = [
        "cue"=>$cue,
        // Prompt is implicit
    ];
    $GLOBALS["PROMPTS"]["inputtext_s"] = [
        "cue"=>$cue,
        // Prompt is implicit
    ];
}

if ((!isset($GLOBALS["action_prompts"]["normal_scene"])) ||
    (!isset($GLOBALS["action_prompts"]["explicit_scene"])) ||
    (empty($GLOBALS["action_prompts"]))) {
    $GLOBALS["action_prompts"] = $GLOBALS["action_prompts_copy"];
    error_log("WARNING in prompts: CHIM made an attempt to disable MinAI action_prompts! ");
}

require_once(__DIR__."/prompts/info_tntr_prompts.php");
require_once(__DIR__."/prompts/info_fillherup_prompts.php");
require_once(__DIR__."/prompts/info_vibrator_prompts.php");
require_once(__DIR__."/prompts/info_narrate.php");

minai_stop_timer("prompts_php");
