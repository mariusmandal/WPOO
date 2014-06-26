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
    public $user_email;
    public $url;
    public $image;
    public $company_name;
    public $registered;
    public $display_name;
    public $firstname;
    public $lastname;
    public $nickname;
    public $description;
    public $facebook_url;
    public $title;
    public $link;

    public function __construct($wpUser)
    {
        $this->id = $wpUser->ID;
        if(has_wp_user_avatar($this->id)) {
            $this->image = get_wp_user_avatar_src($this->id, 'small');
        }
        else {
            $this->image = 'http://grafikk.ukm.no/placeholder/person.jpg';
        }
        $this->company_name = get_the_author_meta( 'title', $this->id );
        $this->login = $wpUser->user_login;
        $this->nicename = $wpUser->nicename;
        $this->user_email = $wpUser->user_email;
        $this->url = $wpUser->url;
        $this->registered = $wpUser->registered;
        $this->display_name = $wpUser->display_name;
        $this->firstname = $wpUser->firstname;
        $this->lastname = $wpUser->lastname;
        $this->nickname = $wpUser->nickname;
        $this->description = $wpUser->description;
        $this->facebook_url = get_the_author_meta( 'facebook', $this->id );
        $this->title = get_the_author_meta( 'title', $this->id );
        $this->link = get_author_posts_url( $this->id, $this->nicename );
    }

}
?>