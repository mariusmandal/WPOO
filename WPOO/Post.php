<?php
/* 
Class Name: WPOO_Post
Class URI: https://github.com/mariusmandal/WPOO
Description: Wordpress ObjectOriented Post. Used to generate PHP object for a given WP POST
Author: Marius Mandal 
Version: 0.1 
Author URI: http://www.mariusmandal.no
*/
class WPOO_Post {
	var $comments = false;

	public function __construct($post) {
		$this->_find_wp_themedir();
		$this->_raw($post);		
		$this->_title($post);
		$this->_content($post);
		$this->_thumbnail($post);
		$this->_author($post);
		$this->_url($post);
		$this->_category($post);
		$this->_excerpt($post);
		$this->_lead($post);

		$pattern = "/<p[^>]*><\\/p[^>]*>/"; 
		$this->content_wo_lead = preg_replace($pattern, '', str_replace($this->lead, '', $this->content));
			
		$this->facebook = new stdClass;
		$this->facebook->shares = 0;
		
	}
	
	public function comments() {
		if(!$this->comments)
			$this->load_comments();
		
		return $this->comments;
	}
	
	private function load_comments() {
		$comments = get_comments( array('status' => 'approve',
								        'post_id' => $this->ID) 
							    );
		foreach( $comments as $comment ) {
			$this->comments[] = new WPOO_Comment( $comment );
		}
	}
	
	private function _find_wp_themedir() {
		if(function_exists('get_stylesheet_directory_uri')) {
			$this->theme_dir = get_stylesheet_directory_uri();
		}
	}
	
	private function _category(&$post) {
		$this->categories = get_the_category();
		foreach($this->categories as $key => $category) {
			$this->categories[$key]->title = $category->name;
#			$this->categories[$key]->url = $category->slug;
			$this->categories[$key]->url = get_category_link($category->term_id);
		}
	}
	
	private function _excerpt( $post ) {
		$this->list_excerpt = implode(' ', array_slice(explode(' ', strip_tags($this->content) ), 0, 55));
	}
	
	private function _lead( $post ) {
		// Find manual excerpt
		if( !empty( $post->post_excerpt )) {
			$this->lead = strip_tags( $post->post_excerpt );
		// Find p class = lead "manual auto"-excerpt
		} elseif( strpos( $this->content, '<p class="lead">' ) === 0 ) {
			$stop = strpos( $this->content, '</p>' );
			$this->lead = substr( $this->content, 16, $stop-16);
		// Find first sentence(s)
		} else {
			$excerpt = get_the_excerpt();
			$excerpt = implode(' ', array_slice(explode(' ', $excerpt), 0, 40));
			$stop = strrpos( $excerpt, '. ');
			if($stop > 0) {
				$this->lead = substr($excerpt, 0, ($stop+1));
				// If lead text more than a tweet
				if( $stop > 140 ) {
					$lead = substr($excerpt, 0, 140);
					// Is there a period within tweet-length lead?
					$dotpos = strrpos($lead, '.');
					if( $dotpos ) {
						$leadstop = ++$dotpos;
						$dotdotdot = '';
					} else {					
						$leadstop = strrpos( $lead, ' ');
						$dotdotdot = '...';
					}
					$this->lead = substr($lead, 0, $leadstop) . $dotdotdot;
				}
			} else {
				$this->lead = $excerpt;
			}
		}
	}
	
	private function _raw(&$post) {
		$this->raw = $post;
		$this->date = $this->raw->post_date;
		$this->modified = $this->raw->post_modified;
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
	    $this->author = new WPOO_Author(get_userdata($post->post_author));
	}
	
	private function _thumbnail(&$post) {
		$image = get_post_thumbnail_id($post->ID);
		$this->image = new stdClass();
		
		if(!$image) {
			// CHECK POST FOR IMAGES
			$output = preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $this->content, $matches);  
			if(is_array($matches) && isset($matches[1]) && isset($matches[1][0])) {
				$image = $matches[1][0];
				if( strpos( $image, 'wp-includes/images/smilies') !== false ) {
					// TO-DO
					// SHOULD BE A WHILE LOOP
					if(isset($matches[1]) && isset($matches[1][1])) {
						$image = $matches[1][1];
					} else {
						$image = '';
					}
				}
			} else {
				$image = '';
			}
			// Do not use thumb-size
			$image = str_replace('-150x150', '', $image );

			if(!empty($image)) {
				$this->image->ID = 1;
				$this->image->url = $image;
				$data = @getimagesize( $image );
				if($data) {
					list($width, $height, $type, $attr) = $data;
					$this->image->src = $image;
					$this->image->width = $width;
					$this->image->height = $height;
					
					$this->og_image 		= new stdClass();
					$this->og_image->url	= $image;
					$this->og_image->width	= $width;
					$this->og_image->height	= $height;
					return;
				}
			}
			
			$this->image->ID = false;
			$this->image->url = defined('THEME_DEFAULT_IMAGE') 
									? THEME_DEFAULT_IMAGE 
									: 'http://placehold.it/930x620';
			return;
		}
		$this->image->ID = $image;
		$this->image->url = wp_get_attachment_url($this->image->ID);
		$source_data = wp_get_attachment_image_src($this->image->ID);
		$this->image->src = $source_data[0];
		$this->image->width = $source_data[1];
		$this->image->height = $source_data[2];
		
		$full = wp_get_attachment_image_src( $this->image->ID, 'full' );
		$this->og_image 		= new stdClass();
		$this->og_image->url	= $full[0];
		$this->og_image->width	= $full[1];
		$this->og_image->height	= $full[2];
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