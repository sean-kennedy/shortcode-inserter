<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://seankennedy.com.au/
 * @since      1.0.0
 *
 * @package    Shortcode_Inserter
 * @subpackage Shortcode_Inserter/admin
 */

/**
 * @package    Shortcode_Inserter
 * @subpackage Shortcode_Inserter/admin
 * @author     Sean Kennedy <sean@seankennedy.com.au>
 */
class Shortcode_Inserter_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * Prefix string for options associated with this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $option_name    		Prefix string for options associated with this plugin.
	 */
	private $option_name = 'shortcode_inserter';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       	The name of the plugin.
	 * @param    string    $version    			The version of this plugin.
	 * @param	 Shortcode_Inserter_Manager 	   $shortcode_manager 	Instance of the Shortcode_Manager class.
	 */
	public function __construct( $plugin_name, $version, $shortcode_manager ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->shortcode_manager = $shortcode_manager;

	}
	
	/**
	 * Load a global JS varibale to hold plugin values.
	 *
	 * @since    1.0.0
	 */
	public function load_global_js_object() { ?>
		
		<script type="text/javascript">
			var shortcodeInserter = <?php echo json_encode( array( 
				'pluginUrl' => SHORTCODE_INSERTER_PLUGIN_URI_ROOT,
				'tinyMceShortcodes' => $this->shortcode_inserter_get_shortcode_tinymce()
			)); ?>
		</script>
  
		<?php
	}
	
	/**
	 * Register TinyMCE button.
	 *
	 * @since    1.0.0
	 */
	public function register_tiny_mce_button() {
		
		if ( ! current_user_can( 'edit_pages' ) && ! current_user_can( 'edit_posts' ) ) {
			return;
		}
	
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', 'shortcode_inserter_add_mce_plugin' );
			add_filter( 'mce_buttons', 'shortcode_inserter_register_mce_button' );
		}
				
		function shortcode_inserter_add_mce_plugin( $plugins ) {
		
			$plugins['shortcode_inserter_button'] = plugin_dir_url( __FILE__ ) . 'js/shortcode-inserter-tiny-mce-button.js';
		
			return $plugins;
		}
		
		
		function shortcode_inserter_register_mce_button( $buttons ) {
		
			$buttons[] = 'shortcode_inserter_button';
		
			return $buttons;
		}
		
	}
	
	/**
	 * Register settings for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
	
		add_settings_section(
			$this->option_name . '_general',
			false,
			false,
			$this->plugin_name
		);
		
		add_settings_field(
			$this->option_name . '_disabled_shortcodes',
			__( 'Disable Shortcodes', 'shortcode-inserter' ),
			array( $this, $this->option_name . '_disabled_shortcodes_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_disabled_shortcodes' )
		);
		
		register_setting( $this->plugin_name, $this->option_name . '_disabled_shortcodes', array( $this, $this->option_name . '_sanitize_disabled_shortcodes' ) );
	
	}
	
	/**
	 * Add options page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function add_options_page() {
	
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Shortcode Inserter Settings', 'shortcode-inserter' ),
			__( 'Shortcode Inserter', 'shortcode-inserter' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_options_page' )
		);
	
	}
	
	/**
	 * Display options page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_options_page() {
		
		include_once 'partials/shortcode-inserter-admin-display.php';
		
	}
	
	/**
	 * Callback to display checkbox fields for disabled shortcodes.
	 *
	 * @since    1.0.0
	 */
	public function shortcode_inserter_disabled_shortcodes_cb() {
		
		$disabled_shortcodes = get_option( $this->option_name . '_disabled_shortcodes' );
		$all_shortcodes = $this->shortcode_manager->all_shortcodes['php'];
		
		?>
		
		<fieldset>
			
			<?php 
			
			if ($all_shortcodes) {
			
				foreach ($all_shortcodes as $key => $value) {
					
					$file_data = $this->get_shortcode_file_meta($value);
					
					?>
					
					<label>
						<input type="checkbox" name="<?php echo $this->option_name . '_disabled_shortcodes[' . $key . ']'; ?>" value="1" <?php checked( 1 == @$disabled_shortcodes[$key] ); ?> />
						<?php _e( $file_data['shortcode_name'], 'shortcode-inserter' ); ?>
					</label>
					<br>
					
				<?php } 
			
			} else { ?>
				
				<p>No shortcodes loaded.</p>
				
			<?php } ?>
			
		</fieldset>
			
		<?php
	}
	
	/**
	 * Sanitize disabled shortcodes checkbox input.
	 *
	 * @since    1.0.0
	 * @param    array     $disabled_shortcodes       	Disabled shortcodes list.
	 */
	public function shortcode_inserter_sanitize_disabled_shortcodes( $disabled_shortcodes ) {
		
	    if( !is_array( $disabled_shortcodes ) || empty( $disabled_shortcodes ) || ( false === $disabled_shortcodes ) )
	        return array();
	
	    $valid_names = array();
	    $valid_shortcodes = $this->shortcode_manager->all_shortcodes['php'];
	    
	    foreach ($valid_shortcodes as $key => $value) {
			$valid_names[] = $key;
		}
	    
	    $clean_disabled_shortcodes = array();

	    foreach( $valid_names as $shortcode_name ) {
	        if( isset( $disabled_shortcodes[$shortcode_name] ) && ( 1 == $disabled_shortcodes[$shortcode_name] ) )
	            $clean_disabled_shortcodes[$shortcode_name] = 1;
	        continue;
	    }
	    
	    unset( $disabled_shortcodes );
	    
	    return $clean_disabled_shortcodes;
	    
	}
	
	/**
	 * Get shortcode properties from enabled shortcodes to display in TinyMCE button.
	 *
	 * @since    1.0.0
	 */
	private function shortcode_inserter_get_shortcode_tinymce() {
		
		$tinymce_shortcodes = array();
		
		$shortcode_files = $this->shortcode_manager->enabled_shortcodes['php'];
		
		if (!empty($shortcode_files)) {
		
			foreach ($shortcode_files as $shortcode_file) {
				
				$file_data = $this->get_shortcode_file_meta($shortcode_file);
				
				$tinymce_shortcodes[] = array('text' => $file_data['shortcode_name'], 'content' => $file_data['tinymce_template']);
				
			}
		
		} else {
			
			$tinymce_shortcodes = false;
			
		}
			
		return $tinymce_shortcodes;
		
	}
	
	/**
	 * Get file meta from commented header
	 *
	 * @since    1.0.0
	 * @param    string    	$shortcode_file       	Shortcode file path.
	 */
	private function get_shortcode_file_meta($shortcode_file) {
		
		$headers = array('shortcode_name' => 'Shortcode Name', 'tinymce_template' => 'Shortcode Tinymce Template');
		
		$file_data = get_file_data($shortcode_file, $headers);
		
		return $file_data;
		
	}
	
	/**
	 * Settings link on Plugin page
	 *
	 * @since  1.0.0
	 */
	public function add_plugin_action_links($links) {
		
		$settings = array('settings' => '<a href="options-general.php?page=shortcode-inserter">' . __('Settings', 'General') . '</a>');
		
		$links = array_merge($settings, $links);
		
		return $links;
	   
	}

}
