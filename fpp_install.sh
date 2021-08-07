#!/bin/bash
pushd $(dirname $(which $0))
target_PWD=$(readlink -f .)
/opt/fpp/scripts/update_plugin ${target_PWD##*/}
/bin/cp RUN-COUNTDOWN-SCRIPT.sh /home/fpp/media/scripts
. /opt/fpp/scripts/common
setSetting restartFlag 1
popd
