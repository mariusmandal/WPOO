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
		$this->_load_data( $comment_data );
		$this->_load_gravatar();
	}
	
	private function _load_data( $comment_data ) {
		foreach( $comment_data as $key => $val) {
			$new_key = str_replace('comment_', '', $key);
			$this->$new_key = $val;
		}
	}
	
	private function _load_gravatar() {
		$this->avatar = get_avatar( $this->author_email, 96, 'mm' );
		preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $this->avatar, $matches);
		$this->avatar_url = $matches[1];
	}
}
