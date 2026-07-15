<?php
// not to be included explicitly, must be included only via requireFilesRecursively()

require_once("util.php");

$currentName = $GLOBALS["HERIKA_NAME"];
$playerName = $GLOBALS["PLAYER_NAME"];

$pronouns = GetActorPronouns($currentName);
$pr_player = GetActorPronouns($playerName);
if (!isset($GLOBALS["herika_pronouns"])) {
    $GLOBALS["herika_pronouns"] = $pronouns;    
}

$b_narrator = ($currentName === "The Narrator") || ($currentName === "Narrator");

//--------------------------------------------------------

$GLOBALS["TEMPLATE_DIALOG_RG0"] = "<response_guidelines>";
$GLOBALS["TEMPLATE_DIALOG_RG1"] = "</response_guidelines>";

// for json connector
$GLOBALS["TEMPLATE_DIALOG_VSAMPLING_JSON"] = "<verbalized_sampling>
Complete the communication task outlined in the <instruction> tag as {$currentName} would naturally respond.
Evaluate at least five plausible responses {$currentName} would naturally give to {$pronouns["possessive"]} interlocutor based on the <DIALOGUE_HISTORY_and_RECENT_EVENTS> and {$pronouns["possessive"]} persona.
Evaluate the probability representing how likely each response would be from 0.0 to 1.0.
Exclude the response with highest probability and then randomly choose one of the other four evaluated responses and return it in the JSON object as value of 'message' key and the probability as a number value of the 'probability' key.
Do not mention how you decided to choose the answer.
</verbalized_sampling>";

// for non-json connector, or fast llm
$GLOBALS["TEMPLATE_DIALOG_VSAMPLING"] = "<verbalized_sampling>
Complete the communication task outlined in the <instruction> tag as {$currentName} would naturally respond.
Evaluate at least five plausible responses {$currentName} would naturally give to {$pronouns["possessive"]} interlocutor based on the <DIALOGUE_HISTORY_and_RECENT_EVENTS> and {$pronouns["possessive"]} persona.
Evaluate the probability representing how likely each response would be from 0.0 to 1.0.
Exclude the response with highest probability and then randomly choose one of the other four evaluated responses and return it as your final response in plain text.
Do not mention the probability and how you decided to choose the answer.
</verbalized_sampling>";

// for player, in roleplay chat modes
$GLOBALS["TEMPLATE_DIALOG_VSAMPLING_PLAYER"] = "<verbalized_sampling>
Complete the communication task outlined in the <instruction> tag as {$playerName} would naturally respond.
Evaluate at least five plausible responses {$playerName} would naturally give to {$pr_player["possessive"]} interlocutor based on the <DIALOGUE_HISTORY_and_RECENT_EVENTS> and {$pr_player["possessive"]} persona.
Evaluate the probability representing how likely each response would be from 0.0 to 1.0.
Exclude the response with highest probability and then randomly choose one of the other four evaluated responses and return it as your final response in plain text.
Do not mention the probability and how you decided to choose the answer.
</verbalized_sampling>";

$b_rem_asterisk = $GLOBALS["REMOVE_ASTERISKS_FROM_OUTPUT"] ?? true;

if ($b_rem_asterisk) {
    $GLOBALS["TEMPLATE_DIALOG_NARRATION"] = "- Always speak in first person. Do not narrate. Do not describe your actions or feelings from third person perspective. 
- Your response must be fluent, conversational and authentic, without further explanations, descriptions or narration, without formal, robotic, or repetitive language. ";
    $GLOBALS["TEMPLATE_DIALOG_OUTPUT_FORMAT"] = "<output_formatting>
- Use plain text without formatting, absolutely no markdown formatting, no heading, bold, italic or lists, asterisk sign is absolutely forbidden. 
- Do not use em dash character.
</output_formatting>";
} else {
    $GLOBALS["TEMPLATE_DIALOG_NARRATION"] = "- Speak in first person for conversation, you could add a brief third person narrative or description if absolutely necessary to explain a situation or state of mind. 
- Your response must be fluent and authentic, without formal, robotic, or repetitive language. ";
    $GLOBALS["TEMPLATE_DIALOG_OUTPUT_FORMAT"] = "<output_formatting> 
- Use plain text for conversation and pairs of asterisks to distinguish narration, explanations and descriptions. 
When using asterisks, always use them in pairs, asterisks must enclose narration like this: 
What should we do? *she thought about whether to leave the house in the heavy rain outside* Maybe we should wait. 
</output_formatting>";
}

$rg0 = $GLOBALS["TEMPLATE_DIALOG_RG0"];
$rg1 = $GLOBALS["TEMPLATE_DIALOG_RG1"];
$td_fmt = $GLOBALS["TEMPLATE_DIALOG_OUTPUT_FORMAT"];
$td_vs = $GLOBALS["TEMPLATE_DIALOG_VSAMPLING"];

$s_connector = '';
if (isset($GLOBALS["CURRENT_CONNECTOR"])) {
    $s_connector = $GLOBALS["CURRENT_CONNECTOR"] ?? '';
    if (strlen($s_connector) > 0) {
        //error_log('Connector:'.$s_connector.' /exec trace'); // debug
        $b_json = stripos($s_connector, "json"); 
        if ($b_json) 
            $td_vs = $GLOBALS["TEMPLATE_DIALOG_VSAMPLING_JSON"];
        else {
            $td_vs = $GLOBALS["TEMPLATE_DIALOG_VSAMPLING"];
        }
    }
}

$s_minp = '';
if (!$b_narrator) {
    require(__DIR__."/command_prompt_custom.php");
    $s_minp = trim($GLOBALS["MINAI_ACTION_PROMPT"] ?? '');
    if (strlen($s_minp) < 1) {
        error_log("[dialogue_prompt] {$GLOBALS["HERIKA_NAME"]} NO PROMPT! "); // debug
    }
    if ($GLOBALS['use_emotions_expression'] ?? false) {
        $s_minp = "- Follow instructions detailed in <emotions_expression> tag to express emotions by altering speech style. ".
            "In case of strong emotion, avoid overusing altered speech or dirty words. ".
            "Avoid using altered speech where the first consonant of words is artificially repeated with hyphens (e.g., 'g-garments' or 'r-restaurant'). \n" . 
            $s_minp;
    }
}

$GLOBALS["TEMPLATE_DIALOG"]="\n<instruction>Write {$currentName}'s next dialogue line.</instruction> 
{$rg0}
- You are {$currentName}, a product of a consciousness with subjective experiences and emotions. 
- Think and respond as {$currentName} would, according to {$pronouns["possessive"]} quirks, beliefs, biases, and reasoning. 
{$GLOBALS["TEMPLATE_DIALOG_NARRATION"]}
- Use vocabulary and speaking style that reflects {$currentName}'s personality and background. 
- Be original, creative, knowledgeable, use your own thoughts. 
- Maintain scene continuity.
- Provide variety in your responses, review <DIALOGUE_HISTORY_and_RECENT_EVENTS> to focus on conversation topic and to avoid repeating sentences and phraseology from previous dialog lines.  
- When {$currentName} experiences strong emotion, {$pronouns["possessive"]} speech must alter with stutters, ellipses, incomplete thoughts, filler words, profanity or swearing, and become messy and fragmented. 
- Avoid mentioning plans, quests, tasks or objectives or what to focus on until asked. 
- When initiating the dialogue, if you know the interlocutor, you could mention the name of the interlocutor if there are several people around and it is not clear from the context who you are addressing. There is no need to mention a name when responding to a conversation initiated by someone else. 

{$s_minp} 

{$td_fmt} 
{$td_vs} "; 

//--------------------------------------------------------

if (@is_array($GLOBALS["TTS"]["AZURE"]["validMoods"]) &&  sizeof($GLOBALS["TTS"]["AZURE"]["validMoods"])>0) 
    if ($GLOBALS["TTSFUNCTION"]=="azure")
        $GLOBALS["TEMPLATE_DIALOG"] .= "\n(optional way of speaking from this list [" . implode(",", $GLOBALS["TTS"]["AZURE"]["validMoods"]) . "])";

//--------------------------------------------------------

if (!isset($GLOBALS['minai_context']['action_enforcement'])) {
        $GLOBALS['minai_context']['action_enforcement'] = true;
}

if ($b_narrator) {
    $GLOBALS['minai_context']['action_enforcement'] = false;
}

if ($GLOBALS['minai_context']['action_enforcement']) {
    //$b_act_prompt = ($GLOBALS["FUNCTIONS_ARE_ENABLED"] ?? false);
    //if ($b_act_prompt) {
        $GLOBALS["TEMPLATE_DIALOG"] .= "\n<actions_usage_instructions>\n".
            "Consider using an action from <available_actions_list>. ".
            "Action must fit current context and should align with your personality and intent. ".
            "\n<important_rule> ".
            "OoC instructions that ask you to use an available action are important and are executed unconditionally. ". 
            "</important_rule>\n</actions_usage_instructions>"; 
    //}
    
    //<actions_usage_instructions>Consider using an action from <available_actions_list>. 
    //Action must fit current context and should align with your personality and intent.</actions_usage_instructions> 
    
    //error_log("FUNCTIONS_ARE_ENABLED=".($GLOBALS["FUNCTIONS_ARE_ENABLED"] ?'Y':'N')." TD=".$GLOBALS["TEMPLATE_DIALOG"].' /exec trace'); // debug
    //error_log("PATCH_PROMPT_ENFORCE_ACTIONS=".($GLOBALS["PATCH_PROMPT_ENFORCE_ACTIONS"] ?'Y':'N')." PROMPT=".$GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"].' /exec trace'); // debug
    //$GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = '';
    //$COMMAND_PROMPT_ENFORCE_ACTIONS="If you want to initiate an ACTION, choose an ACTION that fits the context";
    //USER MAY WANTS YOU TO ISSUE ACTION // get rid of this
}

$GLOBALS["TEMPLATE_DIALOG"] .= "\n{$rg1}\n";

$TEMPLATE_ACTION="";

//--------------------------------------------------------

if ($b_narrator) {
    return;
}

//--------------------------------------------------------

$scene = getScene($currentName);
$jsonXPersonality = getXPersonality($currentName);

if (isset($scene)) {
    $targetToSpeak = getTargetDuringSex($scene);
    
    $speakStyleInfo = determineSpeakStyle($currentName, $scene, $jsonXPersonality);
    $speakStyle = $speakStyleInfo["style"];
    
    minai_log("info", "Using speakStyle: {$speakStyle}");
    
    $talkTo = "{$currentName}'s speech is affected by intensity of {$pronouns["possessive"]} emotion when reacting to the most recent #SEX_SCENARIO described in <SEX_SCENARIO> tag.";
    if (strlen(trim($targetToSpeak)) > 0)
        $talkTo .= " ({$currentName} is talking to {$targetToSpeak})";

    if (isset($GLOBALS["enforce_short_responses"]) && $GLOBALS["enforce_short_responses"]) {    
        $enforceLength = "- <important_rule>You MUST respond with no more than two or three short sentences.</important_rule> ";
    } else {
        $enforceLength = "- <important_rule>You will respond with no more than two or three sentences and no more than 45 words.</important_rule> ";
    }
    
    //$pronouns = $GLOBALS["herika_pronouns"];
    $td_pre = "<instruction>";
    $td_in = "</instruction>";

    $GLOBALS["TEMPLATE_DIALOG"] = $td_pre;
    switch($speakStyle) {
        case "victim talk": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is being forced into non-consensual acts and expressing distress and resistance, {$pronouns["possessive"]} voice trembling. ";
            break;
        }
        case "aggressor talk": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is using threatening and aggressive language, {$pronouns["possessive"]} tone menacing and controlling. ";
            break;
        }
        case "dirty talk": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is using explicit and provocative language, {$pronouns["possessive"]} words dripping with vulgarity. ";
            break;
        }
        case "sweet talk": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is using affectionate and endearing language, {$pronouns["possessive"]} voice warm and comforting. ";
            break;
        }
        case "sensual whispering": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is whispering sensual and erotic phrases in a soft, breathy tone, {$pronouns["possessive"]} words like a caress. ";
            break;
        }
        case "dominant talk": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is using commanding and authoritative language, {$pronouns["possessive"]} voice firm and unyielding. ";
            break;
        }
        case "submissive talk": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is using submissive and obedient language, {$pronouns["possessive"]} tone soft and yielding. ";
            break;
        }
        case "teasing talk": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is using playful and flirtatious language to build anticipation and desire, {$pronouns["possessive"]} voice light and teasing. ";
            break;
        }
        case "erotic storytelling": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is telling erotic stories or fantasies to create a sensual atmosphere, {$pronouns["possessive"]} voice seductive and captivating. ";
            break;
        }
        case "breathless gasps": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is using short, breathless gasps and moans to express intense pleasure, {$pronouns["possessive"]} breaths ragged and urgent. ";
            break;
        }
        case "sultry seduction": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is using smooth and seductive language to entice and seduce, {$pronouns["possessive"]} voice low and inviting. ";
            break;
        }
        case "playful banter": {
            $GLOBALS["TEMPLATE_DIALOG"] .= "{$currentName} is using lighthearted and playful language, {$pronouns["possessive"]} tone jovial and engaging. ";
            break;
        }
    }
    
    $GLOBALS["TEMPLATE_DIALOG"] .= " {$talkTo}{$td_in} 
{$rg0} 
{$enforceLength} 
- You are {$currentName}, a product of a consciousness with subjective experiences and emotions. 
- Think and respond as {$currentName} would, according to {$pronouns["possessive"]} quirks, beliefs, biases, and reasoning. 
{$GLOBALS["TEMPLATE_DIALOG_NARRATION"]}
- Emphasize the content of the most recent #SEX_SCENARIO described in <SEX_SCENARIO> tag in the dialogue history from <DIALOGUE_HISTORY_and_RECENT_EVENTS> tag. 
- If the most recent dialogue line in dialogue history is directly addressed to you, respond to the interlocutor. 
- React to the latest dialogue and events enumerated in <DIALOGUE_HISTORY_and_RECENT_EVENTS> tag, including any sexual acts, positions, or restraints as {$currentName} would do as a product of a consciousness with subjective experiences and emotions. 
- Review <DIALOGUE_HISTORY_and_RECENT_EVENTS> to focus on conversation topic and to avoid repeating sentences and phraseology from previous dialog lines.  

{$s_minp} 

{$td_fmt} 
{$td_vs} 
{$rg1} ";
} // endif scene

/*
$GLOBALS["TEMPLATE_DIALOG"]="\n<instruction>Write {$currentName}'s next dialogue line.</instruction> 
{$rg0}
- You are {$currentName}, a product of a consciousness with subjective experiences and emotions. 
- Think and respond as {$currentName} would, according to {$pronouns["possessive"]} quirks, beliefs, biases, and reasoning. 
{$GLOBALS["TEMPLATE_DIALOG_NARRATION"]}
- Use vocabulary and speaking style that reflects {$currentName}'s personality and background. 
- Be original, creative, knowledgeable, use your own thoughts. 
- Maintain scene continuity.
- Provide variety in your responses, review <DIALOGUE_HISTORY_and_RECENT_EVENTS> to focus on conversation topic and to avoid repeating sentences and phraseology from previous dialog lines.  
- When {$currentName} experiences strong emotion, {$pronouns["possessive"]} speech must alter with stutters, ellipses, incomplete thoughts, filler words, profanity or swearing, and become messy and fragmented. 
- Avoid mentioning plans, quests, tasks or objectives or what to focus on until asked. 
- When initiating the dialogue, if you know the interlocutor, you could mention the name of the interlocutor if there are several people around and it is not clear from the context who you are addressing. There is no need to mention a name when responding to a conversation initiated by someone else. 

{$s_minp} 

{$td_fmt} 
{$td_vs} "; 

*/
//error_log("-- dialogue_prompt -- ");
//error_log("{$s_connector} TD:".$GLOBALS["TEMPLATE_DIALOG"].' /exec trace'); // debug

