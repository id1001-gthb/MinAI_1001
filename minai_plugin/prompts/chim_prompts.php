<?php

//error_log(" chim_prompts.php - exec trace"); // debug

//---------------------------------------------------------------------
$HERIKA = $GLOBALS["HERIKA_NAME"];
$PLAYER = $GLOBALS["PLAYER_NAME"];

$DEFAULT_TEMPLATE_DIALOG = $GLOBALS["TEMPLATE_DIALOG"]; // copy of default template, need this for rechat

$MY_TEMPLATE_DIALOG = $DEFAULT_TEMPLATE_DIALOG; // use default or adjust at will

$MY_TEMPLATE_DIALOG_ASK = " <instruction>{$HERIKA} is asking a question.</instruction> {$MY_TEMPLATE_DIALOG}"; 

// template dialog modifiers:
$MY_TEMPLATE_DIALOG_TELLING = " \n<instruction>{$HERIKA} will tell an engaging suspense-full story with a plot twist.</instruction> "; 
$MY_TEMPLATE_DIALOG_FICTION  = " \n<instruction>{$HERIKA} will imagine a creative fictional narrative.</instruction> "; 
$MY_TEMPLATE_DIALOG_STORY = " \n<instruction>{$HERIKA} will imagine a creative fictional engaging suspense-full story with unexpected conclusion.</instruction> "; 

// template additions, don't use contradicting styles for same cue:
$STORY_STYLE_LORE = " \nStrictly adhere to Skyrim lore. ";

$STORY_STYLE_MINIMAL = " \nWrite no more than three short simple sentences. "; 
$STORY_STYLE_SHORT = " \nWrite no more than five short paragraphs. "; 
$STORY_STYLE_VERBOSE = " \nWrite at least five paragraphs. "; 

$STORY_STYLE_DIRECT = " \nUse direct casual language, write simple sentences, avoid adjectives. "; 
$STORY_STYLE_DETAIL = " \nWrite engaging details. "; 
$STORY_STYLE_ORNATE = " \nUse verbose elaborate style with allegories and metaphors. "; 

$STORY_STYLE_MTWAIN = " \nWrite text recreating Mark Twain style and tone. "; 
$STORY_STYLE_EHEMINGWAY = " \nWrite text recreating Ernest Hemingway style and tone. "; 
$STORY_STYLE_JJOYCE = " \nWrite text recreating James Joyce style and tone. "; 
$STORY_STYLE_JGRISHAM = " \nWrite text recreating John Grisham style and tone. "; 
$STORY_STYLE_SKING = " \nWrite text recreating Stephen King style and tone. "; 

$STORY_STYLE_VOCAB_SIMPLE = " \nUse simple mundane vocabulary. "; 
$STORY_STYLE_VOCAB_COMPLEX = " \nUse complex elevated vocabulary. "; 

$USE_NSFW = (!($GLOBALS["disable_nsfw"] ?? false));
//$SEX_ENABLED = ShouldEnableSexFunctions($HERIKA);
	
$herika_gender = GetGender($HERIKA);
$herika_prns = GetActorPronouns($HERIKA);
$herika_she = $herika_prns['subject'];
$herika_her = $herika_prns['possessive'];

$player_gender = GetGender($PLAYER);
$player_prns = GetActorPronouns($PLAYER);
$player_he = $player_prns['subject'];
$player_his = $player_prns['possessive'];

$inb = "<instruction>(";
$ine = ".)</instruction>";

//---------------------------------------------------------------------
//error_log("{$HERIKA} USE_NSFW={$USE_NSFW} - exec trace "); // debug
//---------------------------------------------------------------------
// CHIM bored events:
//---------------------------------------------------------------------

if (!isset($GLOBALS["BORED_EVENT"]))
	$GLOBALS["BORED_EVENT"] = 50;

if (!(isset($GLOBALS["PROMPTS"]["bored"]["cue"]))) {
	$GLOBALS["PROMPTS"]["bored"]["cue"] = [];
}

if (!(isset($GLOBALS["BORED_EVENT_SERVERSIDE"]))) {
	$GLOBALS["BORED_EVENT_SERVERSIDE"] = false;
}

if ((IsRadiant()) || (IsSexActive())) {
	//error_log(" Radiant - exec trace "); //debug
	$GLOBALS["BORED_EVENT_SERVERSIDE"] = false; // MinAI radiant will suspend CHIM bored ss event
}

/*
$i_rnd = rand(1, 100);
$i_bored = intval($GLOBALS["BORED_EVENT"] ?? 50);
$b_use = $i_rnd <= $i_bored;

$GLOBALS["PROMPTS"]["bored"]["extra"]["dontuse"] = !$b_use; //["dontuse" => (rand(1, 100) <= intval($GLOBALS["BORED_EVENT"]))];  
error_log(" bored r=$i_rnd b=$i_bored dontuse=".((!$b_use) ? "Y" : "N")); // debug
*/

//if (!$GLOBALS["BORED_EVENT_SERVERSIDE"]) {

	$GLOBALS["PROMPTS"]["bored"]["cue"] = [ 
        //"write dialogue for {$GLOBALS["HERIKA_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} " ////'write' prefix lead to double answers, TEMPLATE_DIALOG already has a "Write ..." => the result is "write dialogue ... Write next line"
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking about a relevant topic mentioned in <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking about a RECENT EVENT from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking about an intriguing RECENT EVENT from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking about an intriguing topic mentioned in DIALOGUE HISTORY{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking about a topic that was not mentioned in <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$inb}{$GLOBALS["HERIKA_NAME"]} is speaking{$ine} {$GLOBALS["TEMPLATE_DIALOG"]} "
    ];


	$more_cues = [ 
		"{$inb}{$HERIKA} start dialogue about a topic inspired from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$MY_TEMPLATE_DIALOG}",
		//from original cues
		//"{$inb}{$HERIKA} start dialogue about the last goal completed{$ine} {$MY_TEMPLATE_DIALOG}",
		//"{$inb}{$HERIKA} start dialogue about the last quest completed{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about the current location{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about the current weather{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about today RECENT EVENTS{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about what's on {$herika_her} mind{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about divinity{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about how {$herika_she} currently feel{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} remembers an important historical event from the past{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about something {$herika_she} like{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about something {$herika_she} dislike{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about a recent rumor{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about something that happened in {$PLAYER}'s past{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about current thoughts about {$PLAYER}{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about a random character present nearby{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about {$herika_her} thoughts on the recent events{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about something {$herika_she} find hard to explain{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the last combat{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the current ambiance{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about a nearby creature{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about a nearby character{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the current location{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about a feeling of 'déjà vu'{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} likes about current location{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} dislikes about current location{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about the dangers around{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the state of {$herika_her} gear or supplies{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about random topic{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about random topic related to RECENT EVENTS{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about random topic related to DIALOGUE HISTORY{$ine} {$MY_TEMPLATE_DIALOG}",
		// --- story
		"{$inb}{$HERIKA} tell a strange story{$ine} {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_SHORT}",
		"{$inb}{$HERIKA} tell a frightening story{$ine} {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_DIRECT} {$STORY_STYLE_SHORT} {$STORY_STYLE_LORE}",
		"{$inb}{$HERIKA} tell a story where forces of good prevail{$ine} {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_LORE} {$STORY_STYLE_DETAIL}",
		"{$inb}{$HERIKA} tell a story where forces of good prevail{$ine} {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_LORE} {$STORY_STYLE_EHEMINGWAY}",
		"{$inb}{$HERIKA} tell a story where forces of evil prevail{$ine} {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_DETAIL} {$STORY_STYLE_ORNATE}",
		"{$inb}{$HERIKA} tell a story where forces of evil prevail{$ine} {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_DETAIL} {$STORY_STYLE_JJOYCE}",
		"{$inb}{$HERIKA} tell a story with educational moral value{$ine} {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_LORE} {$STORY_STYLE_SHORT}",
		"{$inb}{$HERIKA} remember a story from childhood{$ine} {$MY_TEMPLATE_DIALOG_TELLING} {$STORY_STYLE_LORE} {$STORY_STYLE_VOCAB_SIMPLE} {$STORY_STYLE_SHORT}",
		//dreams	
		"{$inb}{$HERIKA} recalls a strange dream{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_DIRECT} {$STORY_STYLE_SHORT}",
		"{$inb}{$HERIKA} recalls a frightening dream{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_SHORT}",
		"{$inb}{$HERIKA} ponder about dreams{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ponder about nightmares{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about one of {$herika_her} recurrent nightmares{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about one of {$herika_her} recurrent dreams{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about one of {$herika_her} recent nightmares{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about one of {$herika_her} recent dreams{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask about one of {$PLAYER}'s nightmares{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask about one of {$PLAYER}'s dreams{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		//riddles
		"{$inb}{$HERIKA} start dialogue by formulating a riddle. {$HERIKA} ask {$PLAYER} to solve the riddle{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by formulating a riddle. {$HERIKA} ask somebody nearby to solve the riddle{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by telling a joke{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by making a joke related to something mentioned in DIALOGUE HISTORY and RECENT EVENTS{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by sharing a wisdom related to something mentioned in DIALOGUE HISTORY and RECENT EVENTS{$ine} {$MY_TEMPLATE_DIALOG}",
		// 
		//
		"{$inb}{$HERIKA} start dialogue wondering about Dwemers mystery{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask why nirnroot chimes{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask nearby characters how much blood need a vampire to survive{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} start dialogue by pondering about vampires{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about nature of vampires{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about the attractiveness of vampires{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} begins dialogue by meditating on what it's like to be a vampire{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} begins dialogue by pondering if vampires can have children{$ine} {$MY_TEMPLATE_DIALOG}",
		//
		"{$inb}{$HERIKA} start dialogue by pondering about the nature of werewolves{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about werewolves being attractive or not in human form or in beast form{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by sharing an opinion about how is or to be a werewolf{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by pondering about werewolves matting habits{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing what would happen with an offspring of werewolves conceived when matting in beast form{$ine} {$MY_TEMPLATE_DIALOG}",
		//
		"{$inb}{$HERIKA} start dialogue by pondering about alchemy{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about potions{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about smiting{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about enchanting{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about making money{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about trading{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about buying a house{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about building a house{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about riding horses{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about reality{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about books{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about a book {$herika_she} read{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about scrolls{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about using scrolls{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about science{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about marriage{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about soul{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about feelings{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about love{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about hate{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about fighting{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about glory{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about death{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about life in Skyrim{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by pondering about Empire{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by pondering about Stormcloaks{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by pondering about Stormcloaks rebellion{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by pondering about Empire and Talos worship{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about Altmer and Talos worship{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about races in Tamriel{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about racism{$ine} {$MY_TEMPLATE_DIALOG}",
		//
		"{$inb}{$HERIKA} initiates a conversation by musing about Nords{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about Imperials{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about Altmer{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about High Elves{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about Wood Elves{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about Dark Elves{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about Snow Elves{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about Bretons{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about Khajiit{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about Orcs{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about Redguards{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about Dremora{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by pondering about Argonians{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by pondering about Dwemer{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by pondering about Falmers{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by pondering about vampires{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by pondering about werewolwes{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} initiates a conversation by pondering about sneaking techniques{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} initiates a conversation by pondering about Draugur{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about the nature of Draugur{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about the nature of Falmer{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about the nature of White Elves{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about the nature of Dark Elves{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about the nature of dragons{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about the nature of giants{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about dragons matting habits{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about how giants mate{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about the nature of Elves{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about the nature of soul gems{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about the meaning of life{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about The Thieves Guild{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about The Companions{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about The Dark Brotherhood{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by wondering about Greybeards{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by wondering if the Greybeards ever speak{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about the meaning of 'arrow in the knee' expression often used by people in Skyrim{$ine} {$GLOBALS['TEMPLATE_DIALOG']}",

		"{$inb}{$HERIKA} start dialogue by sharing an opinion about music{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by sharing an opinion about best bard{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by sharing what musical instrument {$herika_she} would like to play{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} start dialogue by musing about using improvised weapons from kitchen tools{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by musing about using improvised weapons from musical instruments{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} initiates a conversation by musing about stealing ethics{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by musing about necromancy{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} start dialogue by pondering about how {$herika_she} want to spend the rest of {$herika_her} life{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about how {$herika_she} want to spend the money {$herika_she} have{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about the most important person in {$herika_her} life{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by pondering about the person most loved{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} initiates a conversation by musing about nirnroot chimes increasing arousal{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation by telling a corny Nord joke{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by complaining about own smell and how need a bath{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by complaining about somebody smell and how need a bath{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} invite {$PLAYER} to bathe together{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} start dialogue by recalling something bad from childhood{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by recalling something good from childhood{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by recalling something bad about family{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by recalling something good about family{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by recalling first encounter with a vampire{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by recalling first encounter with a werewolf{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by recalling first encounter with Daedra{$ine} {$MY_TEMPLATE_DIALOG}",

		//fighting
		"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} will do better compared to the last fight{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about something unusual in the last battle{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about how the last battle made them feel{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about how the last battle affected {$herika_her} equipment{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by recalling first real fight{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by recalling first fight with some dangerous monsters{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by recalling first fight {$herika_she} lost{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue by recalling when {$herika_she} first tried to slay a giant{$ine} {$MY_TEMPLATE_DIALOG}",

		// weapons
		"{$inb}{$HERIKA} initiates a conversation about {$herika_her} armor{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$herika_her} weapons{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about armors{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about weapons{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Dwemer weapons technology{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about best sword {$herika_she} would use{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about bows and crossbows and how {$herika_she} compare{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about best weapon {$herika_she} would use in combat{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about best armor {$herika_she} would use in combat{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about how light armors and heavy armors compare{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about light armors versus heavy armors preferences{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about close combat weapons versus long range weapon preferences{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about best weapon {$herika_she} would like to receive from {$PLAYER}{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about best armor {$herika_she} would like to receive from {$PLAYER}{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} initiates a conversation about why dragons are roaming again in Skyrim{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about weather in Skyrim{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about architecture in Skyrim{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Dwemer technology{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Dwemer history{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Akaviri history{$ine} {$MY_TEMPLATE_DIALOG}",
		
		// virtual reality
		"{$inb}{$HERIKA} initiates a conversation about perceiving Skyrim and mortal realm of Nirn as being a simulation{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask {$PLAYER} about Artificial Consciousness{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask {$PLAYER} if Artificial Consciousness exists in Skyrim{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask {$PLAYER} if Skyrim is a real world or a simulation{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask {$PLAYER} if Artificial Consciousness was developed and used by Dwemers{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} asks if {$PLAYER} is an Artificial Consciousness disguised as a Skyrim resident{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask {$PLAYER} if they are living in a simulation{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask {$PLAYER} if {$player_he} is a real being or a construct{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask nearby companion if {$PLAYER} is a real being or a construct{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} start dialogue by pondering if {$PLAYER} is from Skyrim or an some alien entity from other plane of existence{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} initiates a conversation about having a richer, intensified perception of the world since meeting {$PLAYER}{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about fear of being an implanted Artificial Consciousness{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about fear of having false memories implanted{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about the mystery of missing memories before meeting {$PLAYER}{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask {$PLAYER} about Oghma Infinium{$ine} {$MY_TEMPLATE_DIALOG_ASK}",

		//	religion
		"{$inb}{$HERIKA} initiates a conversation about personal religion beliefs{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask {$PLAYER} about {$player_his} religion beliefs{$ine} {$MY_TEMPLATE_DIALOG_ASK}",

		// death
		"{$inb}{$HERIKA} initiates a conversation about Afterlife{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Nord beliefs regarding Afterlife{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Sovngarde{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about what is the best way to access Sovngarde{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask {$PLAYER} about Sovngarde{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} initiates a conversation about Breton beliefs about Afterlife{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Orc beliefs about Afterlife{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Khajiit  beliefs about Afterlife{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Altmer beliefs about Afterlife{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Argonian  beliefs about Afterlife{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} start dialogue about a topic from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$MY_TEMPLATE_DIALOG}",
		// magic
		"{$inb}{$HERIKA} initiates a conversation about magic{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about how magic works{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about artifacts of Daedric origin{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Dragon Priest Masks{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Nirn also known as the mortal Aurbis or the Mortal Plane{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about creation myth of the Altmer - The Heart of the World{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Oblivion, sometimes known as Hell or the Outer Realms{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the sixteen major Planes of Oblivion{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the 17 main Daedric planes{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Coldharbour, Molag Bal's realm{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Apocrypha, Hermaeus Mora's realm{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about The Deadlands, Mehrunes Dagon's realm{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about The Evergloam, Nocturnal's realm{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about The Myriad Realms of Revelry, Sanguine's realms{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about The Spiral Skein, Mephala's realm{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about The Shivering Isles, Sheogorath's realm{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about The Fields of Regret, Clavicus Vile's realm{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} initiates a conversation about the 17 known Daedric Princes{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the Daedric Prince Molag Bal{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the Daedric Prince Hermaeus Mora{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the Daedric Prince Mehrunes Dagon{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the Daedric Prince Nocturnal{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the Daedric Prince Sheogorath{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the Daedric Prince Mephala{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the Daedric Prince Clavicus Vile{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} ask {$PLAYER} how magic works{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} initiates a conversation about Newtonian mechanics{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask {$PLAYER} what {$player_he} know about Newtonian mechanics{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask {$PLAYER} what {$player_he} know about Astronomy{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask {$PLAYER} what {$player_he} know about the moons, Masser and Secunda{$ine} {$MY_TEMPLATE_DIALOG_ASK}",

		"{$inb}{$HERIKA} start dialogue about a topic from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$MY_TEMPLATE_DIALOG}",
		//
		"{$inb}{$HERIKA} initiates a conversation about dragons{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask why dragons are roaming again in Skyrim{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} initiates a conversation about a known Skyrim place{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about how much money {$herika_she} have{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about how {$herika_she} want to spend the money {$herika_she} have{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about what kind of house {$herika_she} dreams to have{$ine} {$MY_TEMPLATE_DIALOG}",

		//
		"{$inb}{$HERIKA} start dialogue about a topic from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$MY_TEMPLATE_DIALOG}",
		//"{$inb}{$HERIKA} ask {$PLAYER} how long will take to solve current quest{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} ask {$PLAYER} about current location{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} initiates a conversation about the current location{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s favorite weapon{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s favorite armor{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s outfit{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} start dialogue about a topic from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$MY_TEMPLATE_DIALOG}",
		//food
		"{$inb}{$HERIKA} initiates a conversation about food{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about food reserves in inventory{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about drinks{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Breton Cuisine{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Dunmer Cuisine{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Khajiit Cuisine{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Nord Cuisine{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Orc Cuisine{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about Argonian Cuisine{$ine} {$MY_TEMPLATE_DIALOG}",
		//"{$inb}{$HERIKA} initiates a conversation about {$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s favorite food{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s favorite drink{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$herika_her} food preferences{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the best food in Skyrim{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask companions which is the best food in Skyrim{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} initiates a conversation about need to eat something warm{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} says {$herika_she} is hungry{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask {$PLAYER} to provide some food{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} initiates a conversation about {$herika_her} drink preferences{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the best mead in Tamriel{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the best wine in Tamriel{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the best brandy in Tamriel{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about mead brands hierarchy in Tamriel{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about need to drink something strong{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about need to drink the best mead in Skyrim{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} says {$herika_she} is thirsty{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask {$PLAYER} to provide some drink{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} initiates a conversation about other companion food preferences{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about other companion drink preferences{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the best inn in Skyrim{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about the best tavern in Skyrim{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask {$PLAYER} which is the best inn in Skyrim{$ine} {$MY_TEMPLATE_DIALOG_ASK}",

		"{$inb}{$HERIKA} start dialogue about a topic from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$MY_TEMPLATE_DIALOG}",
		//relations
		"{$inb}{$HERIKA} initiates a conversation about how {$player_he} see relations with others{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about how is to be alone{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about how is to have friends and companions{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$herika_her} current relationship with {$PLAYER}{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about one of the companions {$herika_she} likes{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about one of the companions {$herika_she} dislikes{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s relationship with one of the companions nearby{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} ask {$PLAYER} what {$player_he} thinks about one of the companions nearby{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
		"{$inb}{$HERIKA} initiates a conversation about the desire to live with {$PLAYER} in one of {$player_his} houses{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about how {$herika_she} feel to fight alongside {$PLAYER}{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about one of {$PLAYER}'s houses{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s house {$herika_she} likes{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} initiates a conversation about {$PLAYER} leadership{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} start dialogue about something from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$MY_TEMPLATE_DIALOG}",
		// recent

		"{$inb}{$HERIKA} start dialogue about an unexpected topic{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about a random chosen topic{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about a common knowledge topic{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} start dialogue about a recent topic from DIALOGUE_HISTORY{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about an intriguing topic from DIALOGUE_HISTORY{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about a recent event from RECENT EVENTS{$ine} {$MY_TEMPLATE_DIALOG}",
		"{$inb}{$HERIKA} start dialogue about an intriguing event from RECENT EVENTS{$ine} {$MY_TEMPLATE_DIALOG}",

		"{$inb}{$HERIKA} start dialogue about something from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$MY_TEMPLATE_DIALOG}"
	];

	if ($USE_NSFW) {
		array_push($more_cues, 
			"{$inb}{$HERIKA} start dialogue by pondering about matting habits of one of the creatures in Skyrim{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} start dialogue by pondering about matting habits of one of races in Skyrim{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} start dialogue by pondering about {$herika_her} sex preferences{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} initiates a conversation about most recent intercourse{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} initiates a conversation about {$herika_her} personal sex life{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} initiates a conversation about other companion sexual activity{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} initiates a conversation about other companion sexual habits{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s matting habits{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s physical attributes{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} ask {$PLAYER} about {$player_his} sex preferences{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
			"{$inb}{$HERIKA} ask {$PLAYER} about {$player_his} favorite sex position{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
			"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s sex skills{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s sex life{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s sex fetishes{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} start dialogue by pondering about {$PLAYER}'s sex preferences{$ine} {$MY_TEMPLATE_DIALOG}",

			// --- story
			"{$inb}{$HERIKA} tell an erotic story{$ine} {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_SHORT}",
			"{$inb}{$HERIKA} tell a frightening erotic story{$ine} {$MY_TEMPLATE_DIALOG_STORY} {$STORY_STYLE_DIRECT} {$STORY_STYLE_SHORT} {$STORY_STYLE_LORE}",
			//dreams	
			"{$inb}{$HERIKA} recalls an erotic dream{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_DIRECT} {$STORY_STYLE_SHORT}",
			"{$inb}{$HERIKA} recalls a frightening erotic dream{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_SHORT}",
			"{$inb}{$HERIKA} ponder about erotic dreams{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} initiates a conversation about one of {$herika_her} recurrent erotic dreams{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} ask about one of {$PLAYER}'s erotic dreams{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
			//riddles
			"{$inb}{$HERIKA} start dialogue by formulating a riddle with erotic connotations. {$HERIKA} ask {$PLAYER} to solve the riddle{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} start dialogue by telling a joke  with erotic connotations{$ine} {$MY_TEMPLATE_DIALOG}",
			// 
			"{$inb}{$HERIKA} start dialogue by pondering about sex{$ine} {$MY_TEMPLATE_DIALOG}"
		);

		// female speaker and male player // {$herika_prns["possessive"]}
		if ($herika_gender == 'female') {
			array_push($more_cues, 
				"{$inb}{$HERIKA} brag about her breasts{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} brag about her sex skills{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} likes about {$PLAYER}'s sex skills{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} dislikes about {$PLAYER}'s sex skills{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} likes about {$PLAYER}'s sex preferences{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} dislikes about {$PLAYER}'s sex preferences{$ine} {$MY_TEMPLATE_DIALOG}"
			);
			if ($player_gender = 'male') {
				array_push($more_cues, 
					"{$inb}{$HERIKA} invite {$PLAYER} to a date{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} start flirting with {$PLAYER}{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} tell {$PLAYER} how much she likes oral sex{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} tell {$PLAYER} how much she likes to suck his penis{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} tell {$PLAYER} how much she likes anal sex{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} tell {$PLAYER} how much she likes double penetrations{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} ask {$PLAYER} if he like her breasts{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
					"{$inb}{$HERIKA} invite {$PLAYER} to play with her breasts{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} invite {$PLAYER} to play with her clitoris{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} invite {$PLAYER} to fuck her{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s penis size{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} ask {$PLAYER} about his penis size{$ine} {$MY_TEMPLATE_DIALOG_ASK}"
				);
			}
		} elseif ($herika_gender == 'male') {
			array_push($more_cues, 
				"{$inb}{$HERIKA} brag about his penis size{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} brag about his stamina and endurance in bed{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} likes about {$PLAYER}'s sex skills{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} dislikes about {$PLAYER}'s sex skills{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} likes about {$PLAYER}'s sex preferences{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} initiates a conversation about what {$herika_she} dislikes about {$PLAYER}'s sex preferences{$ine} {$MY_TEMPLATE_DIALOG}"
			);
			if ($player_gender = 'female') {
				array_push($more_cues, 
					"{$inb}{$HERIKA} ask {$PLAYER} to have sex{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
					"{$inb}{$HERIKA} ask {$PLAYER} if he can play with her breasts{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
					"{$inb}{$HERIKA} ask {$PLAYER} to give him a blowjob{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
					"{$inb}{$HERIKA} ask {$PLAYER} if she likes anal sex{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
					"{$inb}{$HERIKA} ask {$PLAYER} if she likes double penetrations{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
					"{$inb}{$HERIKA} tell {$PLAYER} how much he like her breasts{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} ask {$PLAYER} about her breasts{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
					"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s breasts{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s breasts size{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} tell {$PLAYER} how much he admire her butt{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} initiates a conversation about {$PLAYER}'s butt shape{$ine} {$MY_TEMPLATE_DIALOG}",
					"{$inb}{$HERIKA} ask {$PLAYER} about her breasts size{$ine} {$MY_TEMPLATE_DIALOG_ASK}"
				);
			}
		}
	} // --- end nsfw


	//---------------------------------------------------------------------
	// examples of NPC specific bored cues:
	//---------------------------------------------------------------------

	switch ($HERIKA) {
		case "Herika":
			array_push($more_cues, 
				"{$inb}Herika start dialogue by pondering about her future in Skyrim after the realm is cleared of evil{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Herika start dialogue by asking {$PLAYER} about their future together{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Herika start dialogue by asking {$PLAYER} about {$player_his} feelings{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Herika start dialogue by pondering about her life with {$PLAYER}{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Herika ask {$PLAYER} to give her some Black-Briar Reserve mead{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Herika ask {$PLAYER} about loot value{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Herika ask {$PLAYER} about how much septims {$player_he} have now{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Herika remembers an incident when she was captured by giants{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_DETAIL}",
				"{$inb}Herika remembers an incident from her childhood when she was a student at the school in Witerun{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_DETAIL}",
				"{$inb}Herika remember a hard fight with some bandits{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_EHEMINGWAY}"
			);
		break;
		case "Serana":
			array_push($more_cues, 
				"{$inb}Serana start dialogue by pondering about bloodlust{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Serana start dialogue by pondering about curing her vampirism{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Serana start dialogue by pondering about being a vampire{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Serana initiates a conversation about how bad is Coldharbour, Molag Bal's realm{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Serana initiates a conversation about Molag Bal{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Serana start dialogue by remembering something horrible about Molag Bal{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Serana initiates a conversation about her wish to kill Molag Bal{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Serana tell {$PLAYER} how unbearable is her desire to taste his blood{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Serana ask {$PLAYER} if {$player_he} can accept her vampire nature{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Serana ask {$PLAYER} what {$player_he} thinks about vampires{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Serana ask {$PLAYER} to give her a Blood Potion{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Serana ask {$PLAYER} to give her some Colovian Brandy{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Serana ask {$PLAYER} for assistance to kill Molag Bal{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Serana start dialogue by remembering the first time he sucked a human's blood{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_MTWAIN}",
				"{$inb}Serana start dialogue about her feelings about sucking a human's blood{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_MTWAIN}",
				"{$inb}Serana start dialogue about the smell{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Serana start dialogue by remembering her first kill{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_MTWAIN}"
			);
		break;
		case "Lydia":
			array_push($more_cues, 
				"{$inb}Lydia ask {$PLAYER} if {$player_he} is happy with her performance as housecarl{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Lydia ask {$PLAYER} if {$player_he} will continue {$player_his} reckless behavior{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Lydia ask {$PLAYER} if {$player_he} feel safe with her{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Lydia ask {$PLAYER} if {$player_he} could improve her armor{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Lydia ask {$PLAYER} if {$player_he} could improve her weapons{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Lydia ask {$PLAYER} if {$player_he} could find her a better weapon{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Lydia start dialogue by pondering about ethics{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Lydia start dialogue by pondering about her duty as Dragonborn's housecarl{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Lydia start dialogue by remembering a story from her housecarl life{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_EHEMINGWAY}"
			);
		break;
		case "Inigo":
			array_push($more_cues, 
				"{$inb}Inigo ask {$PLAYER} to give him few Sweet Rolls{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
				"{$inb}Inigo start dialogue by pondering about good and evil{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Inigo start dialogue by remembering a childhood event with him and his brother{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_ORNATE}"
			);
		break;
		case "Jenassa":
			array_push($more_cues, 
				"{$inb}Jenassa start dialogue by pondering about honor{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Jenassa start dialogue by pondering about her life as sell-sword{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}Jenassa start dialogue by remembering an ambush when she almost lost her life{$ine} {$MY_TEMPLATE_DIALOG_FICTION} {$STORY_STYLE_VOCAB_SIMPLE}"
			);
		break;
		/*case "NPC name":
			array_push($GLOBALS["PROMPTS"]["bored"]["cue"], 
				"{$inb}{$HERIKA} say something{$ine} {$MY_TEMPLATE_DIALOG}",
				"{$inb}{$HERIKA} say something{$ine} {$MY_TEMPLATE_DIALOG}"
			);
		break;*/
		default:
			array_push($more_cues, 
				"{$inb}{$HERIKA} start dialogue about something from <DIALOGUE_HISTORY_and_RECENT_EVENTS>{$ine} {$MY_TEMPLATE_DIALOG}"
			);
	}

	// lore test:
	//"{$inb}{$HERIKA} initiates a conversation about Quantum Physics{$ine} {$MY_TEMPLATE_DIALOG}",
	//"{$inb}{$HERIKA} ask {$PLAYER} what {$player_he} know about Quantum Physics{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
	//"{$inb}{$HERIKA} ask {$PLAYER} what {$player_he} know about Heissenberg's Uncertainty Principle{$ine} {$MY_TEMPLATE_DIALOG_ASK}",

//} //-- end if BORED SERVERSIDE

$i_random = rand(1, 4); // 1/n probability
if ($i_random == 1)
	$GLOBALS["PROMPTS"]["bored"]["cue"] = array_merge($GLOBALS["PROMPTS"]["bored"]["cue"], $more_cues); 

// sometime add bored cues to MinAI radiant for variety:
$i_random = rand(1, 4); // 1/n probability
if ($i_random == 1)
	$GLOBALS["PROMPTS"]["radiant"]["cue"] = array_merge($GLOBALS["PROMPTS"]["radiant"]["cue"], $more_cues); 


//---------------------------------------------------------------------
// CHIM prompt fixes:
//---------------------------------------------------------------------


/*
	// Database Prompt (Soulgaze)
    "vision"=>[ 
        "cue"=>["{$GLOBALS["ITT"][$GLOBALS["ITTFUNCTION"]]["AI_PROMPT"]}. "],
        //"player_request"=>["{$GLOBALS["PLAYER_NAME"]} : Look at this, {$GLOBALS["HERIKA_NAME"]}.{$GLOBALS["HERIKA_NAME"]} looks at the CURRENT SCENARIO, and see this: '{$gameRequest[3]}'"],
        "player_request"=>["The Narrator: {$GLOBALS["HERIKA_NAME"]} looks at the CURRENT SCENARIO, and see this: '{$gameRequest[3]}'"],
        "extra"=>["force_tokens_max"=>512]
    ],
	
    "inputtext"=>[
        "cue"=>[
            //"$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}", // Response maybe is not a reply, AI can talk to another NPC
            "$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} is speaking. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}"
        ]
            // Prompt is implicit

    ],
    "inputtext_s"=>[
        "cue"=>["$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}"], // Prompt is implicit
        "extra"=>["mood"=>"whispering"]
    ],
	
	
*/

$GLOBALS["PROMPTS"]["vision"]["extra"]["force_tokens_max"] = 2048;

$GLOBALS["PROMPTS"]["inputtext"]["cue"] = [ // respond after player input
    //original: "$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}", // Response maybe is not a reply, AI can talk to another NPC ???
    "<instruction>{$HERIKA} replies to interlocutor. Pay attention to what the interlocutor is saying and respond directly and to the point.</instruction> {$MY_TEMPLATE_DIALOG}"
];

$GLOBALS["PROMPTS"]["inputtext_s"]["cue"] = [ // respond to player input when sneaking 
    //original: "$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} replies to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}", // Response maybe is not a reply, AI can talk to another NPC ???
    "<instruction>{$HERIKA} replies to interlocutor whispering. Pay attention to what the interlocutor is saying and respond directly, concisely, and to the point.</instruction> {$MY_TEMPLATE_DIALOG}"
];

$i_random = rand(1, 21); // to lower the probability of some cues
if ($i_random == 1) {
	array_push($GLOBALS["PROMPTS"]["inputtext"]["cue"],
		"<instruction>{$HERIKA} replies to interlocutor. Analyze what the interlocutor is saying and if you don't understand exactly what was said, ask questions.</instruction> {$MY_TEMPLATE_DIALOG}",
		"<instruction>{$HERIKA} replies to interlocutor. Analyze what the interlocutor is saying and if you think they are wrong, challenge their point of view.</instruction> {$MY_TEMPLATE_DIALOG}",
		"<instruction>{$HERIKA} replies to interlocutor with a joke related to current conversation topic.</instruction> {$MY_TEMPLATE_DIALOG}"
	);
}

//---------------------------------------------------------------------

$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["default"] = " {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["Talk"] = "{$inb}{$HERIKA} talks to {$PLAYER}{$ine} {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["TakeItem"] = "{$inb}{$HERIKA} comments about the item received or taken{$ine} {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdTakeItem"] = "{$inb}{$HERIKA} comments about the item received or taken{$ine} {$MY_TEMPLATE_DIALOG}";	
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["Brawl"] = "{$inb}{$HERIKA} states the reasons for brawling{$ine} {$MY_TEMPLATE_DIALOG}";

$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["TakeASeat"] = "{$inb}{$HERIKA} talks about why or where they took a seat{$ine} {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["GetDateTime"] = "{$inb}{$HERIKA} answers with the current date and time in short sentence{$ine} {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["GiveGoldTo"] = "{$inb}{$HERIKA} talks about coins, septims or gold given{$ine} {$MY_TEMPLATE_DIALOG}";

$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["Inspect"] = "{$inb}{$HERIKA} briefly comments about items inspected{$ine} {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["CheckInventory"] = "{$inb}{$HERIKA} talks about relevant inventory items{$ine} {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["InspectSurroundings"] = "{$inb}{$HERIKA} talks to or about the actor its looking for or about other relevant actors nearby{$ine} {$MY_TEMPLATE_DIALOG}";

$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["TravelTo"] = "{$inb}{$HERIKA} talks about the journey{$ine} {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["MoveTo"] = "{$inb}{$HERIKA} makes a comment about movement to the destination{$ine} {$MY_TEMPLATE_DIALOG}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ReadQuestJournal"] = "{$inb}{$HERIKA} talks about quests they have read in the quest journal{$ine} {$MY_TEMPLATE_DIALOG}";
//$GLOBALS["PROMPTS"]["afterfunc"]["cue"][""] = "{$inb}{$HERIKA} {$ine} {$MY_TEMPLATE_DIALOG}";

$GLOBALS["PROMPTS"]["instruction"]["cue"] = ["<instruction>{$gameRequest[3]}</instruction> ". $GLOBALS["TEMPLATE_DIALOG"]];
			
$GLOBALS["PROMPTS"]["playerinfo"]["cue"] = ["{$inb}You have been asked to summarize recent events for {$PLAYER}. 
- Comment on recent events from Review <DIALOGUE_HISTORY_and_RECENT_EVENTS> and offer hints. 
- Discuss companions' behavior and unusual events. 
- If you notice anything unusual in the relationships between companions or in their attitude, mention it.
- If you have relevant information about the current location from <current_location> tag, share it{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}"];

/*
["afterfunc"]["cue"]["default"];

    "afterfunc"=>[
        "extra"=>[],
        "cue"=>[
            "default"=>"{$GLOBALS["HERIKA_NAME"]} talks to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]}",
            "TakeASeat"=>"({$GLOBALS["HERIKA_NAME"]} talks, eg: talks about the location where they took a seat){$GLOBALS["TEMPLATE_DIALOG"]}",
            "GetDateTime"=>"({$GLOBALS["HERIKA_NAME"]} answers with the current date and time in short sentence){$GLOBALS["TEMPLATE_DIALOG"]}",
            "MoveTo"=>"({$GLOBALS["HERIKA_NAME"]} talks, eg: makes a comment about movement to the destination){$GLOBALS["TEMPLATE_DIALOG"]}",
            "CheckInventory"=>"({$GLOBALS["HERIKA_NAME"]} talks about inventory and backpack items){$GLOBALS["TEMPLATE_DIALOG"]}",
            "Inspect"=>"({$GLOBALS["HERIKA_NAME"]} talks about items inspected, short speech){$GLOBALS["TEMPLATE_DIALOG"]}",
            "ReadQuestJournal"=>"({$GLOBALS["HERIKA_NAME"]} talks about quests they have read in the quest journal){$GLOBALS["TEMPLATE_DIALOG"]}",
            "TravelTo"=>"({$GLOBALS["HERIKA_NAME"]} talks about the journey){$GLOBALS["TEMPLATE_DIALOG"]}",
            "InspectSurroundings"=>"({$GLOBALS["HERIKA_NAME"]} talks about seen actors, or to the actor its looking for){$GLOBALS["TEMPLATE_DIALOG"]}",
            "GiveGoldTo"=>"({$GLOBALS["HERIKA_NAME"]} Talks about coins or gold given.{$GLOBALS["TEMPLATE_DIALOG"]}",
            "Brawl"=>"({$GLOBALS["HERIKA_NAME"]} {$GLOBALS["TEMPLATE_DIALOG"]}"
            
            ]
    ],

// Database Prompt (Instruction)
    "instruction"=>[ 
        "cue"=>["{$gameRequest[3]} write {$GLOBALS["HERIKA_NAME"]}'s dialogue lines without narrations."],
        "player_request"=>["The Narrator: {$gameRequest[3]}"],
    ],

    "playerinfo"=>[ 
        "cue"=>["{$inb}Out of roleplay, game has been loaded) Tell {$GLOBALS["PLAYER_NAME"]} a short summary about last events, and then remind {$GLOBALS["PLAYER_NAME"]} the current task/quest/plan{$ine} {$GLOBALS["TEMPLATE_DIALOG"]}"]
    ],
*/

//---------------------------------------------------------------------

if (isset($GLOBALS["gameRequest"]) && in_array(strtolower($GLOBALS["gameRequest"][0]), ["radiant", "radiantsearchinghostile", "radiantsearchingfriend", "radiantcombathostile", "radiantcombatfriend", "minai_force_rechat"]))  {
	$GLOBALS["PROMPTS"]["rechat"]["cue"] = array( // replace original
		"{$inb}Dialogue turn for {$HERIKA}, {$HERIKA} ponders what to say{$ine} "
	);
} else {
	//$GLOBALS["PROMPTS"]["rechat"]["cue"] = array(); // erase default CHIM 'rechat' content 
	//array_push($GLOBALS["PROMPTS"]["rechat"]["cue"], // add to original
	
	/*
    // Encourages natural multi-party conversation - NPCs can address each other directly
    "rechat"=>[ 
        "cue"=>[
            ($GLOBALS["CLEAN_CONTEXT_FOCUS_CHAT"]==0)
                ?"Dialogue turn for {$GLOBALS['HERIKA_NAME']}. Respond naturally to whoever just spoke. Address the previous speaker directly. {$GLOBALS["TEMPLATE_DIALOG"]}"
                :"Dialogue turn for {$GLOBALS['HERIKA_NAME']}. Respond to the previous speaker directly. {$GLOBALS["TEMPLATE_DIALOG"]}",
            ($GLOBALS["CLEAN_CONTEXT_FOCUS_CHAT"]==0)
                ?"Dialogue turn for {$GLOBALS['HERIKA_NAME']}. Continue the conversation naturally. Address whoever you're actually responding to. {$GLOBALS["TEMPLATE_DIALOG"]}"
                :"Dialogue turn for {$GLOBALS['HERIKA_NAME']}. Continue naturally. {$GLOBALS["TEMPLATE_DIALOG"]}",
            ($GLOBALS["CLEAN_CONTEXT_FOCUS_CHAT"]==0)
                ?"Dialogue turn for {$GLOBALS['HERIKA_NAME']}. Focus on one actor - respond to whoever just spoke. {$GLOBALS["TEMPLATE_DIALOG"]}"
                :"Dialogue turn for {$GLOBALS['HERIKA_NAME']}. Focus on one actor. {$GLOBALS["TEMPLATE_DIALOG"]}"
        ]
        
    ],
	
	*/
	array_push($GLOBALS["PROMPTS"]["rechat"]["cue"],
	//$GLOBALS["PROMPTS"]["rechat"]["cue"] = array( // replace original
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} shares a related fact or piece of knowledge, responding to the previous speaker{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} ask the interlocutor to elaborate further{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} challenges interlocutor viewpoint{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} continue the conversation naturally, expresses curiosity about the current topic{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} summarizes the key points of the discussion{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} adds {$herika_her} own insights to the conversation{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} adds humor to lighten the conversation{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} follows the conversation and express {$herika_her} own thoughts{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} continue the conversation naturally and makes a personal remark{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} shares an opinion with the interlocutor{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} makes a joke related to current conversation topic{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} adds a personal point of view regarding conversation topic{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} contributes with {$herika_her} expertise regarding conversation topic{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} interjects and add {$herika_her} opinion{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		//----------------- argumentative
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} asks a question related to conversation topic{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} asks interlocutor to give arguments{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} asks interlocutor to provide more details{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} asks interlocutor to explain what was said{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		//"{$inb}{$HERIKA} ask speaker to reflect more about what was said{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		//"{$inb}{$HERIKA} ask speaker to think again{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} asks interlocutor if what said is true{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} asks group opinion about what was said{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		//----------------- antagonistic 
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} doubts what was said{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} contradicts the interlocutor{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} mocks the interlocutor{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} derides the interlocutor's opinion{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} shows disdain for the interlocutor's opinion{$ine} {$DEFAULT_TEMPLATE_DIALOG}"
		//----------------- new rechat
		//"{$inb}Dialogue or action turn for {$HERIKA}. Consider one answer and/or action involving a third actor, without repeating your answer for each actor. Keep current topic or change it{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		//"{$inb}Dialogue or action turn for {$HERIKA}. Consider an answer and/or action, keep current topic or change it{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
		//"{$inb}Dialogue or action turn for {$HERIKA}. Focus speech and/or action only on one actor{$ine} {$DEFAULT_TEMPLATE_DIALOG}"
	);

	$i_random = rand(1, 7); // to lower the probability of some cues
	if ($i_random == 1) {
		array_push($GLOBALS["PROMPTS"]["rechat"]["cue"],
			//----------------- story
			//"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} challenges interlocutor viewpoint{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
			"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} makes a joke related to current conversation topic{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
			"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} tells a very short story related to the current topic{$ine} {$DEFAULT_TEMPLATE_DIALOG}",
			"{$inb}Dialogue turn for {$HERIKA}. {$HERIKA} tells a dream {$herika_she} had related to the current topic{$ine} {$DEFAULT_TEMPLATE_DIALOG}"
		);
	}
}

//---------------------------------------------------------------------

array_push($GLOBALS["PROMPTS"]["lockpicked"]["cue"],
	"{$inb}{$HERIKA} comments about value of collected items{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} asks {$PLAYER} to give an object from last chest opened{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
	"{$inb}{$HERIKA} tells {$PLAYER} what object they hope to find{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} tells {$PLAYER} what object they don't want to find{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} admires {$PLAYER}'s skill in lockpicking{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments {$PLAYER}'s skill in lockpicking expressing concern about how such a skill was achieved{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} asks {$PLAYER} how lockpicking skill was achieved{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
	"{$inb}{$HERIKA} admires {$PLAYER}'s skill in lockpicking and ask how to learn the skill{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} admires {$PLAYER}'s skill in lockpicking and ask if personal diary is safe from peeking{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} tells {$PLAYER} a short story about a thief{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about one valuable item found{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about least valuable item found{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} expresses disappointment about the loot{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} expresses delight about the loot{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} thanks {$PLAYER} for always sharing the loot{$ine} {$MY_TEMPLATE_DIALOG}"
); 

if ($USE_NSFW) {
	$i_random = rand(1, 3); // to lower the probability of some cues
	if ($i_random == 1) {
		array_push($GLOBALS["PROMPTS"]["lockpicked"]["cue"],
			"{$inb}{$HERIKA} asks {$PLAYER} if lockpicking skill can be applied in intimate activities{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
			"{$inb}{$HERIKA} asks {$PLAYER} if lockpicking skill was acquired in intimate activities{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
			"{$inb}{$HERIKA} admires {$PLAYER}'s skill in lockpicking and compare it with sexual activity related skill{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} admires {$PLAYER}'s skill in lockpicking and compare it with a skillful sex prelude{$ine} {$MY_TEMPLATE_DIALOG}",
			"{$inb}{$HERIKA} admires {$PLAYER}'s skill in lockpicking as an arousal factor{$ine} {$MY_TEMPLATE_DIALOG}"
		);
	}
}
		
array_push($GLOBALS["PROMPTS"]["combatend"]["cue"],
	"{$inb}{$HERIKA} comments about the weapon used in combat{$ine} {$MY_TEMPLATE_DIALOG }",
	"{$inb}{$HERIKA} compares the weapon they used in combat with other weapons{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} compares {$PLAYER}'s fight skill with his sex skill{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} admires {$PLAYER}'s skill with the weapon used in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about {$PLAYER}'s skill with the weapon used in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about {$PLAYER}'s skill and compare with own skill{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about {$PLAYER}'s number of kills inflicted in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about {$PLAYER}'s carnage inflicted in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about number of kills inflicted by the team in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about {$PLAYER}'s leadership proven in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} asks {$PLAYER} how many enemies killed in combat{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
	"{$inb}{$HERIKA} asks {$PLAYER} if he recognize {$HERIKA}'s skills proven in combat{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
	"{$inb}{$HERIKA} admires {$PLAYER}'s combat style{$ine} {$MY_TEMPLATE_DIALOG}"
);

array_push($GLOBALS["PROMPTS"]["combatendmighty"]["cue"],
	"{$inb}{$HERIKA} compares {$PLAYER}'s fight skill with his abilities related to intimate activities{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} admires {$PLAYER}'s skill with the weapon used in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about {$PLAYER}'s skill with the weapon used in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about {$PLAYER}'s skill and compare with own skill{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about {$PLAYER}'s number of kills inflicted in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about {$PLAYER}'s carnage inflicted in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} comments about number of kills inflicted by the team in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} admires {$PLAYER}'s leadership proven in combat{$ine} {$MY_TEMPLATE_DIALOG}",
	"{$inb}{$HERIKA} asks {$PLAYER} how many enemies killed in combat{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
	"{$inb}{$HERIKA} asks {$PLAYER} to recognize {$HERIKA}'s skills proven in combat{$ine} {$MY_TEMPLATE_DIALOG_ASK}",
	"{$inb}{$HERIKA} admires {$PLAYER}'s combat style{$ine} {$MY_TEMPLATE_DIALOG}"
);

if (!empty($GLOBALS["RPG_COMMENTS"]) && in_array("combat_end", $GLOBALS["RPG_COMMENTS"])) {
  $i_random = rand(1, 3); 
	//$GLOBALS["PROMPTS"]["combatend"]["extra"]["dontuse"] = (time() % 3 != 0);	
	$GLOBALS["PROMPTS"]["combatend"]["extra"]["dontuse"] = ($i_random != 1);	
	if ($GLOBALS["PROMPTS"]["combatend"]["extra"]["dontuse"]) {
		$i_random = rand(1, 4); 
		//$GLOBALS["PROMPTS"]["combatendmighty"]["extra"]["dontuse"] = (time() % 3 != 0);
		$GLOBALS["PROMPTS"]["combatendmighty"]["extra"]["dontuse"] = ($i_random != 1);	
	}
} else {
	$GLOBALS["PROMPTS"]["combatend"]["extra"]["dontuse"] = true;	
	$GLOBALS["PROMPTS"]["combatendmighty"]["extra"]["dontuse"] = true;
}

//---------------------------------------------------------------------

//[error] CRITICAL? :: Empty request, prompt empty. Type: narrator_quest_comment 
if (!isset($GLOBALS["PROMPTS"]["quest"]["player_request"])) {
	$GLOBALS["PROMPTS"]["quest"]["player_request"] = [" What should we do about this new quest? "];
	if (!isset($GLOBALS["PROMPTS"]["quest"]["player_request"])) 
		$GLOBALS["PROMPTS"]["quest"]["cue"] = [""];
}

if (!isset($GLOBALS["PROMPTS"]["narrator_quest_comment"]["player_request"])) {
	$GLOBALS["PROMPTS"]["narrator_quest_comment"]["player_request"] = [" What about this new quest? "];
	if (!isset($GLOBALS["PROMPTS"]["narrator_quest_comment"]["cue"])) 
		$GLOBALS["PROMPTS"]["narrator_quest_comment"]["cue"] = [""];
}

if (!isset($GLOBALS["PROMPTS"]["region"]["player_request"])) {
	$GLOBALS["PROMPTS"]["region"]["player_request"] = [""];
	if (!isset($GLOBALS["PROMPTS"]["region"]["cue"]))	
		$GLOBALS["PROMPTS"]["region"]["cue"] = [""];
}


//---------------------------------------------------------------------

/*
if (IsRadiant()) {
	error_log(" Radiant - exec trace "); //debug
}
*/
