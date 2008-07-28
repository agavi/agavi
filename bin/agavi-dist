#! /bin/sh
# This file is part of the Agavi package.
# Copyright (c) 2005-2008 the Agavi Project.
#
# For the full copyright and license information, please view the LICENSE file
# that was distributed with this source code. You can also view the LICENSE
# file online at http://www.agavi.org/LICENSE.txt

# Set this to the path to the Agavi installation's source directory. This is
# the directory that contains the `agavi.php' file.
AGAVI_SOURCE_DIRECTORY="@PEAR-DIR@/agavi"

# Set this to the path to a PHP binary.
PHP_EXECUTABLE=$( which php )

# Message display shortcuts.
agavi_message_null()
{
	echo
}

agavi_message_notice()
{
	MESSAGE=$1
	echo "   [notice] ${1}"
}

agavi_message_warning()
{
	MESSAGE=$1
	echo "  [warning] ${1}"
}

agavi_message_error()
{
	MESSAGE=$1
	echo "    [error] ${1}"
}

agavi_message_fatal()
{
	MESSAGE=$1
	RETURN=$2
	echo "    [fatal] ${1}"
	exit ${2}
}

agavi_input()
{
	VARIABLE=$1
	MESSAGE=$2
	PROMPT=$3
	echo -n "        [?] ${2}${3} "
	read "${1}"
}

# Initial detection.
php_executable_exists()
{
	if test -x "${PHP_EXECUTABLE}"; then
		return 0
	else
		return 1
	fi
}

agavi_directory_exists()
{
	if test -d "${AGAVI_SOURCE_DIRECTORY}" -a -e "${AGAVI_SOURCE_DIRECTORY}/agavi.php"; then
		return 0
	else
		return 1
	fi
}

until php_executable_exists; do
	if [ -z "${PHP_EXECUTABLE}" ]; then
		PHP_EXECUTABLE="(unknown)"
	fi
	agavi_message_error "PHP not found at ${PHP_EXECUTABLE}."
	agavi_message_error "Please set the PHP_EXECUTABLE variable in the script"
	agavi_message_error "${0} to avoid this message."
	agavi_message_null
	agavi_input PHP_EXECUTABLE "Path to PHP executable" ":"
	agavi_message_null
done

until agavi_directory_exists; do
	if [ -z "${AGAVI_SOURCE_DIRECTORY}" ]; then
		AGAVI_SOURCE_DIRECTORY="(unknown)"
	fi
	agavi_message_error "No Agavi installation found in ${AGAVI_SOURCE_DIRECTORY}."
	agavi_message_error "Please set the AGAVI_SOURCE_DIRECTORY variable in the script"
	agavi_message_error "${0} to avoid this message."
	agavi_message_null
	agavi_input AGAVI_SOURCE_DIRECTORY "Path to Agavi source directory" ":"
	agavi_message_null
done

# Call build script.
${PHP_EXECUTABLE} -d memory_limit=4294967296 -f "${AGAVI_SOURCE_DIRECTORY}/build/agavi/script/agavi.php" -- --agavi-source-directory "${AGAVI_SOURCE_DIRECTORY}" $@

