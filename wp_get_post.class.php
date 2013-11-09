<?php
/* 
Class Name: wp_get_post
Class URI: https://github.com/mariusmandal/WPOO
Description: Wordpress ObjectOriented Post. Used to generate PHP object for a given WP POST
Author: Marius Mandal 
Version: 0.1 
Author URI: http://www.mariusmandal.no
*/
class wp_get_post {
	public function __construct($post) {
		$this->_raw($post);		
		$this->_title($post);
		$this->_content($post);
		$this->_thumbnail($post);
		$this->_author($post);
		$this->_content($post);
		$this->_url($post);
		$this->_category($post);
		
		$this->facebook = new stdClass;
		$this->facebook->shares = 0;
	}
	
	private function _category(&$post) {
		$this->categories = get_the_category();
		foreach($this->categories as $key => $category) {
			$this->categories[$key]->title = $category->name;
#			$this->categories[$key]->url = $category->slug;
			$this->categories[$key]->url = get_category_link($category->term_id);
		}
	}
	
	private function _raw(&$post) {
		$this->raw = $post;
		$this->date = $this->raw->post_date;		
		$this->ID = isset( $this->raw->ID ) ? $this->raw->ID : 0;
	}
	
	private function _content(&$post) {
		$content = apply_filters('the_content', get_the_content());
		$content = apply_filters('do_shortcode', $content);
		$this->content = str_replace(']]>', ']]&gt;', $content);
	}
	
	private function _url(&$post) {
		$this->url = apply_filters('the_permalink', get_permalink());
	}
	
	private function _title(&$post) {
		$this->title = apply_filters( 'the_title', get_the_title(), $this->ID );
		$this->title_attribute = the_title_attribute(array('echo'=>false));
	}
	
	private function _author(&$post) {
		$userdata = get_userdata( $post->post_author );
		$this->author = $userdata->data;
	}
	
	private function _thumbnail(&$post) {
		$image = get_post_thumbnail_id($post->ID);
		$this->image = new stdClass();

		if(!$image) {
			$this->image->ID = false;
			$this->image->url = $this->theme_dir . '/img/missing.jpg';
			return;
		}
		$this->image->ID = $image;
		$this->image->url = wp_get_attachment_url($this->image->ID);
		$source_data = wp_get_attachment_image_src($this->image->ID);
		$this->image->src = $source_data[0];
		$this->image->width = $source_data[1];
		$this->image->height = $source_data[2];
		#		$post->image->meta = wp_get_attachment_metadata($post->image->ID);
	}
	
	public function facebook_value() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/'.$this->url.'?method=GET&format=json&suppress_http_code=1&');
		curl_setopt($ch, CURLOPT_REFERER, $_SERVER['PHP_SELF']);
		curl_setopt($ch, CURLOPT_USERAGENT, "WPOO");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		
		$result = curl_exec( $ch );
		$result = json_decode($result);
		
		if( is_object( $result ) ) {
			$this->facebook->shares = $result->shares;
		} else {
			$this->facebook->shares = false;
		}
	}
}
?>