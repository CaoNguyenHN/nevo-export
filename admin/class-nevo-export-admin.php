<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://nevothemes.com/
 * @since      1.0.0
 *
 * @package    Nevo_Export
 * @subpackage Nevo_Export/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Nevo_Export
 * @subpackage Nevo_Export/admin
 * @author     NevoThemes <nevothemes.com>
 */
class Nevo_Export_Admin {

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
	 * The slug of this plugin menu.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $page_slug;

	/**
	 * Capability of user to export
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $export_capability;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name       = $plugin_name;
		$this->version           = $version;
		$this->page_slug         = apply_filters( 'nevo_export_page_slug', 'nevo-export' );
		$this->export_capability = apply_filters( 'nevo_export_capability', 'export' );
	}

	/**
	 * Add plugin menu items.
	 *
	 * @access public
	 *
	 * @since 1.0.0
	 * @param string[] $actions     An array of plugin action links. By default this can include
	 *                              'activate', 'deactivate', and 'delete'. With Multisite active
	 *                              this can also include 'network_active' and 'network_only' items.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data An array of plugin data. See get_plugin_data()
	 *                              and the {@see 'plugin_row_meta'} filter for the list
	 *                              of possible values.
	 * @param string   $context     The plugin context. By default this can include 'all',
	 *                              'active', 'inactive', 'recently_activated', 'upgrade',
	 *                              'mustuse', 'dropins', and 'search'.
	 * @return array settings schema for this plugin.
	 */
	public function add_plugin_links( $actions, $plugin_file, $plugin_data, $context ) {
		$actions[] = '<a href="' . esc_url( menu_page_url( $this->page_slug, false ) ) . '">' . esc_html__( 'Export', 'nevo-export' ) . '</a>';
		return $actions;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook_suffix ) {

		if ( 'tools_page_' . $this->page_slug == $hook_suffix ) {
			wp_enqueue_style( $this->plugin_name, NEVO_EXPORT_URL . 'assets/css/nevo-export-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook_suffix ) {

		if ( 'tools_page_' . $this->page_slug == $hook_suffix ) {
			wp_enqueue_script( $this->plugin_name, NEVO_EXPORT_URL . 'assets/js/nevo-export-admin.js', array( 'jquery' ), $this->version, false );
			wp_localize_script(
				$this->plugin_name,
				'nevo_export_js_object',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			);
		}
	}

	/**
	 * Create admin pages in menu
	 *
	 * @since    1.0.0
	 */
	public function export_menu() {
		add_management_page( esc_html__( 'Nevo Export', 'nevo-export' ), esc_html__( 'Nevo Export', 'nevo-export' ), $this->export_capability, $this->page_slug, array( $this, 'export_screen' ) );
	}

	/**
	 * The Admin Screen, placeholder div for ajax form
	 *
	 * @since    1.0.0
	 */
	public function export_screen() {
		?>
		<div id="nevo-export-ajax-form-data">
		</div>
		<?php
	}

	/**
	 * Form Loading Ajax Callback
	 *
	 * @since    1.0.0
	 */
	public function form_load() {
		nevo_export_form();
		exit;
	}

	/**
	 * Export Content
	 *
	 * @since    1.0.0
	 */
	public function export_content() {
		if ( empty( $_GET['page'] ) || $this->page_slug !== $_GET['page'] ) {
			return;
		}

		// If the 'download' URL parameter is set, a Theme Data ZIP export file returned.
		if ( isset( $_POST['nevo-export-download'] ) ) {
			if ( ! current_user_can( $this->export_capability ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to export the content of this site.', 'nevo-export' ) );
			}

			/*security check*/
			check_admin_referer( 'nevo-export' );

			$args = array();

			if ( ! isset( $_POST['content'] ) || 'all' == $_POST['content'] ) {
				$args['content'] = 'all';
			} elseif ( 'posts' == $_POST['content'] ) {
				$args['content'] = 'post';

				if ( $_POST['cat'] ) {
					$args['category'] = absint( $_POST['cat'] );
				}

				if ( $_POST['post_author'] ) {
					$args['author'] = absint( $_POST['post_author'] );
				}

				if ( $_POST['post_start_date'] || $_POST['post_end_date'] ) {
					$args['start_date'] = sanitize_text_field( $_POST['post_start_date'] );
					$args['end_date']   = sanitize_text_field( $_POST['post_end_date'] );
				}

				if ( $_POST['post_status'] ) {
					$args['status'] = sanitize_text_field( $_POST['post_status'] );
				}
			} elseif ( 'pages' == $_POST['content'] ) {
				$args['content'] = 'page';

				if ( $_POST['page_author'] ) {
					$args['author'] = absint( $_POST['page_author'] );
				}

				if ( $_POST['page_start_date'] || $_POST['page_end_date'] ) {
					$args['start_date'] = sanitize_text_field( $_POST['page_start_date'] );
					$args['end_date']   = sanitize_text_field( $_POST['page_end_date'] );
				}

				if ( $_POST['page_status'] ) {
					$args['status'] = sanitize_text_field( $_POST['page_status'] );
				}
			} elseif ( 'attachment' == $_POST['content'] ) {
				$args['content'] = 'attachment';

				if ( $_POST['attachment_start_date'] || $_POST['attachment_end_date'] ) {
					$args['start_date'] = sanitize_text_field( $_POST['attachment_start_date'] );
					$args['end_date']   = sanitize_text_field( $_POST['attachment_end_date'] );
				}
			} else {
				$args['content'] = sanitize_text_field( $_POST['content'] );
			}
			if ( isset( $_POST['include_media'] ) && $_POST['include_media'] == 1 ) {
				$args['include_media'] = 1;
			}
			if ( isset( $_POST['widgets_data'] ) && $_POST['widgets_data'] == 1 ) {
				$args['widgets_data'] = 1;
			}
			if ( isset( $_POST['options_data'] ) && $_POST['options_data'] == 1 ) {
				$args['options_data'] = 1;
			}

			/**
			 * Create zip
			 */
			require_once NEVO_EXPORT_PATH . 'admin/function-create-zip.php';
			nevo_export_ziparchive( $args );
			die();
		}
	}
}
