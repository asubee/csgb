CREATE TABLE wp_ggp_earth (
	earth_no int PRIMARY KEY,
	co2 int
);

CREATE TABLE wp_ggp_team (
	earth_no int PRIMARY KEY,
	team_no int PRIMARY KEY,
	name varchar,
	turn int,
	money int,
	co2 int
);

CREATE TABLE wp_ggp_action (
	earth_no int,
	team_no int,
	phase varchar,
	cardname varchar,
	money int,
	co2 int,
	require_turn int
};

CREATE TABLE wp_ggp_message (
	msg varchar
);

