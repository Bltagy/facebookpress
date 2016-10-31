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
			'dashicons-facebook'
		);
	}

	public function facebookpress_setting_add_plugin_page_2() {
		add_submenu_page(
			'facebookpress-setting', // parent_slug
			'Run importer', // page_title
			'Run importer', // menu_title
			'manage_options', // capability
			'facebookpress-setting-run', // menu_slug
			array( $this, 'facebookpress_setting_create_admin_page_2' )
		);
	}


	public function facebookpress_setting_create_admin_page_2() {
		$this->facebookpress_setting_options = get_option( 'facebookpress_setting_option' );
		$nonce = wp_create_nonce( 'fp-nonce' );
		$callback_url =  admin_url( 'admin.php?page=facebookpress-setting-run&fp_action=run_impoter&_wpnonce='.$nonce);
		?>
		<style type="text/css" media="screen">
			.ui-progressbar {
    position: relative;
  }
  .progress-label {
    position: absolute;
    left: 50%;
    top: 4px;
    font-weight: bold;
    text-shadow: 1px 1px 0 #fff;
  }
		</style>
		<div class="wrap">
			<h2>Facebookpress Importer</h2>
			<div id="progressbar"><div class="progress-label">Loading...</div></div>

			<p><a href="<?php echo $callback_url;?>" class="button button-primary">Start Importer</a></p>
		</div>
	<?php }

	public function facebookpress_setting_create_admin_page() {
		$this->facebookpress_setting_options = get_option( 'facebookpress_setting_option' ); ?>

		<div class="wrap">
			<h2>Facebookpress Setting</h2>
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
			'Post Type Map', // title
			array( $this, 'choose_post_type_callback' ), // callback
			'facebookpress-setting-admin', // page
			'facebookpress_setting_setting_section' // section
		);

		add_settings_field(
			'import_images', // id
			'Import images?', // title
			array( $this, 'import_images_callback' ), // callback
			'facebookpress-setting-admin', // page
			'facebookpress_setting_setting_section' // section
		);

		add_settings_field(
			'choose_category', // id
			'', // title
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
		if ( isset( $input['import_images'] ) ) {
			$sanitary_values['import_images'] = $input['import_images'];
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
		$selected_post_type = Facebookpress::get_option('choose_post_type');
		$term_slug = Facebookpress::get_option('choose_category');
		?> 
		<table>
			<caption>Choose category for every facebook feed type</caption>
			<thead>
				<tr>
					<th>Feed Type</th>
					<th>Post type</th>
					<th>Category</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>All/The Rest</td>
					<td>
						<select name="facebookpress_setting_option[choose_post_type][all]" id="choose_post_type">
							<option value="0" disabled="" selected>Choose post type</option>
							<?php foreach ($this->admin->get_post_types() as $slug => $post_type) {?>

							<?php $selected = (isset( $this->facebookpress_setting_options['choose_post_type']['all'] ) && $this->facebookpress_setting_options['choose_post_type']['all'] === $slug ) ? 'selected' : '' ; ?>
							<option value="<?php echo $slug;?>" <?php echo $selected; ?>><?php echo $post_type;?> </option>
							<?php } ?>
						</select>
					</td>
					<td>
						<select name="facebookpress_setting_option[choose_category][all]" id="choose_category">
							<?php
							if ( isset( $term_slug['all'] ) && !empty( $term_slug['all'] ) ) {
							$terms = $this->admin->get_post_cats( $selected_post_type['all'] );
								foreach ( $terms as $term ) {
								
									?>
									<?php $selected = (isset( $term_slug['all'] ) && $term_slug['all'] == $term['slug'] ) ? 'selected' : '' ; ?>
									<option value="<?php echo $term['slug'];?>" <?php echo $selected; ?>><?php echo $term['name'];?> </option>
								<?php } 
							} ?>

							<option value="0">Select post type first</option>	
						</select>
					</td>
				</tr>
				<tr>
					<td>FB Post</td>
					<td>
						<select name="facebookpress_setting_option[choose_post_type][post]" id="choose_post_type">
							<option value="0" disabled="" selected>Choose post type</option>
							<?php $selected = (isset( $this->facebookpress_setting_options['choose_post_type']['post'] ) && $this->facebookpress_setting_options['choose_post_type']['post'] === 'no-import' ) ? 'selected' : '' ; ?>
							<option value="no-import" <?php echo $selected;?>>Don't import</option>
							<?php foreach ($this->admin->get_post_types() as $slug => $post_type) {?>

							<?php $selected = (isset( $this->facebookpress_setting_options['choose_post_type']['post'] ) && $this->facebookpress_setting_options['choose_post_type']['post'] === $slug ) ? 'selected' : '' ; ?>
							<option value="<?php echo $slug;?>" <?php echo $selected; ?>><?php echo $post_type;?> </option>
							<?php } ?>
						</select>
					</td>
					<td>
						<select name="facebookpress_setting_option[choose_category][post]" id="choose_category">
							<?php
							if ( isset( $term_slug['post'] ) && !empty( $term_slug['post'] ) ) {
							$terms = $this->admin->get_post_cats( $selected_post_type['post'] );
								foreach ( $terms as $term ) {
								
									?>
									<?php $selected = (isset( $term_slug['post'] ) && $term_slug['post'] == $term['slug'] ) ? 'selected' : '' ; ?>
									<option value="<?php echo $term['slug'];?>" <?php echo $selected; ?>><?php echo $term['name'];?> </option>
								<?php } 
							} ?>

							<option value="0">Select post type first</option>	
						</select>
					</td>
				</tr>
				<tr>
					<td>Album</td>
					<td>
						<select name="facebookpress_setting_option[choose_post_type][album]" id="choose_post_type">
							<option value="0" disabled="" selected>Choose post type</option>
							<option value="0" disabled="" selected>Choose post type</option>
							<?php $selected = (isset( $this->facebookpress_setting_options['choose_post_type']['album'] ) && $this->facebookpress_setting_options['choose_post_type']['post'] === 'no-import' ) ? 'selected' : '' ; ?>
							<?php foreach ($this->admin->get_post_types() as $slug => $post_type) {?>

							<?php $selected = (isset( $this->facebookpress_setting_options['choose_post_type']['album'] ) && $this->facebookpress_setting_options['choose_post_type']['album'] === $slug ) ? 'selected' : '' ; ?>
							<option value="<?php echo $slug;?>" <?php echo $selected; ?>><?php echo $post_type;?> </option>
							<?php } ?>
						</select>
					</td>
					<td>
						<select name="facebookpress_setting_option[choose_category][album]" id="choose_category">
							<?php
							if ( isset( $term_slug['album'] ) && !empty( $term_slug['album'] ) ) {
							$terms = $this->admin->get_post_cats( $selected_post_type['album'] );
								foreach ( $terms as $term ) {
								
									?>
									<?php $selected = (isset( $term_slug['album'] ) && $term_slug['album'] == $term['slug'] ) ? 'selected' : '' ; ?>
									<option value="<?php echo $term['slug'];?>" <?php echo $selected; ?>><?php echo $term['name'];?> </option>
								<?php } 
							} ?>

							<option value="0">Select post type first</option>	
						</select>
					</td>
				</tr>
				<tr>
					<td>Event</td>
					<td>
						<select name="facebookpress_setting_option[choose_post_type][event]" id="choose_post_type">
							<option value="0" disabled="" selected>Choose post type</option>
							<option value="0" disabled="" selected>Choose post type</option>
							<?php $selected = (isset( $this->facebookpress_setting_options['choose_post_type']['album'] ) && $this->facebookpress_setting_options['choose_post_type']['event'] === 'no-import' ) ? 'selected' : '' ; ?>
							<?php foreach ($this->admin->get_post_types() as $slug => $post_type) {?>

							<?php $selected = (isset( $this->facebookpress_setting_options['choose_post_type']['event'] ) && $this->facebookpress_setting_options['choose_post_type']['event'] === $slug ) ? 'selected' : '' ; ?>
							<option value="<?php echo $slug;?>" <?php echo $selected; ?>><?php echo $post_type;?> </option>
							<?php } ?>
						</select>
					</td>
					<td>
						<select name="facebookpress_setting_option[choose_category][event]" id="choose_category">
							<?php
							if ( isset( $term_slug['event'] ) && !empty( $term_slug['event'] ) ) {
							$terms = $this->admin->get_post_cats( $selected_post_type['event'] );
								foreach ( $terms as $term ) {
								
									?>
									<?php $selected = (isset( $term_slug['event'] ) && $term_slug['event'] == $term['slug'] ) ? 'selected' : '' ; ?>
									<option value="<?php echo $term['slug'];?>" <?php echo $selected; ?>><?php echo $term['name'];?> </option>
								<?php } 
							} ?>

							<option value="0">Select post type first</option>	
						</select>
					</td>
				</tr>
				<tr>
					<td>Video</td>
					<td>
						<select name="facebookpress_setting_option[choose_post_type][video]" id="choose_post_type">
							<option value="0" disabled="" selected>Choose post type</option>
							<option value="0" disabled="" selected>Choose post type</option>
							<?php $selected = (isset( $this->facebookpress_setting_options['choose_post_type']['album'] ) && $this->facebookpress_setting_options['choose_post_type']['video'] === 'no-import' ) ? 'selected' : '' ; ?>
							<?php foreach ($this->admin->get_post_types() as $slug => $post_type) {?>

							<?php $selected = (isset( $this->facebookpress_setting_options['choose_post_type']['video'] ) && $this->facebookpress_setting_options['choose_post_type']['video'] === $slug ) ? 'selected' : '' ; ?>
							<option value="<?php echo $slug;?>" <?php echo $selected; ?>><?php echo $post_type;?> </option>
							<?php } ?>
						</select>
					</td>
					<td>
						<select name="facebookpress_setting_option[choose_category][video]" id="choose_category">
							<?php
							if ( isset( $term_slug['video'] ) && !empty( $term_slug['video'] ) ) {
							$terms = $this->admin->get_post_cats( $selected_post_type['video'] );
								foreach ( $terms as $term ) {
								
									?>
									<?php $selected = (isset( $term_slug['video'] ) && $term_slug['video'] == $term['slug'] ) ? 'selected' : '' ; ?>
									<option value="<?php echo $term['slug'];?>" <?php echo $selected; ?>><?php echo $term['name'];?> </option>
								<?php } 
							} ?>

							<option value="0">Select post type first</option>	
						</select>
					</td>
				</tr>
				
			</tbody>
		</table>
		 <?php
	}

	public function import_images_callback() {
		printf(
			'<input class="checkbox" type="checkbox" name="facebookpress_setting_option[import_images]" id="import_images" value="on" %s>',
			isset( $this->facebookpress_setting_options['import_images'] ) ? 'checked' : ''
		);
	}

	public function choose_category_callback() {
		$this->admin->sdk->login_button();

	}

}
