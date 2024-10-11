scriptname minai_SapienceController extends Quest

minai_MainQuestController main
minai_AIFF aiff
minai_Sex  sex
minai_DeviousStuff devious
minai_Config config

actor playerRef
bool bHasAIFF
GlobalVariable minai_SapienceEnabled

function Maintenance(minai_MainQuestController _main)
  playerRef = Game.GetPlayer()
  main = _main
  config = Game.GetFormFromFile(0x0912, "MinAI.esp") as minai_Config
  if !config
    Main.Fatal("Could not load configuration - script version mismatch with esp")
  EndIf
  aiff = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_AIFF
  sex = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_Sex
  devious = Game.GetFormFromFile(0x0802, "MinAI.esp") as minai_DeviousStuff
  Main.Info("Initializing Sapience Module.")
  minai_SapienceEnabled = Game.GetFormFromFile(0x091A, "MinAI.esp") as GlobalVariable
  RegisterForModEvent("AIFF_CommandReceived", "CommandDispatcher") ; Hook into AIFF - This is a separate quest, so we have to do this separately
  RegisterForModEvent("AIFF_TextReceived", "OnTextReceived")
  bHasAIFF = (Game.GetModByName("AIAgent.esp") != 255)
  StartRadiantDialogue()
EndFunction


Event CommandDispatcher(String speakerName,String  command, String parameter)
  if !bHasAIFF
    return
  EndIf
  Main.Debug("Sapience - CommandDispatcher(" + speakerName +", " + command +", " + parameter + ")")

EndEvent


Function SetContext(actor akTarget)
  
EndFunction


string Function GetKeywordsForActor(actor akTarget)
  return "";
EndFunction


string Function GetFactionsForActor(actor akTarget)
  return "";
EndFunction

Event OnUpdate()
  if minai_SapienceEnabled.GetValueInt() != 1 || !bHasAIFF
    StopRadiantDialogue()
    return
  EndIf
  if Utility.RandomFloat(0, 100) <= config.radiantDialogueChance    
    actor[] nearbyActors = AIAgentFunctions.findAllNearbyAgents()
    ; Use bored chat instead for this
    ; PapyrusUtil.PushActor(nearbyActors, playerRef) ; Let the NPC decide to talk to the player
    if nearbyActors.Length >= 2
      int actor1 = PO3_SKSEFunctions.GenerateRandomInt(0, nearbyActors.Length - 1)
      int actor2 = actor1
      while actor2 == actor1
        actor2 = PO3_SKSEFunctions.GenerateRandomInt(0, nearbyActors.Length - 1)
      endwhile
      string actor1name = Main.GetActorName(nearbyActors[actor1])
      string actor2name = Main.GetActorName(nearbyActors[actor2])
      Main.Info("SAPIENCE: Triggering Radiant Dialogue ( " + actor1name + " => " + actor2name + ")")
      AIAgentFunctions.requestMessageForActor(actor2name, "radiant", actor1name)
    else
      Main.Debug("SAPIENCE: Not enough nearby actors for radiant dialogue")
    EndIf
  EndIf
  StartNextUpdate()
EndEvent

Event OnTextReceived(String speakerName, String sayLine)
  if minai_SapienceEnabled.GetValueInt() == 1
    Main.Debug("SAPIENCE: Received LLM response, Resetting radiant dialogue cooldown")
    StartNextUpdate()
  EndIf
EndEvent


Function StartRadiantDialogue()
  Main.Info("SAPIENCE: Beginning Radiant Dialogue")
  StartNextUpdate()
EndFunction


Function StartNextUpdate()
  if minai_SapienceEnabled.GetValueInt() == 1  && config.radiantDialogueChance > 0
    RegisterForSingleUpdate(config.radiantDialogueFrequency)
  EndIf
EndFunction

Function StopRadiantDialogue()
  if !bHasAIFF
    return
  EndIf
  Main.Info("SAPIENCE: Stopping Radiant Dialogue")
  UnregisterForUpdate()
EndFunction
