#!/usr/bin/env bash
set -u

targets=(
  "php82|^9.0|Laravel 9|PHP 8.2"
  "php82|^10.0|Laravel 10|PHP 8.2"
  "php83|^11.0|Laravel 11|PHP 8.3"
  "php83|^12.0|Laravel 12|PHP 8.3"
  "php85|^13.0|Laravel 13|PHP 8.5"
)

results=()
failures=0

for target in "${targets[@]}"; do
    IFS="|" read -r service constraint laravel_label php_label <<<"$target"
    label="${laravel_label} / ${php_label}"

    echo "==> ${label}"

    if docker compose run --rm "$service" bin/test-target.sh "$constraint"; then
        results+=("${laravel_label}|${php_label}|PASS")
    else
        results+=("${laravel_label}|${php_label}|FAIL")
        failures=$((failures + 1))
    fi

    echo
done

printf "%-12s %-8s %-6s\n" "Laravel" "PHP" "Result"
printf "%-12s %-8s %-6s\n" "-------" "---" "------"

for result in "${results[@]}"; do
    IFS="|" read -r laravel_label php_label status <<<"$result"
    printf "%-12s %-8s %-6s\n" "$laravel_label" "$php_label" "$status"
done

if [[ $failures -gt 0 ]]; then
    exit 1
fi
