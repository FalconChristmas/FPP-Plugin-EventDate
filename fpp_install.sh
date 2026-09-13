#!/bin/bash
set -e
pushd $(dirname $(which $0))
target_PWD=$(readlink -f .)
/opt/fpp/scripts/update_plugin ${target_PWD##*/}
/bin/cp RUN-COUNTDOWN-SCRIPT.sh /home/fpp/media/scripts
popd
