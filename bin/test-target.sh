#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
    echo "Usage: $0 <illuminate/database constraint>" >&2
    exit 1
fi

constraint="$1"
source_dir="/app"
tmp_root="${PACKAGE_TMP_DIR:-/worktmp}"
mkdir -p "$tmp_root"
work_dir="$tmp_root/$(basename "$0" .sh)-$$"
trap 'rm -rf "$work_dir"' EXIT

rm -rf "$work_dir"
cp -R "$source_dir"/. "$work_dir"
cd "$work_dir"

composer update "illuminate/database:${constraint}" "phpunit/phpunit" --with-all-dependencies
composer validate
vendor/bin/phpunit
