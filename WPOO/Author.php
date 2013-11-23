<?php
/* 
Class Name: WPOO_Post
Class URI: https://github.com/mariusmandal/WPOO
Description: Wordpress ObjectOriented Post. Used to generate PHP object for a given WP POST
Author: Marius Mandal 
Version: 0.1 
Author URI: http://www.mariusmandal.no
*/
class WPOO_Author {

    protected $id;
    protected $login;
    protected $nicename;
    protected $email;
    protected $url;
    protected $registered;
    protected $display_name;
    protected $firstname;
    protected $lastname;
    protected $nickname;
    protected $description;
    protected $facebook;
    protected $title;
    protected $link;

    public function __construct($wpUser)
    {
        $this->id = $wpUser->data->ID;
        $this->login = $wpUser->data->user_login;
        $this->nicename = $wpUser->data->nicename;
        $this->email = $wpUser->data->email;
        $this->url = $wpUser->data->url;
        $this->registered = $wpUser->data->registered;
        $this->display_name = $wpUser->data->display_name;
        $this->firstname = $wpUser->data->firstname;
        $this->lastname = $wpUser->data->lastname;
        $this->nickname = $wpUser->data->nickname;
        $this->description = $wpUser->data->description;
        $this->facebook = get_the_author_meta( 'facebook', $this->id );
        $this->title = get_the_author_meta( 'title', $this->id );
        $this->link = get_the_author_link();
    }

}
?>