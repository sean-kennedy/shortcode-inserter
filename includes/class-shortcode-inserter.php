<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://seankennedy.com.au/
 * @since      1.0.0
 *
 * @package    Shortcode_Inserter
 * @subpackage Shortcode_Inserter/includes
 */

/**
 * @since      1.0.0
 * @package    Shortcode_Inserter
 * @subpackage Shortcode_Inserter/includes
 * @author     Sean Kennedy <sean@seankennedy.com.au>
 */
class Shortcode_Inserter {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Shortcode_Inserter_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	
	/**
	 * Instance of the Shortcode_Manager class.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Shortcode_Inserter_Manager    $shortcode_manager    Instance of the Shortcode_Manager class.
	 */
	protected $shortcode_manager;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'shortcode-inserter';
		$this->version = '1.0.1';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shortcode-inserter-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shortcode-inserter-shortcode-manager.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-shortcode-inserter-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-shortcode-inserter-public.php';

		$this->loader = new Shortcode_Inserter_Loader();
		$this->shortcode_manager = new Shortcode_Inserter_Shortcode_Manager();
		
		$this->loader->add_action( 'init', $this->shortcode_manager, 'init' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Shortcode_Inserter_Admin( $this->get_plugin_name(), $this->get_version(), $this->get_shortcode_manager() );

		$this->loader->add_action( 'admin_head', $plugin_admin, 'load_global_js_object' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_options_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_tiny_mce_button' );
		$this->loader->add_action( 'plugin_action_links_shortcode-inserter/shortcode-inserter.php', $plugin_admin, 'add_plugin_action_links' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Shortcode_Inserter_Public( $this->get_plugin_name(), $this->get_version(), $this->get_shortcode_manager() );
		
		$this->loader->add_action( 'init', $plugin_public, 'init' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'the_content', $plugin_public, 'shortcode_inserter_process_shortcodes', 7 );
		$this->loader->add_filter( 'acf_the_content', $plugin_public, 'shortcode_inserter_process_shortcodes', 7 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Shortcode_Inserter_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	
	/**
	 * The reference to the class that manages the shortcodes.
	 *
	 * @since     1.0.0
	 * @return    Shortcode_Inserter_Manager    Manages the shortcodes.
	 */
	public function get_shortcode_manager() {
		return $this->shortcode_manager;
	}

}
