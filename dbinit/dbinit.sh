#!/bin/bash

export PGPASSWORD="${PGPASSWORD:-$POSTGRES_PASSWORD}"
#psql=( psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --no-password )
psql=( psql --username "$POSTGRES_USER" --no-password )

for db in cservice local_db; do
  "${psql[@]}" --dbname postgres --set db="$db" <<-'EOSQL'
				CREATE DATABASE :"db" ;
			EOSQL
  echo
done

echo "$0: Setting up cservice db"; wget -O- https://raw.githubusercontent.com/UndernetIRC/gnuworld/master/doc/cservice.sql | ${psql[@]} --dbname cservice
echo "$0: Setting up local db"; wget -O- https://raw.githubusercontent.com/UndernetIRC/gnuworld/master/doc/local_db.sql | ${psql[@]} --dbname local_db
echo "$0: Loading themes into local_db..."
for theme in $(find /app/docs/gnuworld/themes/data -name "*.sql"); do
  cat $theme | ${psql[@]} --dbname local_db
done
