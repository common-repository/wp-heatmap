<?php
/*
Plugin Name: WordPress Heat Map
Plugin URI: http://www.engadgeted.net/
Description: Template tags for a heat map of category links, archive links and author links. This version is for WordPress 1.5 "Strayhorn", version 1.0 of this plugin is still compatible with WordPress 1.2 "Mingus".
Version: 1.1
Author: Christoph Wimmer
Author URI: http://www.engadgeted.net/
*/

function heatmap_categories($smallest=10, $largest=36, $unit="pt", $cold="00f", $hot="f00", $before='', $after='&nbsp', $exclude='') {
	global $tablecategories, $tableposts, $tablepost2cat, $wpdb, $category_posts;
	global $querystring_start, $querystring_equal, $querystring_separator;
	
	$exclusions = '';
	if (!empty($exclude)) {
		$excats = preg_split('/[\s,]+/',$exclude);
		if (count($excats)) {
			foreach ($excats as $excat) {
				$exclusions .= ' AND '.$tablecategories.'.cat_ID <> ' . intval($excat) . ' ';
			}
		}
	}

	$results = $wpdb->get_results("SELECT $tablecategories.cat_ID AS `id`, $tablecategories.cat_name AS `name`, $tablecategories.category_nicename AS `nicename`, count($tablepost2cat.rel_id) as `posts` FROM $tablecategories, $tablepost2cat WHERE $tablecategories.cat_ID = $tablepost2cat.category_id $exclusions GROUP BY $tablecategories.cat_id ORDER BY cat_name ASC");

	foreach ($results as $result) {
		$counts[] = $result->posts;
	}
	$min = min($counts);
	$max = max($counts);
	$spread = $max - $min;
	
	if ($largest != $smallest) {
		$fontspread = $largest - $smallest;
		if ($spread != 0) {
			$fontstep = $fontspread / $spread;
		} else {
			$fontstep = 0;
		}
	}
	if ($hot != $cold) {		
		for ($i = 0; $i < 3; $i++) {
			$coldval[] = hexdec($cold[$i]);
			$hotval[] = hexdec($hot[$i]);
			$colorspread[] = hexdec($hot[$i]) - hexdec($cold[$i]); 
			if ($spread != 0) {
				$colorstep[] = (hexdec($hot[$i]) - hexdec($cold[$i])) / $spread;
			} else {
				$colorstep[] = 0;
			}
		}
	}
	
	foreach ($results as $result) {
		$url  = get_category_link($result->id);
		$text = stripslashes($result->name);
		$fraction = ($result->posts - $min);
		$fontsize = $smallest + ($fontstep * $fraction);
		$color = "";
		for ($i = 0; $i < 3; $i++) {
			$color .= dechex($coldval[$i] + ($colorstep[$i] * $fraction));
		}
		$style = "style=\"";
		if ($largest != $smallest) {
			$style .= "font-size:".round($fontsize).$unit.";";
		}
		if ($hot != $cold) {
			$style .= "color:#".$color.";";
		}
		$style .= "\"";
		echo $before
			."<a href=\""
			.$url
			."\" title=\""
			.$result->posts
			." entries\" "
			.$style
			.">"
			.$text
			."</a>"
			.$after
			."\n";
	}
}

function heatmap_archives($smallest=10, $largest=36, $unit="pt", $cold="00f", $hot="f00", $before='', $after='&nbsp') {	
    global $tableposts;
    global $querystring_start, $querystring_equal, $querystring_separator, $month, $wpdb;

	$now = current_time('mysql');
	$results = $wpdb->get_results("SELECT DISTINCT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) AS `posts` FROM $tableposts WHERE post_date < '$now' AND post_status = 'publish' GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC" . $limit);
	
	foreach ($results as $result) {
		$counts[] = $result->posts;
	}
	$min = min($counts);
	$max = max($counts);
	$spread = $max - $min;
	
	if ($largest != $smallest) {
		$fontspread = $largest - $smallest;
		if ($spread != 0) {
			$fontstep = $fontspread / $spread;
		} else {
			$fontstep = 0;
		}
	}
	if ($hot != $cold) {		
		for ($i = 0; $i < 3; $i++) {
			$coldval[] = hexdec($cold[$i]);
			$hotval[] = hexdec($hot[$i]);
			$colorspread[] = hexdec($hot[$i]) - hexdec($cold[$i]); 
			if ($spread != 0) {
				$colorstep[] = (hexdec($hot[$i]) - hexdec($cold[$i])) / $spread;
			} else {
				$colorstep[] = 0;
			}
		}
	}

	foreach ($results as $result) {
		$url  = get_month_link($result->year, $result->month);
		$text = sprintf('%s %d', $month[zeroise($result->month,2)], $result->year);
		$fraction = ($result->posts - $min);
		$fontsize = $smallest + ($fontstep * $fraction);
		$color = "";
		for ($i = 0; $i < 3; $i++) {
			$color .= dechex($coldval[$i] + ($colorstep[$i] * $fraction));
		}
		$style = "style=\"";
		if ($largest != $smallest) {
			$style .= "font-size:".round($fontsize).$unit.";";
		}
		if ($hot != $cold) {
			$style .= "color:#".$color.";";
		}
		$style .= "\"";
		echo $before
			."<a href=\""
			.$url
			."\" title=\""
			.$result->posts
			." entries\" "
			.$style
			.">"
			.$text
			."</a>"
			.$after
			."\n";
	}
}

?>