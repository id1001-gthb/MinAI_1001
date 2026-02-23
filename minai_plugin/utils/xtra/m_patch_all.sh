#!/bin/bash

HERIKA_SERVER="/var/www/html/HerikaServer"
XTRA_DIR="$HERIKA_SERVER/ext/minai_plugin/utils/xtra"
PATCH_SCRIPT="$XTRA_DIR/m_patch.py"

cd "$HERIKA_SERVER"
python3 "$PATCH_SCRIPT" main.php "$XTRA_DIR/main.php.src" "$XTRA_DIR/main.php.rpl"

cd "$HERIKA_SERVER/lib"
python3 "$PATCH_SCRIPT" data_functions.php "$XTRA_DIR/data_functions.php.src" "$XTRA_DIR/data_functions.php.rpl"
python3 "$PATCH_SCRIPT" data_functions.php "$XTRA_DIR/data_functions2.php.src" "$XTRA_DIR/data_functions2.php.rpl"
python3 "$PATCH_SCRIPT" data_functions.php "$XTRA_DIR/data_functions3.php.src" "$XTRA_DIR/data_functions3.php.rpl"

cd "$HERIKA_SERVER/processor"
python3 "$PATCH_SCRIPT" postrequest.php "$XTRA_DIR/postrequest.php.src" "$XTRA_DIR/postrequest.php.rpl"

cd "$HERIKA_SERVER/prompts"
python3 "$PATCH_SCRIPT" command_prompt.php "$XTRA_DIR/command_prompt.php.src" "$XTRA_DIR/command_prompt.php.rpl"
