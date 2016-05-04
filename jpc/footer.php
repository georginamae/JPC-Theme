<?php
/*
  FOOTER
 */
?>

	
	</div><!-- #main .wrapper -->
	<footer id="colophon" role="contentinfo">
		<div class="site-info">
			<?=do_shortcode('[developer]');?>
			<?=do_shortcode('[copyright]');?>
			<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'twentytwelve' ) ); ?>" title="<?php esc_attr_e( 'Semantic Personal Publishing Platform', 'twentytwelve' ); ?>"><?php printf( __( 'Proudly powered by %s', 'twentytwelve' ), 'WordPress' ); ?></a>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>