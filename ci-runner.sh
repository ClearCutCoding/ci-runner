#!/usr/bin/env bash
set -e

#############################################################
# Basic usage:                        ./ci-runner.sh
# Without making file modifications:  ./ci-runner.sh --no-mods
#############################################################

# TERMINAL COLORS
COL_RED='\033[1;31m'
COL_YELLOW='\033[1;33m'
COL_GREEN='\033[0;32m'
COL_BLUE='\033[0;34m'
COL_NC='\033[0m' # No Color

ARG_NO_MODS="no"

parse_args()
{
    while [ $# -gt 0 ]
    do
        case "${1}" in
            --no-mods)
                ARG_NO_MODS="yes"
                shift
            ;;
            *)
                echo -e "\nERROR: UNKNOWN ARGUMENT ${1}\n"
                exit 1
            ;;
        esac
    done

    return
}
parse_args "$@"

# Main flow
function fnc_main()
{
    read_config

    fnc_fix_phpcs
    fnc_lint_yaml
    fnc_lint_twig
    fnc_phpunit
    fnc_phpcs
    fnc_phpmd
    fnc_phpstan
    fnc_psalm

    return
}

config_read_file() {
    (grep -E "^${2}=" -m 1 "${1}" 2>/dev/null || echo "VAR=__UNDEFINED__") | head -n 1 | cut -d '=' -f 2-;
}

config_get() {
    val="$(config_read_file ci-runner.cfg "${1}")";
    printf -- "%s" "${val}";
}

function read_config()
{
  VAR_PHPCSFIXER=$(config_get phpcsfixer)
  VAR_LINTYAML=$(config_get lintyaml)
  VAR_LINTTWIG=$(config_get linttwig)
  VAR_PHPCS=$(config_get phpcs)
  VAR_PHPUNIT=$(config_get phpunit)
  VAR_PHPMD=$(config_get phpmd)
  VAR_PHPSTAN=$(config_get phpstan)
  VAR_PSALM=$(config_get psalm)
}

function fnc_fix_phpcs()
{
    if [[ ${ARG_NO_MODS} == "yes" ]]; then
      echo -e "\n${COL_RED}IGNORE PHP-CS-FIXER${COL_NC}\n"
      return
    fi

    if [[ ${VAR_PHPCSFIXER} != "yes" ]]; then
      echo -e "\n${COL_RED}BYPASS PHP-CS-FIXER${COL_NC}\n"
      return
    fi

    echo -e "\n${COL_GREEN}START PHP-CS-FIXER${COL_NC}\n"

    (PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix)

    echo -e "\n${COL_GREEN}END PHP-CS-FIXER${COL_NC}\n"

    return
}

function fnc_lint_yaml()
{
    if [[ ${VAR_LINTYAML} != "yes" ]]; then
      echo -e "\n${COL_RED}BYPASS YAML LINT${COL_NC}\n"
      return
    fi

    echo -e "\n${COL_GREEN}START YAML LINT${COL_NC}\n"

    (bin/console lint:yaml config)
    (bin/console lint:yaml src)

    echo -e "\n${COL_GREEN}END YAML LINT${COL_NC}\n"

    return
}

function fnc_lint_twig()
{
    if [[ ${VAR_LINTTWIG} != "yes" ]]; then
      echo -e "\n${COL_RED}BYPASS TWIG LINT${COL_NC}\n"
      return
    fi

    echo -e "\n${COL_GREEN}START TWIG LINT${COL_NC}\n"

    (bin/console lint:twig src)

    echo -e "\n${COL_GREEN}END TWIG LINT${COL_NC}\n"

    return
}

function fnc_phpunit()
{
    if [[ ${VAR_PHPUNIT} != "yes" ]]; then
      echo -e "\n${COL_RED}BYPASS PHPUNIT${COL_NC}\n"
      return
    fi

    echo -e "\n${COL_GREEN}START PHPUNIT${COL_NC}\n"

    (bin/phpunit --configuration phpunit.xml.dist)

    echo -e "\n${COL_GREEN}END PHPUNIT${COL_NC}\n"

    return
}

function fnc_phpcs()
{
    if [[ ${VAR_PHPCS} != "yes" ]]; then
      echo -e "\n${COL_RED}BYPASS PHPCS${COL_NC}\n"
      return
    fi

    echo -e "\n${COL_GREEN}START PHPCS${COL_NC}\n"

    (vendor/bin/phpcs --report=checkstyle --extensions=php src tests)

    echo -e "\n${COL_GREEN}END PHPCS${COL_NC}\n"

    return
}

function fnc_psalm()
{
    if [[ ${VAR_PSALM} != "yes" ]]; then
      echo -e "\n${COL_RED}BYPASS PSALM${COL_NC}\n"
      return
    fi

    echo -e "\n${COL_GREEN}START PSALM${COL_NC}\n"

    (vendor/bin/psalm --show-info=true)

    echo -e "\n${COL_GREEN}END PSALM${COL_NC}\n"

    return
}

function fnc_phpmd()
{
    if [[ ${VAR_PHPMD} != "yes" ]]; then
      echo -e "\n${COL_RED}BYPASS PHPMD${COL_NC}\n"
      return
    fi

    echo -e "\n${COL_GREEN}START PHPMD${COL_NC}\n"

    (vendor/bin/phpmd src,tests text controversial,unusedcode)

    echo -e "\n${COL_GREEN}END PHPMD${COL_NC}\n"

    return
}

function fnc_phpstan()
{
    if [[ ${VAR_PHPSTAN} != "yes" ]]; then
      echo -e "\n${COL_RED}BYPASS PHPSTAN${COL_NC}\n"
      return
    fi

    echo -e "\n${COL_GREEN}START PHPSTAN${COL_NC}\n"

    (php -d memory_limit=-1 vendor/bin/phpstan analyse src tests)

    echo -e "\n${COL_GREEN}END PHPSTAN${COL_NC}\n"

    return
}

# RUN
fnc_main "$@"
