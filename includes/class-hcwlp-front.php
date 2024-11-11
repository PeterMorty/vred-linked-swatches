<?php

// Exit if accessed directly
defined ('ABSPATH') or exit;

if( ! class_exists( 'HCWLP_Front' ) )
{
    class HCWLP_Front
	{
		/**
		 * Init plugin
		 * @since	1.0.0
		 */
        public function init()
		{
			// Enqueue scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'hcwlp_enqueue_styles' ) );

			// Display product variation
			//add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'hc_display_product_variation' ) , 10 );

			// Set shortcodes
			add_shortcode( 'hcwlp_variation', [HCWLP_Front::class, 'hc_product_variation'] );
			add_shortcode( 'hcwlp_products', [HCWLP_Front::class, 'hc_linked_products'] );
        }

		/**
		 * Register the stylesheets
		 * @since	1.0.0
		 */
		public function hcwlp_enqueue_styles()
		{
			wp_enqueue_style( HCWLP_PLUGIN_NAME . '-css', plugin_dir_url( __FILE__ ) . '../assets/css/hcwlp-front.css', array(), HCWLP_PLUGIN_VERSION, 'all' );
		}

		/**
		 * Product variation
		 * @since	1.0.0
		 */
		public static function hc_product_variation()
		{
			global $post;

			$product_id = $post->ID;

			$data = array(
				'label'		  => get_post_meta( $product_id, 'hcwlp_label', true ),
				'name' 		  => get_post_meta( $product_id, 'hcwlp_name', true ),
				'hex'   	  => get_post_meta( $product_id, 'hcwlp_hex', true ),
				'hex2'   	  => get_post_meta( $product_id, 'hcwlp_hex2', true ),
				'product_ids' => get_post_meta( $product_id, 'hcwlp_ids', true )
			);

			if( !$data['label'] || !$data['hex'] || !$data['product_ids'] )
				return;
			
			return '
			<ul id="hcwlp-variation">
				<li><b>' . $data['label'] . '</b></li>
				<li>' . $data['name'] . '</li>' .
				HCWLP_Front::hc_pattern('li', $data) . '
			</ul>';
		}

		/**
		 * Linked products
		 * @since	1.0.0
		 */
		public static function hc_linked_products()
		{
			global $post;

			$product_id = $post->ID;

			$data = array(
				'name' 		  => get_post_meta( $product_id, 'hcwlp_name', true ),
				'product_ids' => get_post_meta( $product_id, 'hcwlp_ids', true )
			);

			if( !$data['product_ids'] )
				return;
			
			$str = '
			<ul id="hcwlp-products">';
			
			foreach($data['product_ids'] as $lp_id)
			{
				$lp = wc_get_product( $lp_id );	

				$data_lp = array(
					'label' => get_post_meta( $lp_id, 'hcwlp_label', true ),
					'name'  => get_post_meta( $lp_id, 'hcwlp_name', true ),
					'hex'   => get_post_meta( $lp_id, 'hcwlp_hex', true ),
					'hex2'  => get_post_meta( $lp_id, 'hcwlp_hex2', true ),
				);

				$str.= '
				<li' . ( $data['name'] == $data_lp['name'] ? ' class="active"' : NULL ) . '>
					<a href="' .get_permalink( $lp->get_id() ) . '">' .
						$lp->get_image() . '
						<h3>' . HCWLP_Front::hc_pattern('div', $data_lp) . $data_lp['name']/*$lp->get_name()*/ . '</h3>
					</a>
				</li>';
			}

			$str.= '
			</ul>';

			return $str;
		}

		/**
		 * Set pattern
		 * @since	1.0.0
		 */
		public static function hc_pattern($tag, $data)
		{
			if( !$data['hex'] )
				return;
				
			return '
			<' . $tag . ' class="hcwlp-pattern"' . ($data['hex'] ? ' style="background-color:' . $data['hex'] . '"' : NULL) . '> ' .
				($data['hex2'] ? '<div style="border-bottom-color:' . $data['hex2'] . '"></div>' : NULL) . '
			</' . $tag . '>';
		}

		/**
		 * Display product variation
		 * @since	1.0.0
		 */
		public function hc_display_product_variation() {
			echo HCWLP_Front::hc_product_variation();
		}

		/**
		 * Display linked products
		 * @since	1.0.0
		 */
		public function hc_display_linked_products() {
			echo HCWLP_Front::hc_linked_products();
		}
    }
}