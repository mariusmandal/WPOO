<?php
/* 
Class Name: WPOO_Comment
Class URI: https://github.com/mariusmandal/WPOO
Description: Wordpress ObjectOriented Comment. Used to generate PHP object for a given WP COMMENT
Author: Marius Mandal 
Version: 0.1 
Author URI: http://www.mariusmandal.no
*/
class WPOO_Comment {
	public function __construct( $comment_data ) {
		foreach( $comment_data as $key => $val) {
			$new_key = str_replace('comment_', '', $key);
			$this->$new_key = $val;
		}
	}
}