<?php
/*
Template Name: Big Stat
Description: Big Stat output, refreshed every 30 seconds
Author: Ian Cole ian.cole@gmail.com
Date: June 8th 2015
*/

	echo '<META HTTP-EQUIV="REFRESH" CONTENT="30">';

	the_post();
	the_content();
?>
