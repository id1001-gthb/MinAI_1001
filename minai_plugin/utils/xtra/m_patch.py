#!/usr/bin/env python
'''
m_patch.py - python tool to patch source files

Command line syntax: m_patch.py source_file.ext search_block.txt replace_block.txt
where arguments are:
- source_file.ext is the source file to patch
- search_block.txt is the file containing the text block that must be replaced
- replace_block.txt is the file containing the text block replacement

Features:
- Logs messages and errors to m_patch.log with timestamps
- Verifies files exist, are readable and writable
- Verifies source file contains search block
- Creates backup of source file (source_file.ext.original)
- Replaces text block in source file
'''

import sys
import os
import shutil
import logging

def setup_logging():
    logging.basicConfig(
        filename='/var/www/html/HerikaServer/log/m_patch.log',
        level=logging.INFO,
        format='%(asctime)s - %(levelname)s - %(message)s',
        datefmt='%Y-%m-%d %H:%M:%S'
    )
    # Also log to console
    console = logging.StreamHandler()
    console.setLevel(logging.INFO)
    formatter = logging.Formatter('%(message)s')
    console.setFormatter(formatter)
    logging.getLogger('').addHandler(console)

def log_message(message, level='info'):
    if level == 'info':
        logging.info(message)
    elif level == 'error':
        logging.error(message)
    elif level == 'warning':
        logging.warning(message)

def main():
    setup_logging()
    
    log_message("Starting m_patch.py execution")

    # Check arguments
    if len(sys.argv) != 4:
        msg = "Error: Invalid arguments. Usage: m_patch.py source_file.ext search_block.txt replace_block.txt"
        log_message(msg, 'error')
        sys.exit(1)

    source_file = sys.argv[1]
    search_block_file = sys.argv[2]
    replace_block_file = sys.argv[3]

    files_to_check = [source_file, search_block_file, replace_block_file]

    # Verify files exist, are readable and writable
    try:
        for file_path in files_to_check:
            if not os.path.exists(file_path):
                log_message(f"Error: File '{file_path}' does not exist.", 'error')
                sys.exit(1)
            
            if not os.access(file_path, os.R_OK):
                log_message(f"Error: File '{file_path}' is not readable.", 'error')
                sys.exit(1)
                
            if not os.access(file_path, os.W_OK):
                log_message(f"Error: File '{file_path}' is not writable.", 'error')
                sys.exit(1)
        
        log_message(f"Verification successful: All files ({', '.join(files_to_check)}) exist, are readable and writable.")

        # Read files
        with open(source_file, 'r', encoding='utf-8') as f:
            source_content = f.read()
        
        with open(search_block_file, 'r', encoding='utf-8') as f:
            search_content = f.read()
            
        with open(replace_block_file, 'r', encoding='utf-8') as f:
            replace_content = f.read()

        # Verify source contains search block
        if search_content not in source_content:
            log_message(f"The search block from '{search_block_file}' was not found in '{source_file}', no patch applyed.", 'info')
            sys.exit(1)
        
        log_message(f"Search block found in '{source_file}', need to patch.")

        # Create backup
        backup_file = source_file + ".original"
        if not os.path.exists(backup_file):
            shutil.copy2(source_file, backup_file)
            log_message(f"Backup file created at '{backup_file}'.")
        else:
            log_message(f"Backup file '{backup_file}' already exists. Skipping backup creation.")

        # Replace content
        new_source_content = source_content.replace(search_content, replace_content)

        # Write back to source file
        with open(source_file, 'w', encoding='utf-8') as f:
            f.write(new_source_content)
            
        log_message(f"Successfully patched '{source_file}'.")

    except Exception as e:
        log_message(f"An unexpected error occurred: {str(e)}", 'error')
        sys.exit(1)

if __name__ == "__main__":
    main()
