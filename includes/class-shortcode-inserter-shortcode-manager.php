<?php

/**
 * Load all shortcodes for the plugin.
 *
 * @link       https://seankennedy.com.au/
 * @since      1.0.0
 *
 * @package    Shortcode_Inserter
 * @subpackage Shortcode_Inserter/includes
 */

/**
 * @package    Shortcode_Inserter
 * @subpackage Shortcode_Inserter/includes
 * @author     Sean Kennedy <sean@seankennedy.com.au>
 */
class Shortcode_Inserter_Shortcode_Manager {

	/**
	 * Reference to the list of enabled shortcodes.
	 *
	 * @since    1.0.0
	 * @var      array    $enabled_shortcodes   Reference to the list of enabled shortcodes.
	 */
	public $enabled_shortcodes;
	
	/**
	 * Reference to the list of all shortcodes.
	 *
	 * @since    1.0.0
	 * @var      array     $all_shortcodes    	Reference to the list of all shortcodes.
	 */
	public $all_shortcodes;

	/**
	 * Prefix string for options associated with this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $option_name    		Prefix string for options associated with this plugin.
	 */
	private $option_name = 'shortcode_inserter';

	/**
	 * Initialize the lists of shortcodes.
	 *
	 * @since    1.0.0
	 */
	public function init() {
		
		$this->all_shortcodes = $this->get_all_shortcodes();
		$this->enabled_shortcodes = $this->get_enabled_shortcodes($this->all_shortcodes);
		
	}

	/**
	 * Return an array of all shortcodes.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function get_all_shortcodes() {
		
		$all_shortcodes = array();
		
		$all_shortcodes['php'] = $this->get_paths('.php');
		$all_shortcodes['js'] = $this->get_paths('.js');
		$all_shortcodes['css'] = $this->get_paths('.css');
		
		return $this->all_shortcodes = $all_shortcodes;
		
	}
	
	/**
	 * Return an array of enabled shortcodes.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array     $all_shortcodes    	Reference to the list of all shortcodes.	
	 */
	private function get_enabled_shortcodes($all_shortcodes) {
		
		$options_disabled_shortcodes = get_option( $this->option_name . '_disabled_shortcodes' );
		$disabled_shortcodes = array();
		
		if ($options_disabled_shortcodes) {
			
			foreach ($options_disabled_shortcodes as $key => $value) {
				$disabled_shortcodes[] = $key;
			}
		
		}
		
		function remove_disabled_shortcodes(&$array, $keys) {
			
			foreach ($array as $key => &$value) {
				
				if (is_array($value)) { 
					remove_disabled_shortcodes($value, $keys); 
				} else {
					if (in_array($key, $keys)){
				    	unset($array[$key]);
					}
				}
				
			}
			
		}
		
		remove_disabled_shortcodes($all_shortcodes, $disabled_shortcodes);
		
		return $all_shortcodes;
		
	}
	
	/**
	 * Return an array of paths to the shortcode files and their associated CSS/JS.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $extension    		File extension.
	 */
	private function get_paths($extension) {
		
		$glob_regex_list = $this->get_glob_regex_list($extension);
		
		$path_list = array();
		$filtered_paths = array();
		
		foreach ($glob_regex_list as $glob_regex) {
			$path_list = array_merge($path_list, glob($glob_regex));
		}
		
		foreach ($path_list as $path) {
			
			$name = $this->get_shortcode_name($path);
			
			$filtered_paths[$name] = $path;
			
		}
		
		return $filtered_paths;
		
	}
	
	/**
	 * Return an array of glob regexes.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $extension    		File extension.	
	 */
	private function get_glob_regex_list($extension) {
		
		$glob_regex_list = array(
			get_template_directory() . '/shortcodes/*/*'
		);
		
		$glob_regex_list = apply_filters( 'shortcode_inserter_glob_paths', $glob_regex_list );
		
		foreach ($glob_regex_list as $key => $value) {
			$glob_regex_list[$key] = $value . $extension;
		}
		
		return $glob_regex_list;
		
	}
	
	/**
	 * Return the shortcode name from a path string.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $path    			Path to shortcode.	
	 */
	private function get_shortcode_name($path) {
		
		$shortcode_name = explode('/', $path);
		$shortcode_name = end($shortcode_name);
		$shortcode_name = explode('.', $shortcode_name);
		
		return $shortcode_name[0];
		
	}

}
