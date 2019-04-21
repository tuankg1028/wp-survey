<div id="form_settings_page" class="wrap">
	<?php
	if ( is_callable('FrmAppHelper::get_admin_header' ) ) {
		FrmAppHelper::get_admin_header( array(
			'form'   => $form,
		) );
	} else {
		echo '<h1 id="frm_form_heading">' . esc_html__( 'Translate Form', 'formidable-polylang' ) . '</h1>';
		FrmAppController::get_form_nav( $id, true );
	}
	?>

	<?php include( FrmAppHelper::plugin_path() .'/classes/views/shared/errors.php' ); ?>

	<div id="poststuff" class="metabox-holder">
		<div id="post-body">
			<?php
			if ( empty( $listlanguages ) ) {
				esc_html_e( 'Please add a language in Polylang for translations to appear.', 'formidable-polylang' );
			} else {
				if ( file_exists( PLL_ADMIN_INC . '/view-tab-strings.php' ) ) {
					$file_name = PLL_ADMIN_INC . '/view-tab-strings.php';
				} else {
					$file_name = PLL_SETTINGS_INC . '/view-tab-strings.php';
				}
				include( $file_name );
			}
			?>
		</div>

	</div>
</div>

<script type="text/javascript">
var pllFrmForm = document.getElementById('string-translation');
pllFrmForm.action = '<?php echo esc_url_raw( admin_url( 'admin.php?page=formidable&frm_action=update_translations&id='. $id ) ); ?>';
</script>

<style>
.tablenav .bulkactions, .tablenav .actions, .search-box, .check-column,
#string-translation > label, #string-translation > p{display:none;}
#string-translation > p.submit{display:block;}
textarea{min-height:60px;}
</style>