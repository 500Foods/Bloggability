#!/bin/bash

# Set the path to the configuration directory
config_dir="."

# Set the script name prefix (without the .json at the end)
script_name="bloggable"

# List of database types we can switch between
declare -a options=("MySQL" "SQLite" "DB2")

# Function to print usage
print_usage() {
    echo "Usage: $0 <option>"
    echo "Available options: ${options[*]}"
}

# Check if an option was provided
if [ -z "$1" ]; then
    echo "Error: No option provided."
    print_usage
    exit 1
fi

# Check if the provided option is valid
found=0
for option in "${options[@]}"; do
    if [ "$1" == "$option" ]; then
        found=1
        break
    fi
done

if [ $found -eq 0 ]; then
    echo "Error: Invalid option '$1'."
    print_usage
    exit 1
fi

# Check if the configuration file for the provided option exists
option_file="$config_dir/$script_name-$1.json"
if [ ! -f "$option_file" ]; then
    echo "Error: Configuration file '$option_file' does not exist."
    exit 1
fi

# Switch to the provided option
cp "$option_file" "$config_dir/$script_name.json"
echo "Configuration switched to $1 option."
