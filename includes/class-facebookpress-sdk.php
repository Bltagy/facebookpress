<?php

/**
 * To instantiate a new Facebook\Facebook service.
 *
 * @link       http://bltagy.com
 * @since      1.0.0
 *
 * @package    Facebookpress
 * @subpackage Facebookpress/includes
 */

/**
 * The Facebook\Facebook service class provides an easy interface 
 * for working with all the components of the SDK
 * @package    Facebookpress
 * @subpackage Facebookpress/includes
 * @author     Ahmed Bltagy <ahmed@bltagy.com>
 */
class Facebookpress_SDK {

	/**
	 * The Facebook object calss.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected $app_id;

	/**
	 * The Facebook object calss.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected $app_secret;

	/**
	 * The generated token.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected $token;

	/**
	 * The Facebook object calss.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	public $fb;

	/**
	 * Initialize FB class.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->app_id = Facebookpress::get_option('app_id');
		$this->app_secret = Facebookpress::get_option('app_secret');

		if ( ! empty( $this->app_id ) && ! empty( $this->app_secret ) ){
			$fb = new Facebook\Facebook([
			  'app_id' => $this->app_id,
			  'app_secret' => $this->app_secret,
			  'default_graph_version' => 'v2.5',
			]);
			$this->fb = $fb;
		}else{
			$this->fb =false;
		}
	}

	/**
	 * Instantiate a new Facebook\Facebook service,
	 *
	 * @since    1.0.0
	 */
	public function login_button() {

		$token = Facebookpress::get_option('auth_token');
		$nonce = wp_create_nonce( 'fp-nonce' );
		$revoke_url =  admin_url( 'admin.php?page=facebookpress-setting&revoke&_wpnonce='.$nonce);

		if (  false !== $this->fb && empty( $token )) {

			$callback_url = Facebookpress::get_option('callback_url');
			$helper = $this->fb->getRedirectLoginHelper();
			$permissions = ['email', 'user_likes']; // optional
			$callback_url =  admin_url( 'admin.php?page=facebookpress-setting&_wpnonce='.$nonce);
			$loginUrl = $helper->getLoginUrl($callback_url, $permissions);
			$html_url =  '<br><br><br><a href="' . $loginUrl . '" class="button button-primary">Log in with Facebook!</a>';
		}elseif ( !empty( $token ) ) {

			$html_url =  '<br><br><br><a href="" class="button button-primary fb-button disabled" disabled="disabled">Already with Facebook!</a> <a href="' . $revoke_url . '" class="button button-primary">Revoke</a>';

		}else{

			$html_url =  '<br><br><p>Please enter App ID and App Secret	 to activate the button.</p><br><a href="" class="button button-primary fb-button disabled" disabled="disabled">Log in with Facebook!</a>';

		}
		echo $html_url;

	}	

	/**
	 * Check whether token exist or not
	 *
	 * @since    1.0.0
	 * @return boolean
	 */
	public function check_token() {
		$token = Facebookpress::get_option('fb_token');
		if ( isset( $token ) && ! empty( $token )  )
			return true;
		return false;

	}

	/**
	 * Check whether the info is completed or not
	 *
	 * @since    1.0.0
	 * @return boolean
	 */
	public function is_info_complete() {
		

	}

}

