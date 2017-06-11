#!/bin/sh

rm *.db
sqlite3 doujin.db < initdb.sql
sqlite3 metadata.db < initmetadata.sql
