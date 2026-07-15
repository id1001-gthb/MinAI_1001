#!/bin/bash

HERIKA_SERVER="/var/www/html/HerikaServer"
XTRA_DIR="$HERIKA_SERVER/ext/minai_plugin/utils/xtra"
PATCH_SCRIPT="$XTRA_DIR/m_patch.py"

cd "$HERIKA_SERVER"
python3 "$PATCH_SCRIPT" main.php "$XTRA_DIR/main.php.src" "$XTRA_DIR/main.php.rpl"
