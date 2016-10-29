<?php
/**
 *
 */

class FacebookpressSetting {
	private $facebookpress_setting_options;

	private $admin;

	public function __construct( $admin ) {

		$this->admin = $admin;

	}

	public function facebookpress_setting_add_plugin_page() {
		add_menu_page(
			'Facebookpress Setting', // page_title
			'Facebookpress', // menu_title
			'manage_options', // capability
			'facebookpress-setting', // menu_slug
			array( $this, 'facebookpress_setting_create_admin_page' ), // function
			'dashicons-facebook', // icon_url
			3 // position
		);
	}

	public function facebookpress_setting_create_admin_page() {
		$this->facebookpress_setting_options = get_option( 'facebookpress_setting_option' ); ?>

		<div class="wrap">
			<h2>Facebookpress_Setting</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'facebookpress_setting_option_group' );
					do_settings_sections( 'facebookpress-setting-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function facebookpress_setting_page_init() {
		register_setting(
			'facebookpress_setting_option_group', // option_group
			'facebookpress_setting_option', // option_name
			array( $this, 'facebookpress_setting_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'facebookpress_setting_setting_section', // id
			'Settings', // title
			array( $this, 'facebookpress_setting_section_info' ), // callback
			'facebookpress-setting-admin' // page
		);

		add_settings_field(
			'app_id', // id
			'App ID', // title
			array( $this, 'app_id_callback' ), // callback
			'facebookpress-setting-admin', // page
			'facebookpress_setting_setting_section' // section
		);

		add_settings_field(
			'app_secret', // id
			'App Secret', // title
			array( $this, 'app_secret_callback' ), // callback
			'facebookpress-setting-admin', // page
			'facebookpress_setting_setting_section' // section
		);

		add_settings_field(
			'page_id', // id
			'page ID', // title
			array( $this, 'page_id_callback' ), // callback
			'facebookpress-setting-admin', // page
			'facebookpress_setting_setting_section' // section
		);

		add_settings_field(
			'choose_post_type', // id
			'Choose Post type', // title
			array( $this, 'choose_post_type_callback' ), // callback
			'facebookpress-setting-admin', // page
			'facebookpress_setting_setting_section' // section
		);

		add_settings_field(
			'choose_category', // id
			'Choose Category', // title
			array( $this, 'choose_category_callback' ), // callback
			'facebookpress-setting-admin', // page
			'facebookpress_setting_setting_section' // section
		);
	}

	public function facebookpress_setting_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['app_id'] ) ) {
			$sanitary_values['app_id'] = sanitize_text_field( $input['app_id'] );
		}

		if ( isset( $input['app_secret'] ) ) {
			$sanitary_values['app_secret'] = sanitize_text_field( $input['app_secret'] );
		}

		if ( isset( $input['page_id'] ) ) {
			$sanitary_values['page_id'] = sanitize_text_field( $input['page_id'] );
		}

		if ( isset( $input['choose_post_type'] ) ) {
			$sanitary_values['choose_post_type'] = $input['choose_post_type'];
		}

		if ( isset( $input['choose_category'] ) ) {
			$sanitary_values['choose_category'] = $input['choose_category'];
		}

		return $sanitary_values;
	}

	public function facebookpress_setting_section_info() {
		
	}

	public function app_id_callback() {
		printf(
			'<input class="regular-text" type="text" name="facebookpress_setting_option[app_id]" id="app_id" value="%s">',
			isset( $this->facebookpress_setting_options['app_id'] ) ? esc_attr( $this->facebookpress_setting_options['app_id']) : ''
		);
	}

	public function app_secret_callback() {
		printf(
			'<input class="regular-text" type="text" name="facebookpress_setting_option[app_secret]" id="app_secret" value="%s">',
			isset( $this->facebookpress_setting_options['app_secret'] ) ? esc_attr( $this->facebookpress_setting_options['app_secret']) : ''
		);
	}

	public function page_id_callback() {
		printf(
			'<input class="regular-text" type="text" name="facebookpress_setting_option[page_id]" id="page_id" value="%s">',
			isset( $this->facebookpress_setting_options['page_id'] ) ? esc_attr( $this->facebookpress_setting_options['page_id']) : ''
		);
	}

	public function choose_post_type_callback() {
		?> <select name="facebookpress_setting_option[choose_post_type]" id="choose_post_type">
			<option value="0" disabled="" selected>Choose post type</option>
			<?php foreach ($this->admin->get_post_types() as $slug => $post_type) {?>

			<?php $selected = (isset( $this->facebookpress_setting_options['choose_post_type'] ) && $this->facebookpress_setting_options['choose_post_type'] === $slug ) ? 'selected' : '' ; ?>
			<option value="<?php echo $slug;?>" <?php echo $selected; ?>><?php echo $post_type;?> </option>
			<?php } ?>


		</select> <?php
	}

	public function choose_category_callback() {
		$term_slug = Facebookpress::get_option('choose_category');
		if ( isset( $term_slug ) && !empty( $term_slug ) ) {
			$post_type = Facebookpress::get_option('choose_post_type');
			$terms = $this->admin->get_post_cats( $post_type );?>
			<select name="facebookpress_setting_option[choose_category]" id="choose_category">
			<?php
			foreach ( $terms as $term ) {
			
				?>
				<?php $selected = (isset( $term_slug ) && $term_slug == $term['slug'] ) ? 'selected' : '' ; ?>
				<option value="<?php echo $term['slug'];?>" <?php echo $selected; ?>><?php echo $term['name'];?> </option>
			<?php } } ?>

			<option value="0">Select post type first</option>
			
		</select> 
		<?php $this->admin->sdk->login_button();

	}

}
