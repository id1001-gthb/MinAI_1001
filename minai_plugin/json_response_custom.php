<?php

// Avoid processing for fast / storage events
//if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
//    return;
//}

require_once(__DIR__."/config.php");
require_once(__DIR__."/globals.php");

// add emotions
if ($GLOBALS['use_emotions_expression']) {
    if (!array_key_exists("emotion", $GLOBALS["responseTemplate"])) {
        $GLOBALS["responseTemplate"]["emotion"] = 
		"calm|surprised|aroused|desire|love|happy|amusement|gratitude|proud|anxious|fearful|panic|grieving|envious|jealous|sad|disappointed|ashamed|angry|offended|disgusted|sarcastic";
    }
    if (!array_key_exists("emotion_intensity", $GLOBALS["responseTemplate"])) {
        $GLOBALS["responseTemplate"]["emotion_intensity"] = "low|moderate|strong";
    }
    /*    
    $GLOBALS["responseTemplate"] = array_merge($GLOBALS["responseTemplate"], [
        //"emotion" => "calm|arousal|desire|love|happy|gratitude|pride|fear|apprehension|panic|anxiety|grief|envy|jealousy|disappointment|shame|embarrassment|anger|rage|resentment|disgust",
        "emotion" => "calm|surprised|aroused|desire|love|happy|amusement|gratitude|proud|anxious|fearful|panic|grieving|envious|jealous|sad|disappointed|ashamed|angry|offended|disgusted|sarcastic", 
        "emotion_intensity" => "low|moderate|strong"
    ]); 
    //inworld tts: [happy], [sad], [angry], [surprised], [fearful], [disgusted]
    */
    
    $crt_moods = trim($GLOBALS["responseTemplate"]["mood"] ?? ""); 
    if ($crt_moods == "")
        $crt_moods = "default|neutral|calm|assisting|assertive|playful|delighted|sexy|amused|kindly|lovely|seductive|smug|sassy|sarcastic|sardonic|smirking".
                     "|irritated|teasing|mocking|bored|curious|confident|courageous|content|angry|belligerent|anxious|fearful|sad|gloomy|drunk|high|sober".
                     "|desperate|distressed|pleading";
    else {
        if (strpos($crt_moods, "default") === false) 
           $crt_moods .= "|default";
        if (strpos($crt_moods, "neutral") === false) 
           $crt_moods .= "|neutral";
        if (strpos($crt_moods, "calm") === false) 
           $crt_moods .= "|calm";
        if (strpos($crt_moods, "assisting") === false) 
           $crt_moods .= "|assisting";
        if (strpos($crt_moods, "assertive") === false) 
           $crt_moods .= "|assertive";
        if (strpos($crt_moods, "playful") === false) 
           $crt_moods .= "|playful";
        if (strpos($crt_moods, "delighted") === false) 
           $crt_moods .= "|delighted";
        if (strpos($crt_moods, "sexy") === false) 
           $crt_moods .= "|sexy";
        if (strpos($crt_moods, "amused") === false) 
           $crt_moods .= "|amused";
        if (strpos($crt_moods, "kindly") === false) 
           $crt_moods .= "|kindly";
        if (strpos($crt_moods, "lovely") === false) 
           $crt_moods .= "|lovely";
        if (strpos($crt_moods, "seductive") === false) 
           $crt_moods .= "|seductive";
        if (strpos($crt_moods, "smug") === false) 
           $crt_moods .= "|smug";
        if (strpos($crt_moods, "sassy") === false) 
           $crt_moods .= "|sassy";
        if (strpos($crt_moods, "sarcastic") === false) 
           $crt_moods .= "|sarcastic";
        if (strpos($crt_moods, "sardonic") === false) 
           $crt_moods .= "|sardonic";
        if (strpos($crt_moods, "smirking") === false) 
           $crt_moods .= "|smirking";
        if (strpos($crt_moods, "irritated") === false) 
           $crt_moods .= "|irritated";
        if (strpos($crt_moods, "teasing") === false) 
           $crt_moods .= "|teasing";
        if (strpos($crt_moods, "mocking") === false) 
           $crt_moods .= "|mocking";
        if (strpos($crt_moods, "bored") === false) 
           $crt_moods .= "|bored";
        if (strpos($crt_moods, "curious") === false) 
           $crt_moods .= "|curious";
        if (strpos($crt_moods, "confident") === false) 
           $crt_moods .= "|confident";
        if (strpos($crt_moods, "courageous") === false) 
           $crt_moods .= "|courageous";
        if (strpos($crt_moods, "content") === false) 
           $crt_moods .= "|content";
        if (strpos($crt_moods, "angry") === false) 
           $crt_moods .= "|angry";
        if (strpos($crt_moods, "belligerent") === false) 
           $crt_moods .= "|belligerent";
        if (strpos($crt_moods, "anxious") === false) 
           $crt_moods .= "|anxious";
        if (strpos($crt_moods, "fearful") === false) 
           $crt_moods .= "|fearful";
        if (strpos($crt_moods, "sad") === false) 
           $crt_moods .= "|sad";
        if (strpos($crt_moods, "gloomy") === false) 
           $crt_moods .= "|gloomy";
        if (strpos($crt_moods, "drunk") === false) 
           $crt_moods .= "|drunk";
        if (strpos($crt_moods, "high") === false) 
           $crt_moods .= "|high";
        if (strpos($crt_moods, "sober") === false) 
           $crt_moods .= "|sober";
        if (strpos($crt_moods, "desperate") === false) 
           $crt_moods .= "|desperate";
        if (strpos($crt_moods, "distressed") === false) 
           $crt_moods .= "|distressed";
        if (strpos($crt_moods, "pleading") === false) 
           $crt_moods .= "|pleading";
    }
    
  /*
  $EMOTEMOODS="sassy,"
    . "assertive,"
    . "sexy,"
    . "smug,"
    . "kindly,"
    . "lovely,"
    . "seductive,"
    . "sarcastic,"
    . "sardonic,"
    . "smirking,"
    . "amused,"
    . "default,"
    . "assisting,"
    . "irritated,"
    . "playful,"
    . "neutral,"
    . "teasing,"
    . "mocking"
    
    . "desperate"
    . "distressed"
    . "pleading"
    . "sad"; //List of moods passed to LLM (comma separated). Triggers animations if enabled.

  */  
   
    $GLOBALS["responseTemplate"]["mood"] = $crt_moods;
}

$GLOBALS["responseTemplate"]["message"] = "{$GLOBALS["HERIKA_NAME"]}'s response as lines of dialogue in plain text without formatting";
$GLOBALS["responseTemplate"]["target"] = "the Name of the character who is the target of the action or the Name of the destination location if the action is a movement action";
$GLOBALS["responseTemplate"]["listener"] = "specify the Name of the character who {$GLOBALS["HERIKA_NAME"]} is directly talking to";
$GLOBALS["responseTemplate"]["item"] = "Item Name when using GiveItemTo or PickupItem actions, Spell Name when using action CastSpell, amount of gold written as number with single quotes (like '50') when using action GiveGoldTo"; 
//"item"=>"item name (REQUIRED when action is GiveItemTo or PickupItem or CastSpell - use exact item name from inventory or spell name from spells) OR amount of gold (REQUIRED when action is GiveGoldTo - number as string, e.g. '50')"

if (!array_key_exists("probability", $GLOBALS["responseTemplate"])) {
    $GLOBALS["responseTemplate"]["probability"] = "number in 0.0 - 1.0 interval";
}

/*
if (IsEnabled($GLOBALS["PLAYER_NAME"], "isSinging")) {
    $moods=explode(",",$GLOBALS["EMOTEMOODS"]);
    shuffle($moods);
    $pronouns = $GLOBALS["player_pronouns"];
    $GLOBALS["responseTemplate"] = [
        "character"=>$GLOBALS["PLAYER_NAME"],
        "listener"=>"{$GLOBALS['PLAYER_NAME']} is singing to those around {$pronouns['object']}",
        "message"=>"lines of dialogue",
        "mood"=>implode("|",$moods),
        "action"=>implode("|",$GLOBALS["FUNC_LIST"]),
        "target"=>"action's target|destination name",
        "lang"=>"en|es",
        "response_tone_happiness"=>"Value from 0-1",
        "response_tone_sadness"=>"Value from 0-1",
        "response_tone_disgust"=>"Value from 0-1",
        "response_tone_fear"=>"Value from 0-1",
        "response_tone_surprise"=>"Value from 0-1",
        "response_tone_anger"=>"Value from 0-1",
        "response_tone_other"=>"Value from 0-1",
        "response_tone_neutral"=>"Value from 0-1"
    ];
}
else*/

if (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    $pronouns = $GLOBALS["player_pronouns"];
    $GLOBALS["responseTemplate"]["character"] = IsExplicitScene() ? $GLOBALS["PLAYER_NAME"] . "'s body" : $GLOBALS["PLAYER_NAME"] . "'s subconscious";
    $GLOBALS["responseTemplate"]["listener"] = IsExplicitScene()
        ? "{$GLOBALS['PLAYER_NAME']} is reacting to physical sensations"
        : "{$GLOBALS['PLAYER_NAME']} is thinking to {$pronouns['object']}self";
    
    // Only include response tones if TTSFUNCTION is zonos_gradio
    if (zonosIsActive()) {
        $GLOBALS["responseTemplate"]["response_tone_happiness"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_sadness"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_disgust"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_fear"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_surprise"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_anger"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_other"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_neutral"] = "Value from 0-1";
    }
}

$GLOBALS["ENFORCE_ACTIONS_PROMPT"] = true;
$GLOBALS["PATCH_PROMPT_ENFORCE_ACTIONS"] = true;


