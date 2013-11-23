<?php
/* 
Class Name: WPOO_Author
Class URI: https://github.com/mariusmandal/WPOO
Description: Wordpress ObjectOriented Post. Used to generate PHP object for a given WP POST
Author: Marius Mandal 
Version: 0.1 
Author URI: http://www.mariusmandal.no
*/
class WPOO_Author {

    public $id;
    public $login;
    public $nicename;
    public $email;
    public $url;
    public $registered;
    public $display_name;
    public $firstname;
    public $lastname;
    public $nickname;
    public $description;
    public $facebook;
    public $title;
    public $link;

    public function __construct($post)
    {
        $wpUser = get_userdata($post->post_author);
        $this->id = $wpUser->ID;
        $this->login = $wpUser->user_login;
        $this->nicename = $wpUser->nicename;
        $this->email = $wpUser->email;
        $this->url = $wpUser->url;
        $this->registered = $wpUser->registered;
        $this->display_name = $wpUser->display_name;
        $this->firstname = $wpUser->firstname;
        $this->lastname = $wpUser->lastname;
        $this->nickname = $wpUser->nickname;
        $this->description = $wpUser->description;
        $this->facebook = get_the_author_meta( 'facebook', $this->id );
        $this->title = get_the_author_meta( 'title', $this->id );
        $this->link = get_author_posts_url( $this->id, $this->nicename );
    }

}
?>