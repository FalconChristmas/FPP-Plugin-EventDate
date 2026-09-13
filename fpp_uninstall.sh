#!/bin/bash
# This plugin registers a command type via commands/descriptions.json; fppd only
# reads that at startup, so ask for a restart so the command doesn't linger.
( set +u; source "${FPPDIR:-/opt/fpp}/scripts/common" && setSetting restartFlag 1 ) || true
