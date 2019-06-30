#/bin/bash

if [ ! -f "docker-compose.yml" ]; then
  echo "Run $0 from the cservice-web top level directory"
  exit 1
fi

./bin/docker-compose -f docker-compose.yml up $*
