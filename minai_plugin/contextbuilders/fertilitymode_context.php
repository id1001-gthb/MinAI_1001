<?php
require_once("FillHerUp_context.php");

Function GetFertilityContext($name) {
    $ret = "";
    // Skip if no fertility state or male
    if (GetActorValue($name, "gender") != "female") {
        return $ret;
    }

    //error_log("Fertility $name - exec trace");    

    $state = strtolower(GetActorValue($name, "fertility_state"));
    if (empty($state)) {
        return $ret;
    }
    
    //error_log("Fertility $name $state - exec trace");    // debug

    //$isNarrator = ($GLOBALS["HERIKA_NAME"] == "The Narrator");
    $isNarrator = (strtolower($name) == "the narrator");
    
    //$isSelf = false; //GetTargetActor() == $name;
    
    // Pregnancy states are visible to everyone

    //fertility_state_father
    $s_father_msg = "";
    if (($state == "third_trimester") || ($state == "second_trimester") || ($state == "first_trimester")) {
        $s_father = GetActorValue($name, "fertility_state_father", true) ?? '';
        if (strlen($s_father) > 0) {
            if (strtolower($s_father) == 'the narrator')
                $s_father = $GLOBALS['PLAYER_NAME'];
            $s_father_msg = " <biological_father>The child's biological father is {$s_father}.</biological_father> ";
        }
    }
    
    if ($state == "third_trimester") {
        $ret .= "<pregnancy_status>{$name} is in the third trimester of pregnancy and is very visibly pregnant.{$s_father_msg}</pregnancy_status>\n";
    }
    elseif ($state == "second_trimester") {
        $ret .= "<pregnancy_status>{$name} is in the second trimester of pregnancy and is showing a noticeable baby bump.{$s_father_msg}</pregnancy_status>\n";
    }
    elseif ($state == "first_trimester") {
        $ret .= "<pregnancy_status>{$name} is in the first trimester of pregnancy, though it's not very noticeable yet.{$s_father_msg}</pregnancy_status>\n";
    }
    // Other states only visible to Narrator or self <fertility_status>
    //elseif ($isNarrator || $isSelf) {
    //elseif (!$isNarrator) {
        if ($state == "ovulating") {
            $ret .= "<fertility_status><is_fertile>{$name}</is_fertile> is currently ovulating and fertile, there is a high chance of getting pregnant.</fertility_status>\n";
            //error_log("Fertility $name $state - exec trace");
        }
        elseif ($state == "pms") {
            $ret .= "<fertility_status><not_fertile>{$name}</not_fertile> is experiencing PMS symptoms.</fertility_status>\n";
        }
        elseif ($state == "menstruating") {
            $ret .= "<fertility_status><not_fertile>{$name}</not_fertile> is currently menstruating.</fertility_status>\n";
            //error_log("Fertility $name $state - exec trace");
        }
        elseif ($state == "normal") {
            $ret .= "<fertility_status><not_fertile>{$name}</not_fertile> is not near ovulation, unlikely to get pregnant.</fertility_status>\n";
        }
    //}

    // Add Fill Her Up context
    $ret .= GetFillHerUpContext($name);

    if ($ret != "") {
        $ret .= "\n";
    }
    return $ret;
}

Function GetFertilityContextShort($name, $b_show_pregnant=true, $b_show_fertility=false) {
    $ret = "";
    // Skip if no fertility state or male
    if (GetActorValue($name, "gender") != "female") {
        return $ret;
    }

    //error_log("Fertility $name - exec trace");    

    $state = strtolower(GetActorValue($name, "fertility_state"));
    if (empty($state)) {
        return $ret;
    }
    
    //error_log("Fertility $name $state - exec trace");    // debug

    //$isNarrator = ($GLOBALS["HERIKA_NAME"] == "The Narrator");
    $isNarrator = (strtolower($name) == "the narrator");
    if ($isNarrator) {
        return $ret;
    }
    //$isSelf = false; //GetTargetActor() == $name;
    


    //fertility_state_father
    $s_father_msg = "";
    if (($state == "third_trimester") || ($state == "second_trimester") || ($state == "first_trimester")) {
        $s_father = GetActorValue($name, "fertility_state_father", true) ?? '';
        if (strlen($s_father) > 0) {
            if (strtolower($s_father) == 'the narrator')
                $s_father = $GLOBALS['PLAYER_NAME'];
            $s_father_msg = "the father is {$s_father}";
        }
    }

    if ($b_show_pregnant) {
        // Pregnancy states are visible to everyone
        if ($state == "third_trimester") {
            $ret .= "pregnant in third trimester {$s_father_msg}";
        } elseif ($state == "second_trimester") {
            $ret .= "pregnant in second trimester {$s_father_msg}";
        } elseif ($state == "first_trimester") {
            $ret .= "pregnant in first trimester {$s_father_msg}";
        }
    }
    if ($b_show_fertility) {  // fertility state not visible by default
        if ($state == "ovulating") {
            $ret .= "currently ovulating and fertile";
        //} elseif ($state == "pms") {
        //    $ret .= "experiencing PMS symptoms";
        } elseif ($state == "menstruating") {
            $ret .= "currently menstruating";
        //} elseif ($state == "normal") {
        //    $ret .= "";
        }
    }
    
    return $ret;
}
