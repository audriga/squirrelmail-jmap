#!/bin/bash
# TODO ansiblize

CONTAINER_DIR="$HOME/ops/containers/squirrelmail"
SQUIRRELMAIL_VERSION=1.4.22
SUIRRELMAIL_URL="https://sourceforge.net/projects/squirrelmail/files/stable/${SQUIRRELMAIL_VERSION}/squirrelmail-webmail-${SQUIRRELMAIL_VERSION}.tar.gz/download?use_mirror=heanet"

wget "${SUIRRELMAIL_URL}" -O squirrelmail-webmail-${SQUIRRELMAIL_VERSION}.tar.gz
tar -xvzf squirrelmail-webmail-${SQUIRRELMAIL_VERSION}.tar.gz --strip-components=1
mkdir "$CONTAINER_DIR/localdata"
mkdir "$CONTAINER_DIR/localattach"
sudo chown -R 33:33 "$CONTAINER_DIR"
sudo chmod -R 777 "$CONTAINER_DIR"
cp ../resources/sqmail_config.php "$CONTAINER_DIR/config"

# Install OpenXPort
mkdir "$CONTAINER_DIR/plugins/openxport"
cp -r ../../../jmap-openxport/* "$CONTAINER_DIR/plugins/openxport"

# Install JMAP plugin
mkdir -P "$CONTAINER_DIR/plugins/jmap/jmap"
cp -r ../../ "$CONTAINER_DIR/plugins/jmap"
cp ../../jmap.php "$CONTAINER_DIR/plugins/jmap"

podman run --name squirrel -p 8087:80 -v "$CONTAINER_DIR:$CONTAINER_DIR" -w "$CONTAINER_DIR" docker.io/phpdockerio/php56-cli php -S 0.0.0.0:80
podman logs -f squirrel
