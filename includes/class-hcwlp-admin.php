<?php

// Exit if accessed directly
defined ('ABSPATH') or exit;

if( ! class_exists( 'HCWLP_Admin' ) )
{
    class HCWLP_Admin
	{		
		/**
		 * Init plugin
		 * @since	1.0.0
		 */
        public function init()
		{
			// Enqueue scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'hcwlp_enqueue_styles' ) );

			// Create woocommerce tab
            add_filter( 'woocommerce_product_data_tabs', array( $this, 'hcwlp_create_tab' ) );

            // Add fields to woocommerce tab
            add_action( 'woocommerce_product_data_panels', array( $this, 'hcwlp_display_fields' ) );

            // Save woocommerce tab fields
            add_action( 'woocommerce_process_product_meta', array( $this, 'hcwlp_save_fields' ) );
        }

		/**
		 * Register the stylesheets
		 * @since	1.0.0
		 */
		public function hcwlp_enqueue_styles()
		{
			wp_enqueue_style( HCWLP_PLUGIN_NAME. '-admin-css', plugin_dir_url( __FILE__ ) . '../assets/css/hcwlp-admin.css', array(), HCWLP_PLUGIN_VERSION, 'all' );
			wp_enqueue_script( HCWLP_PLUGIN_NAME. '-admin-script', plugin_dir_url( __FILE__ ) . '../assets/js/hcwlp-admin.js', array('jquery'), '', true);

			// Enqueue color picker
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}

        /**
         * Add tab to the woocommerce $tabs array
         * @param   $tabs
         * @since   1.0.0
         */
        public function hcwlp_create_tab( $tabs )
		{
            $tabs['hcwlp'] = array(
                'label'    => __( 'Linked products', 'hcwlp' ),
                'target'   => 'hcwlp',
                'class'    => array( 'hcwlp_tab', 'show_if_simple', 'show_if_variable', 'show_if_grouped', 'show_if_downloadable', 'show_if_external' ),
                'priority' => 40, // Where your panel will appear. By default, 70 is last item
            );

            return $tabs;
        }

        /**
         * Display fields
         * @since   1.0.0
         */
        public function hcwlp_display_fields()
		{
			global $product_object;

			?>
			<div id='hcwlp' class='panel woocommerce_options_panel'>
				
				<div class="options_group">
					<?php
					woocommerce_wp_text_input(
						array(
							'id'          => 'hcwlp_label',
							'label'       => __( 'Label', 'hcwlp' ),
							'type'        => 'text',
							'desc_tip'    => true,
							'description' => __( 'Enter the label name (Ex. Color)', 'hcwlp' )
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'          => 'hcwlp_name',
							'label'       => __( 'Name', 'hcwlp' ),
							'type'        => 'text',
							'desc_tip'    => true,
							'description' => __( 'Enter the variation name (Ex. Red)', 'hcwlp' )
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'          => 'hcwlp_hex',
							'label'       => __( 'Color 1', 'hcwlp' ),
							'type'        => 'text',
							'class'		  => 'hcwlp-color-picker'
						)
					);

					woocommerce_wp_text_input(
						array(
							'id'          => 'hcwlp_hex2',
							'label'       => __( 'Color 2', 'hcwlp' ),
							'type'        => 'text',
							'class'		  => 'hcwlp-color-picker'
						)
					);
					?>
				</div>

				<div class="options_group">
					<p class="form-field">
						<label for="hcwlp_ids"><?php _e( 'Linked products', 'hcwlp' ); ?></label>
						<select class="wc-product-search" multiple="multiple" style="width: 50%;" id="hcwlp_ids" name="hcwlp_ids[]" data-placeholder="<?php _e( 'Search for a product&hellip;', 'hcwlp' ); ?>" data-action="woocommerce_json_search_products_and_variations">
							<?php
							$product_ids = $product_object->get_meta( 'hcwlp_ids' );
							foreach ( $product_ids as $product_id ) {
								$product = wc_get_product( $product_id );
								if ( is_object( $product ) ) {
									echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
								}
							}
							?>
						</select>
					</p>
				</div>

			</div>
			<?php
		}

        /**
         * Save fields
         * @param	$post_id
         * @since	1.0.0
         */
        public function hcwlp_save_fields( $post_id )
		{
            $product = wc_get_product( $post_id );

			// Save hcwlp_label setting
            $hcwlp_label = isset( $_POST['hcwlp_label'] ) ? $_POST['hcwlp_label'] : false;
            $product->update_meta_data( 'hcwlp_label', sanitize_text_field( $hcwlp_label ) );

			// Save hcwlp_name setting
            $hcwlp_name = isset( $_POST['hcwlp_name'] ) ? $_POST['hcwlp_name'] : false;
            $product->update_meta_data( 'hcwlp_name', sanitize_text_field( $hcwlp_name ) );

			// Save hcwlp_hex setting
            $hcwlp_hex = isset( $_POST['hcwlp_hex'] ) ? $_POST['hcwlp_hex'] : false;
            $product->update_meta_data( 'hcwlp_hex', sanitize_text_field( $hcwlp_hex ) );

			// Save hcwlp_hex setting
            $hcwlp_hex2 = isset( $_POST['hcwlp_hex2'] ) ? $_POST['hcwlp_hex2'] : false;
            $product->update_meta_data( 'hcwlp_hex2', sanitize_text_field( $hcwlp_hex2 ) );

			// Save hcwlp_ids setting
			$hcwlp_ids = isset( $_POST['hcwlp_ids'] ) ? array_map( 'intval', (array) $_POST['hcwlp_ids'] ) : array();
    		$product->update_meta_data( 'hcwlp_ids', $hcwlp_ids );

            $product->save();
        }
    }
}