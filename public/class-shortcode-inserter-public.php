<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://seankennedy.com.au/
 * @since      1.0.0
 *
 * @package    Shortcode_Inserter
 * @subpackage Shortcode_Inserter/public
 */

/**
 * @package    Shortcode_Inserter
 * @subpackage Shortcode_Inserter/public
 * @author     Sean Kennedy <sean@seankennedy.com.au>
 */
class Shortcode_Inserter_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    		The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    			The current version of this plugin.
	 */
	private $version;

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
	 * Init function to get and require enabled shortcodes from the Shortcode Manager.
	 *
	 * @since    1.0.0
	 */
	public function init() {
		
		$shortcode_functions = $this->shortcode_manager->enabled_shortcodes['php'];
		
		foreach ($shortcode_functions as $shortcode_function) {
			require $shortcode_function;
		}
		
	}

	/**
	 * Register (not enqueue) the stylesheets for enabled shortcodes.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		$shortcodes_css = $this->shortcode_manager->enabled_shortcodes['css'];
		
		foreach ($shortcodes_css as $shortcode_css) {
			
			$shortcode_object = $this->get_enqueue_object($shortcode_css, 'css');
			
			wp_register_style( $this->plugin_name . '_' . $shortcode_object['name'], $shortcode_object['url'], array(), '0.1.0', false );
			
		}

	}

	/**
	 * Register (not enqueue) the scripts for enabled shortcodes.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$shortcodes_js = $this->shortcode_manager->enabled_shortcodes['js'];
		
		foreach ($shortcodes_js as $shortcode_js) {
			
			$shortcode_object = $this->get_enqueue_object($shortcode_js, 'js');
			
			wp_register_script( $this->plugin_name . '_' . $shortcode_object['name'], $shortcode_object['url'], array( 'jquery' ), '0.1.0', true );
			
		}

	}
	
	/**
	 * Filter the content, register shortcodes, only enqueue styles/scripts for shortcodes being used. Clean up disabled shortcode tags.
	 *
	 * @since    1.0.0
	 * @param    string    $content				Content passed via filter.
	 */
	public function shortcode_inserter_process_shortcodes( $content ) {
		
		global $shortcode_tags;		
	
		$original_shortcode_tags = $shortcode_tags;
	
		remove_all_shortcodes();
	
		$this->register_shortcodes();
		
		$loaded_shortcodes = $this->extract_shortcodes($content, $shortcode_tags);
		
		$all_shortcode_tags = array_merge($original_shortcode_tags, $shortcode_tags);
		
		$content = $this->strip_disabled_shortcodes($content, $all_shortcode_tags);
		
		if ($loaded_shortcodes) {
			
			foreach ($loaded_shortcodes as $shortcode) {
				
				wp_enqueue_script($this->plugin_name . '_' . $shortcode);
				wp_enqueue_style($this->plugin_name . '_' . $shortcode);
				
			}
			
		}
	
		$content = do_shortcode($content);
	
		$shortcode_tags = $original_shortcode_tags;
	
		return $content;
		
	}
	
	/**
	 * Return the content stripped of disabled shortcode tags.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $content       		Content passed via filter.
	 * @param	 array	   $shortcode_tags		List of shortcode tags.
	 */
	private function strip_disabled_shortcodes($content, $shortcode_tags) {
		
		$active_shortcodes = ( is_array( $shortcode_tags ) && !empty( $shortcode_tags ) ) ? array_keys( $shortcode_tags ) : array();
		
		$hack1 = md5( microtime() );
		$content = str_replace( "[/", $hack1, $content );
		$hack2 = md5( microtime() + 1 );
		$content = str_replace( "/", $hack2, $content ); 
		$content = str_replace( $hack1, "[/", $content );
		
		if(!empty($active_shortcodes)){
			$keep_active = implode("|", $active_shortcodes);
			$content= preg_replace( "~(?:\[/?)(?!(?:$keep_active))[^/\]]+/?\]~s", '', $content );
		} else {
			$content = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $content);			
		}
		
		$content = str_replace($hack2,"/",$content);
		
		return $content;
		
	}
	
	/**
	 * Return a list of shortcodes that appear in both the content and tags list. Other shortcodes ignored.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $content       		Content passed via filter.
	 * @param	 array	   $shortcode_tags		List of shortcode tags.
	 */
	private function extract_shortcodes($content, $shortcode_tags) {
		
		if (strpos($content, '[') === false) {
			return false;
		}
	
		if (empty($shortcode_tags) || !is_array($shortcode_tags)) {
			return false;
		}
	
		preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
		
		$loaded_shortcodes = array_intersect(array_keys($shortcode_tags), $matches[1]);
		
		if (empty($loaded_shortcodes)) {
			return false;
		}
		
		return $loaded_shortcodes;
		
	}
	
	/**
	 * Register shortcodes hooked in via the filter.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_shortcodes() {
		
		add_filter('widget_text', 'do_shortcode');
		
		foreach($this->shortcode_inserter_register_shortcode() as $shortcode_name => $shortcode_function) {
			add_shortcode($shortcode_name, $shortcode_function);
		}
		
	}
	
	/**
	 * Shortcodes list with hookable filter.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function shortcode_inserter_register_shortcode() {
	
		$shortcodes = array();
			
		return apply_filters('shortcode_inserter_register_shortcode', $shortcodes);
		
	}
	
	/**
	 * Return an array with the name and relative url of the shortcode.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $shortcode_path      Absolute path to shortcode file.
	 * @param	 string	   $extension			File type extension.
	 */
	private function get_enqueue_object($shortcode_path, $extension) {
		
		$shortcode_url = str_replace(ABSPATH, '/', $shortcode_path);
		
		$shortcode_name = explode('/', $shortcode_path);
		$shortcode_name = end($shortcode_name);
		$shortcode_name = str_replace('.' . $extension, '', $shortcode_name);
		
		return array('name' => $shortcode_name, 'url' => $shortcode_url);
		
	}

}
