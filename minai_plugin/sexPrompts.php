<?php

require_once(__DIR__."/util.php");
require_once(__DIR__."/speakStylesPrompts/dirtyTalk.php");
require_once(__DIR__."/speakStylesPrompts/sweetTalk.php");
require_once(__DIR__."/speakStylesPrompts/sensualWhispering.php");
require_once(__DIR__."/speakStylesPrompts/playfulBanter.php");
require_once(__DIR__."/speakStylesPrompts/sultrySeduction.php");
require_once(__DIR__."/speakStylesPrompts/breathlessGasps.php");
require_once(__DIR__."/speakStylesPrompts/eroticStorytelling.php");
require_once(__DIR__."/speakStylesPrompts/teasingTalk.php");
require_once(__DIR__."/speakStylesPrompts/dominantTalk.php");
require_once(__DIR__."/speakStylesPrompts/submissiveTalk.php");
require_once(__DIR__."/speakStylesPrompts/victimTalk.php");
require_once(__DIR__."/speakStylesPrompts/aggressorTalk.php");

$HerikaName = $GLOBALS["HERIKA_NAME"];
$currentName = strtolower($HerikaName);

if (($currentName === "the narrator") || ($currentName === "narrator"))  {
    return;
}

$scene = getScene($HerikaName);
$bIsInScene = (isset($scene) && (!empty($scene)));

/*Function IsInScene($name) {
    $scene = getScene($name);
    return (isset($scene) && (!empty($scene)));
}*/

// Add debug logging for scene data
// minai_log("info", "Scene data: " . json_encode($scene));

// Initialize sex scene context with scene data
$GLOBALS["SEX_SCENE_CONTEXT"] = [
    "victimActors" => isset($scene["victim_actors"]) ? $scene["victim_actors"] : null,
    "femaleActors" => isset($scene["female_actors"]) ? $scene["female_actors"] : null,
    "maleActors" => isset($scene["male_actors"]) ? $scene["male_actors"] : null,
    "framework" => isset($scene["framework"]) ? $scene["framework"] : "",
    "threadId" => isset($scene["thread_id"]) ? $scene["thread_id"] : null,
    "scene" => isset($scene["curr_scene_id"]) ? $scene["curr_scene_id"] : "",
    "description" => isset($scene["description"]) ? $scene["description"] : "",
    "fallback" => isset($scene["fallback"]) ? $scene["fallback"] : ""
];

if($bIsInScene){

    $jsonXPersonality = getXPersonality($HerikaName);
    addXPersonality($jsonXPersonality);

    setDirtyTalkPrompts($HerikaName); //default prompts when speak style is not found 

    $targetToSpeak = getTargetDuringSex($scene) ?? "";
    $gender = GetGender($HerikaName);
       
    $speakStyleInfo = determineSpeakStyle($HerikaName, $scene, $jsonXPersonality);
    $speakStyle = strtolower(trim($speakStyleInfo["style"]));

    minai_log("info", "Setting sex speak style: $speakStyle. Role: {$speakStyleInfo["role"]}");
    //error_log(" $HerikaName use $speakStyle with $targetToSpeak - exec trace "); // debug 
    
    switch($speakStyle) {
        case "victim talk": {
            setVictimTalkPrompts($HerikaName);
            break;
        }
        case "aggressor talk": {
            setAggressorTalkPrompts($HerikaName);
            break;
        }
        case "dirty talk": {
            setDirtyTalkPrompts($HerikaName);
            break;
        }
        case "sweet talk": {
            setSweeTalkPrompts($HerikaName);
            break;
        }
        case "sensual whispering": {
            setSensualWhisperingPrompts($HerikaName);
            break;
        }
        case "dominant talk": {
            setDominantTalkPrompts($HerikaName);
            break;
        }
        case "submissive talk": {
            setSubmissiveTalkPrompts($HerikaName);
            break;
        }
        case "teasing talk": {
            setTeasingTalkPrompts($HerikaName);
            break;
        }
        case "erotic storytelling": {
            setEroticStorytellingPrompts($HerikaName);
            break;
        }
        case "breathless gasps": {
            setBreathlessGaspsPrompts($HerikaName);
            break;
        }
        case "sultry seduction": {
            setSultrySeductionPrompts($HerikaName);
            break;
        }
        case "playful banter": {
            setPlayfulBanterPrompts($HerikaName);
            break;
        }
        default: {
            $i_rnd = rand(1, 4);
            if ($gender == 'female') {
                if ($i_rnd == 1) 
                    setSweeTalkPrompts($HerikaName);
                elseif ($i_rnd == 2)
                    setPlayfulBanterPrompts($HerikaName);
                elseif ($i_rnd == 3)
                    setSubmissiveTalkPrompts($HerikaName);
                else
                    setDirtyTalkPrompts($HerikaName);
            } elseif ($gender == 'male') { 
                if ($i_rnd == 1) 
                    setDominantTalkPrompts($HerikaName);
                elseif ($i_rnd == 2)
                    setBreathlessGaspsPrompts($HerikaName);
                else
                    setDirtyTalkPrompts($HerikaName);
            }
        }
    }
} else {
    // speaker not in scene, replace prompt with something else
    $s_type = $GLOBALS["gameRequest"][0] ?? ""; 
    $s_prompt = $GLOBALS["gameRequest"][3] ?? "";
    //error_log("[sexPrompts] npc={$HerikaName} type={$s_type} prompt={$s_prompt} - dbg"); //debug

    $s_def_prompt = "<instruction>{$HerikaName} replies to interlocutor. Pay attention to what the interlocutor is saying and respond directly and to the point.</instruction> {$GLOBALS["TEMPLATE_DIALOG"]}";
    $i_random = rand(1, 7); // to lower the probability of some cues
    if ($i_random == 1) {
        $s_def_prompt = "<instruction>{$HerikaName} replies to interlocutor and change focus to the sex scene unfolding nearby. </instruction> {$GLOBALS["TEMPLATE_DIALOG"]}";
        //error_log("[sexPrompts] npc={$HerikaName} type={$s_type} prompt={$s_prompt} - dbg"); //debug
    }    
    
    $GLOBALS["PROMPTS"]["sextalk_climaxchastity"] = ["cue" => [$s_def_prompt]];
    $GLOBALS["PROMPTS"]["sextalk_climax"] = ["cue" => [$s_def_prompt]];
    $GLOBALS["PROMPTS"]["sextalk_scenechange"] = ["cue" => [$s_def_prompt]];
    $GLOBALS["PROMPTS"]["sextalk_speedincrease"] = ["cue" => [$s_def_prompt]];
    $GLOBALS["PROMPTS"]["sextalk_speeddecrease"] = ["cue" => [$s_def_prompt]];
    $GLOBALS["PROMPTS"]["sextalk_end"] = ["cue" => [$s_def_prompt]];
    $GLOBALS["PROMPTS"]["sextalk_ambient"] = ["cue" => [$s_def_prompt]];
    //$GLOBALS["PROMPTS"]["sextalk_"] = ["cue" => [$s_def_prompt]];
}
