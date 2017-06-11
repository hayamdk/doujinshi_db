create table items ( id integer primary key autoincrement, metatitle text, title text, subtitle text,
	type text, size text, page integer,
	date integer, event_id integer, new_date integer, new_event_id integer,
	revision text, group_id integer,  volume text, series_id integer,
	form text, lang text, adult text,
	side text, copibon text, collection text, collabo text, printed text,
	circle_id text, circle_name text, author_id text, author_name text,
	original_id text, original_sub_id text,
	guest_circle_id text, guest_circle_name text, guest_id text, guest_name text,
	tag text,
	ref_id text );

create table events( id integer primary key autoincrement, name text );
create table circles( id integer primary key autoincrement, name text );
create table authors( id integer primary key autoincrement, name text );
create table originals( id integer primary key autoincrement, name text );
create table groups( id integer primary key autoincrement );
create table series( id integer primary key autoincrement, name text );
