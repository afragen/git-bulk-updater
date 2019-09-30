<?php
/**
 * Git Bulk Updater
 *
 * @author  Andy Fragen
 * @license MIT
 * @link    https://github.com/afragen/git-bulk-updater
 * @package git-bulk-updater
 */

namespace Fragen\Git_Bulk_Updater;

/*
 * Exit if called directly.
 * PHP version check and exit.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Class Actions
 */
class Actions {
	/**
	 * Load hooks.
	 *
	 * @return void
	 */
	public function load_hooks() {
		$this->load_js();
		add_action( 'admin_menu', [ $this, 'add_plugin_menu' ] );
		add_action( 'admin_init', [ new Updater(), 'update' ] );
	}

	/**
	 * Add options page.
	 */
	public function add_plugin_menu() {
		global $_registered_pages;
		if ( isset( $_registered_pages['settings_page_git-bulk-updater'] ) ) {
			return;
		}

		$parent     = is_multisite() ? 'settings.php' : 'tools.php';
		$capability = is_multisite() ? 'manage_network' : 'manage_options';

		add_submenu_page(
			$parent,
			esc_html__( 'Git Bulk Updater', 'git-bulk-updater' ),
			esc_html__( 'Git Bulk Updater', 'git-bulk-updater' ),
			$capability,
			'git-bulk-updater',
			[ $this, 'create_admin_page' ]
		);
	}

	/**
	 * Options page callback.
	 */
	public function create_admin_page() {
		$action = is_multisite() ? 'edit.php?action=git-bulk-updater' : 'options.php';

		echo '<div class="wrap"><h2>';
		esc_html_e( 'Git Bulk Updater', 'git-bulk-updater' );
		echo '</h2>';

		$this->show_feedback();
		$this->repo_or_site_selector();

		echo '<form method="post" action="' . esc_attr( $action ) . '">';
		echo '<table class="form-table">';

		echo '<tbody class="git-bulk-updater-repo">';
		( new Actions_Row() )->add_repo_rows();
		echo '</tbody>';

		echo '<tbody class="git-bulk-updater-site">';
		( new Actions_Row() )->add_site_rows();
		echo '</tbody>';

		echo '</table></div>';
		echo '</form>';
	}

	/**
	 * Display update feedback.
	 *
	 * @return void
	 */
	private function show_feedback() {
		$feedback = get_site_transient( 'git_bulk_updater_feedback' );
		if ( $feedback ) {
			echo '<div>';
			echo '<h3>' . esc_html__( 'Update Feedback', 'git-bulk-updater' ) . '</h3>';
			foreach ( $feedback as $repo_feedback ) {
					echo '<div><p>';
				foreach ( $repo_feedback as $message ) {
					echo wp_kses_post( $message ) . '<br>';
				}
					echo '</p></div>';
			}
			echo '</div>';
		}
	}

	/**
	 * Repo or Site option.
	 */
	private function repo_or_site_selector() {
		$options = [
			'git-bulk-updater-repo' => esc_html__( 'Show Repositories', 'git-bulk-updater' ),
			'git-bulk-updater-site' => esc_html__( 'Show Sites', 'git-bulk-updater' ),
		];
		?>
		<label for="git-bulk-updater">
			<select id="git-bulk-updater" name="git-bulk-updater">
				<?php foreach ( $options as $key => $value ) : ?>
						<option value="<?php esc_attr_e( $key ); ?>" <?php selected( $key ); ?> >
							<?php esc_html_e( $value ); ?>
						</option>
				<?php endforeach ?>
			</select>
		</label>
		<?php
	}

	/**
	 * Redirect to where we came from.
	 *
	 * @return void
	 */
	public function redirect() {
		$redirect_url = is_multisite() ? network_admin_url( 'settings.php' ) : admin_url( 'tools.php' );
		$location     = add_query_arg(
			[ 'page' => 'git-bulk-updater' ],
			$redirect_url
		);
		wp_safe_redirect( $location );
		exit();
	}

	/**
	 * Load javascript for Install.
	 *
	 * @return void
	 */
	public function load_js() {
		if ( isset( $_GET['page'] ) && 'git-bulk-updater' === $_GET['page'] ) {
			add_action(
				'admin_enqueue_scripts',
				function () {
					wp_register_script( 'git-bulk-updater-actions', plugins_url( basename( GIT_BULK_UPDATER_DIR ) . '/js/git-bulk-updater-switcher.js' ), [], false, true );
					wp_enqueue_script( 'git-bulk-updater-actions' );
				}
			);
		}
	}

}
