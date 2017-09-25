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
		setup_postdata( $post );
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
		$this->_meta($post);

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
	
	public function getContentBeforeMore() {
		$p = preg_split( '/<span id=\"more-(.*)?"><\/span>/', $this->content );
		return $p[0];
	}	
	public function getContentAfterMore() {
		$p = preg_split( '/<span id=\"more-(.*)?"><\/span>/', $this->content );
		return $p[1];
	}
	
	private function _meta( &$post ) {
		$meta = get_post_meta( $this->ID );
		$this->meta = new stdClass();
		if( is_array( $meta ) ) {
			foreach( $meta as $key => $val ) {
				if( is_array( $val ) && sizeof( $val ) == 1 ) {
					$this->meta->$key = $val[0];
				} else {
					$this->meta->$key = $val;
				}
			}
		}
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
		$this->categories = get_the_category($this->ID);
		foreach($this->categories as $key => $category) {
			$this->categories[$key]->title = $category->name;
#			$this->categories[$key]->url = $category->slug;
			$kat_link = get_category_link($category->term_id);
			if( !strpos( $kat_link, '/blog/' ) ) {
				$kat_link_wb = str_replace(array('category/','author/'),array('blog/category/','blog/author/'),$kat_link);
				$this->categories[$key]->url_w_blog = $kat_link_wb;
			} else {
				$this->categories[$key]->url_w_blog = $kat_link;
			}
			$this->categories[$key]->url = $kat_link;
			
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
			$this->lead = strip_tags( substr( $this->content, 16, $stop-16) );
		// Find first sentence(s)
		} else {
			$excerpt = strip_tags($this->content);
			
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
				$this->lead = strip_tags( $excerpt );
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
		$this->url = apply_filters('the_permalink', get_permalink($this->ID));
	}
	
	private function _title(&$post) {
		$this->title = apply_filters( 'the_title', get_the_title($this->ID), $this->ID );
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
		#$this->image->url = wp_get_attachment_url($this->image->ID);
		$source_data = wp_get_attachment_image_src($this->image->ID, 'large');
		if( is_bool($source_data) && !$source_data ) {
			$source_data = wp_get_attachment_image_src($this->image->ID, 'medium');
		}
		if( is_bool($source_data) && !$source_data ) {
			$source_data = wp_get_attachment_image_src($this->image->ID);
		}
		
		$full = wp_get_attachment_image_src( $this->image->ID, 'full' );
		$this->image->full 		= new stdClass();
		$this->image->full->url	= $full[0];
		$this->image->full->width	= $full[1];
		$this->image->full->height	= $full[2];
		
		$this->og_image = $this->image->full;

		$large = wp_get_attachment_image_src( $this->image->ID, 'large' );
		$this->image->large = new stdClass();
		$this->image->large->src = $large[0];
		$this->image->large->width = $large[1];
		$this->image->large->height = $large[2];

		$medium = wp_get_attachment_image_src( $this->image->ID, 'medium' );
		$this->image->medium = new stdClass();
		$this->image->medium->src = $medium[0];
		$this->image->medium->width = $medium[1];
		$this->image->medium->height = $medium[2];		
		
		if( !is_bool($large) ) {
			$source_data = $large;
		} elseif( !is_bool($medium) ) {
			$source_data = $medium;
		} else {
			$source_data = $full;
		}
		
		$this->image->src = $source_data[0];
		$this->image->url = $source_data[0];
		$this->image->width = $source_data[1];
		$this->image->height = $source_data[2];
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
