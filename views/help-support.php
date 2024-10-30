<?php 
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* This shows the Help & Support page
**/

if ( defined( 'IT_RST_PLUGIN_PREMIUM_ACTIVE' ) ) {
	$pro_string = '';
	$pro_btn    =  __( 'Open Ticket', 'ithemes-sales-accelerator' );
}
else {
	$pro_string = '<span class="soon">' . __( 'PRO', 'ithemes-sales-accelerator' ) . '</span>';
	$pro_btn    =  __( 'Learn more', 'ithemes-sales-accelerator' );
}

?>
	
<div id="rst_reporting_wrapper">
	<div class="inline" style="width:100%;">
		<div style="display: block;"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/sales-acc_white.svg" style="height:50px;"></div>
		<div class="btt_dashboard"><a href="<?php echo get_dashboard_url(); ?>"><span><?php _e( 'Dashboard', 'ithemes-sales-accelerator' ); ?></span></a></div>
	</div>
</div>
<form method="post" action="">
	<div class="inside modules">
		<div class="row">
			<div class="col hide-on-med-and-down l2 xl2">&nbsp;</div>
			<div class="col s12 m12 l8 xl8">
				<div class="box_content first_box">
					<h4><?php _e( 'Help & Support', 'ithemes-sales-accelerator' ); ?></h4><br>
					<p><?php _e( 'Sales Accelerator comes with basic support for free users. Pro users get private, ticketed support from the iThemes Help Desk.', 'ithemes-sales-accelerator' ); ?></p>
					<div style="height: 1x; width: 100%; border-bottom: 1px solid #EAEAEA; margin: 50px 0 30px 0;"></div>
					<div class="row moduleItem">
						<! --- Tutorials --- !>
						<div class="col s8 principal">
							<div class="inline" style="width:100%;">
								<div style="min-width: 90px;"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/isa_tutorials.svg"></div>
								<div class="name_desc">
									<h5><?php _e( 'Tutorials', 'ithemes-sales-accelerator' ); ?></h5>
									<p><?php _e( 'Watch this series of Sales Accelerator video tutorials.', 'ithemes-sales-accelerator' ); ?></p>
								</div>
							</div>
						</div>
						<div class="col s4 laterals" style="text-align: right; display: flex; align-items: center; justify-content: flex-end;">
							<div class="click_modal">
								<span class="last_btts it_link_external"><a href="https://ithemes.com/tutorial/category/ithemes-sales-accelerator" target="_blank"><?php _e( 'Learn more', 'ithemes-sales-accelerator' ); ?></a></span>
							</div>
						</div>
						<! --- Tutorials --- !>
					</div>
					<div style="height: 1x; width: 100%; border-bottom: 1px solid #EAEAEA; margin: 50px 0 30px 0;"></div>
					<div class="row moduleItem">
						<! --- Knowledgebase --- !>
						<div class="col s8 principal">
							<div class="inline" style="width:100%;">
								<div style="min-width: 90px;"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/isa_knowledgebase.svg"></div>
								<div class="name_desc">
									<h5><?php _e( 'Knowledgebase', 'ithemes-sales-accelerator' ); ?></h5>
									<p><?php _e( 'Check out the Sales Accelerator documentation.', 'ithemes-sales-accelerator' ); ?></p>
								</div>
							</div>
						</div>
						<div class="col s4 laterals" style="text-align: right; display: flex; align-items: center; justify-content: flex-end;">
							<div class="click_modal">
								<span class="last_btts it_link_external"><a href="https://ithemeshelp.zendesk.com/hc/en-us/categories/115000280854-iThemes-Sales-Accelerator/" target="_blank"><?php _e( 'Learn more', 'ithemes-sales-accelerator' ); ?></a></span>
							</div>
						</div>
						<! --- Knowledgebase --- !>
					</div>
					
					<div style="height: 1x; width: 100%; border-bottom: 1px solid #EAEAEA; margin: 50px 0 30px 0;"></div>
					<div class="row moduleItem">
						<! --- Pro Support --- !>
						<div class="col s8 principal">
							<div class="inline" style="width:100%;">
								<div style="min-width: 90px;"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/isa_pro_support.svg"></div>
								<div class="name_desc">
									<h5><?php _e( 'Private, Ticketed Support', 'ithemes-sales-accelerator' ); ?></h5>
									<p><?php _e( 'Open a support ticket from the iThemes Help Desk.', 'ithemes-sales-accelerator'); ?> <?php echo $pro_string; ?></p>
								</div>
							</div>
						</div>
						<div class="col s4 laterals" style="text-align: right; display: flex; align-items: center; justify-content: flex-end;">
							<div class="click_modal">
								<span class="last_btts it_link_external"><a href="https://ithemes.com/support" target="_blank"><?php echo $pro_btn; ?></a></span>
							</div>
						</div>
						<! --- Pro Support --- !>
					</div>
					
					<div style="height: 1x; width: 100%; border-bottom: 1px solid #EAEAEA; margin: 50px 0 30px 0;"></div>
					<div class="row moduleItem">
						<! --- Basic Support --- !>
						<div class="col s8 principal">
							<div class="inline" style="width:100%;">
								<div style="min-width: 90px;"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/isa_basic_support.svg"></div>
								<div class="name_desc">
									<h5><?php _e( 'Basic Support', 'ithemes-sales-accelerator' ); ?></h5>
									<p><?php _e( 'Ask a question with our free, basic support.', 'ithemes-sales-accelerator' ); ?></p>
								</div>
							</div>
						</div>
						<div class="col s4 laterals" style="text-align: right; display: flex; align-items: center; justify-content: flex-end;">
							<div class="click_modal">
								<span class="last_btts it_link_external"><a href="https://wordpress.org/support/plugin/ithemes-sales-accelerator" target="_blank"><?php _e( 'Learn more', 'ithemes-sales-accelerator' ); ?></a></span>
							</div>
						</div>
						<! --- Knowledgebase --- !>
					</div>
					
				</div>
				
				<?php include 'template-parts/box_help.php'; ?>
				
			</div>
			<div class="col hide-on-med-and-down l2 xl2">&nbsp;</div>
		</div>
	</div>
</form>
<div class="img_footer"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/sales-acc_footer.svg" style="height:60px;"></div>