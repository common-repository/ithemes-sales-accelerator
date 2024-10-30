<?php

if ( !defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
$modules = get_option( 'it_rooster_modules_status' );

$import_status = get_option( 'it_rooster_reporting_import_status' );
$progress      = isset( $import_status['progress'] ) ? $import_status['progress'] : 0;
$time_string = __( 'Import is starting', 'ithemes-sales-accelerator' );

$progress = round( $progress, 1 );

if ( $progress && $progress < 100 ) {
	
	// Average refresh time ( in seconds )
	$period     	  = 60;
	$items_per_period = 400;
	$time_left 		  = 0;
	$time_string      = __( 'Import_completed', 'ithemes-sales-accelerator' );
	
	$tot_products = ( isset( $import_status['total_products'] ) )    ? $import_status['total_products']    : 0;
	$tot_order    = ( isset( $import_status['total_orders'] ) )      ? $import_status['total_orders']      : 0;
	
	$imp_products = ( isset( $import_status['imported_products'] ) ) ? $import_status['imported_products'] : 0;
	$imp_order    = ( isset( $import_status['imported_orders'] ) )   ? $import_status['imported_orders']   : 0;
	$items_left   = $tot_products + $tot_order - $imp_products - $imp_order;
	if ( $items_left > 0 ) {
		$time_left = ( $items_left / $items_per_period ) * $period;
		$time_left_mins = round( $time_left / 60, 0 );
		if ( !$time_left_mins ) {
			$time_left_mins = 1;
		}
		
		$mins_string = ( $time_left_mins == 1 ) ? __( 'minutes remaining', 'ithemes-sales-accelerator' ) : __( 'minutes remaining', 'ithemes-sales-accelerator' );
		$time_string = '(' . __('approx.', 'ithemes-sales-accelerator'). ' ' . $time_left_mins . ' ' . $mins_string . ')';
	}
	else {
		$progress = 100;
	}
}

?>

<div id="rst_reporting_wrapper">
	<div class="inline" style="width:100%;">
		<div style="display: block;"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/sales-acc_white.svg" style="height:50px;"></div>
		<div class="btt_dashboard"><a href="<?php echo get_dashboard_url(); ?>"><span><?php _e( 'DASHBOARD', 'ithemes-sales-accelerator' ); ?></span></a></div>
	</div>
</div>
<div class="inside">
	<div class="row">
		<div class="col hide-on-med-and-down l1 xl2">&nbsp;</div>
		<div class="col s12 m12 l10 xl8">
			<div class="box_content first_box">
				<img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/sales-acc_icon_blue.svg">
				<h3><?php _e( 'Welcome to Sales Accelerator', 'ithemes-sales-accelerator' ); ?></h3>
				<p><?php _e( 'Get detailed data and e-commerce insights about your online store.', 'ithemes-sales-accelerator' ); ?></p>
				<div style="padding-top: 30px;">
					<?php if ( !isset( $modules['rooster_reporting']['active'] ) || !$modules['rooster_reporting']['active'] ) {?>
						<a id="it_rooster_activate_reporting_module_a" href="#"><span class="btt_upgrade activate_rep"><?php _e( 'Activate Reporting', 'ithemes-sales-accelerator' ); ?></span></a>
					<?php } else if ( isset( $import_status['status'] ) && $import_status['status'] == 'deleted' ) { ?>
						<span id="it_rst_start_import" class="btt_download"><?php _e( 'Reporting Import', 'ithemes-sales-accelerator' ); ?></span>
					<?php } ?>
					<a href="<?php menu_page_url( 'ithemes-sales-acc' ) ?>"><span class="btt_download"><?php _e( 'Manage Modules', 'ithemes-sales-accelerator' ); ?></span></a>
				</div>
				<?php if ( isset( $modules['rooster_reporting']['active'] ) && $modules['rooster_reporting']['active'] && $progress < 100 ) { ?>
					<div style="width:70%; margin: 0 auto;"><div class="progress it_rooster_reporting_progressbar">
					  <div id="it_rooster_reporting_import_progressbar_top" class="progress-bar active" role="progressbar"
					  aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $progress; ?>%"><?php echo $progress; ?>%
					  </div>
					</div>
					<p class="txt_remaining"><?php echo $time_string; ?></p></div>
				<?php } ?>
			</div>
			
			<?php do_shortcode( '[it_rst_notifications]' ); ?>
		
			<div class="box_content second_box">
				
				<div style="position:relative;">
					<svg width="925px" height="461px" viewBox="0 0 925 461" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					    <!-- Generator: Sketch 45.2 (43514) - http://www.bohemiancoding.com/sketch -->
					    <desc>Created with Sketch.</desc>
					    <defs>
					        <path d="M4.47565587e-13,8.00565501 C4.51531966e-13,3.58425384 3.57530896,0 7.99687159,0 L915.003128,0 C919.419679,0 923,3.57669975 923,8.00925748 L923,355.551402 L811.579764,371.436184 C809.395608,371.747571 806.05967,372.862222 804.124857,373.927965 L695.750143,433.623436 C693.817067,434.688222 690.51816,435.092809 688.378276,434.526166 L580.746724,406.025236 C578.60843,405.459014 575.255053,405.765941 573.263585,406.707543 L465.111415,457.843859 C463.116886,458.786909 459.891047,458.776714 457.888585,457.812557 L349.736415,405.738844 C347.741886,404.778507 344.381776,404.370952 342.220157,404.830937 L234.654843,427.720465 C232.498258,428.179379 228.995357,428.193228 226.828548,427.750919 L119.296452,405.800483 C117.130694,405.358388 113.677099,405.567337 111.585512,406.26622 L5.68434189e-14,443.551402 L4.47565587e-13,8.00565501 Z" id="path-1"></path>
					        <filter x="-0.2%" y="-0.3%" width="100.3%" height="100.7%" filterUnits="objectBoundingBox" id="filter-2">
					            <feMorphology radius="0.5" operator="dilate" in="SourceAlpha" result="shadowSpreadOuter1"></feMorphology>
					            <feOffset dx="0" dy="0" in="shadowSpreadOuter1" result="shadowOffsetOuter1"></feOffset>
					            <feColorMatrix values="0 0 0 0 0.901960784   0 0 0 0 0.91372549   0 0 0 0 0.921568627  0 0 0 1 0" type="matrix" in="shadowOffsetOuter1"></feColorMatrix>
					        </filter>
					    </defs>
					    <g id="Activation" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
					        <g transform="translate(-273.000000, -589.000000)" id="Promo">
					            <g transform="translate(274.000000, 590.000000)">
					                <g id="Group">
					                    <g id="Rectangle-Copy">
					                        <use fill="black" fill-opacity="1" filter="url(#filter-2)" xlink:href="#path-1"></use>
					                        <use fill="#132E47" fill-rule="evenodd" xlink:href="#path-1"></use>
					                    </g>
					                </g>
					            </g>
					        </g>
					    </g>
					</svg>
					<div class="second_box_content">
						<h4><?php _e( 'All the Most Important Stats About Your Store in One Place', 'ithemes-sales-accelerator' ); ?></h4>
						<p><?php _e( 'See how your WooCommerce<sup>®</sup> store is performing in a new way.', 'ithemes-sales-accelerator' ); ?></p>
					</div>
					<div class="graph">
						<img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/graph.svg">
					</div>
				</div>
				<div class="content_padding">
					
					<div class="row">
						<div class="col s6" style="margin-bottom:30px; min-height: 200px;">
						
							<img src="<?php echo IT_RST_PLUGIN_URL; ?>assets/img/reports.svg">
							<h3><?php _e( 'Reports', 'ithemes-sales-accelerator' ); ?></h3>
							<p style="margin-bottom: 20px;"><?php _e( 'Get detailed data and e-commerce insights about your online store.', 'ithemes-sales-accelerator' ); ?></p>
							<?php if ( !defined( 'IT_RST_REPORTING_PREMIUM_ACTIVE' ) ) { ?>
								<a href="https://ithemes.com/sales-accelerator/woocommerce-reporting-pro/" target="_blank"><span class="soon soon_about soon_pro">Upgrade to pro</span></a>
							<?php } else { ?>
								<a style="visibility: hidden;" href="#" target="_blank"><span class="soon soon_about soon_pro">Pro</span></a>
							<?php } ?>
						
						</div>
						<div class="col s6" style="margin-bottom:30px; min-height: 200px;">
							
							<img src="<?php echo IT_RST_PLUGIN_URL; ?>assets/img/inventory.svg" style="height:64px; width:auto;">
							<h3><?php _e( 'Inventory', 'ithemes-sales-accelerator' ); ?></h3>
							<p style="margin-bottom: 20px;"><?php _e( 'Manage the stock of your products with multiple warehouses.', 'ithemes-sales-accelerator' ); ?></p>
							<?php if ( !defined( 'IT_RST_WH_PREMIUM_ACTIVE' ) ) { ?>
							<a href="https://ithemes.com/sales-accelerator/woocommerce-inventory/" target="_blank"><span class="soon soon_about soon_pro">Upgrade to pro</span></a>
							<?php } else { ?>
								<a style="visibility: hidden;" href="#" target="_blank"><span class="soon soon_about soon_pro">Pro</span></a>
							<?php } ?>
						
						</div>

						<div class="col s6" style="margin-bottom:30px; min-height: 200px;">
						
							<img src="<?php echo IT_RST_PLUGIN_URL; ?>assets/img/omnichannel.svg">
							<h3><?php _e( 'Multichannel', 'ithemes-sales-accelerator' ); ?></h3>
							<p style="margin-bottom: 20px;"><?php _e( 'Connects your WooCommerce store with eBay, Amazon, Facebook and Google Merchant.', 'ithemes-sales-accelerator' ); ?></p>
							<?php if ( !defined( 'IT_RST_OC_PREMIUM_ACTIVE' ) ) { ?>
							<a href="https://ithemes.com/sales-accelerator/woocommerce-omnichannel/" target="_blank"><span class="soon soon_about soon_pro">Upgrade to pro</span></a>
							<?php } else { ?>
								<a style="visibility: hidden;" href="#" target="_blank"><span class="soon soon_about soon_pro">Pro</span></a>
							<?php } ?>
						
						</div>
						
						<div class="col s6" style="margin-bottom:30px; min-height: 200px;">
						
							<img src="<?php echo IT_RST_PLUGIN_URL; ?>assets/img/marketing_blue.svg">
							<h3><?php _e( 'Marketing Automation', 'ithemes-sales-accelerator' ); ?></h3>
							<p style="margin-bottom: 20px;"><?php _e( "Grow your sales with powerful features.", 'ithemes-sales-accelerator' ); ?></p>
							<span class="soon soon_about">Soon</span>

						
						</div>
						
					</div>
					
					
					<div class="row">	
						<div class="col s12 world_map">
						
							<h3><?php _e( 'Track your Global Growth', 'ithemes-sales-accelerator' ); ?></h3>
							<p><?php _e( 'See how your store is performing around the world.', 'ithemes-sales-accelerator' ); ?></p>
							<div style="width:100%; text-align:center;"><img style="width:100%;" src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/world_map.png"></div>
							<div style="width:100%; text-align:center; margin-top:40px;">
								<?php if ( !defined( 'IT_RST_PLUGIN_PREMIUM_ACTIVE' ) ) { ?>
								<a href="https://ithemes.com/sales-accelerator" target="_blank" class="a_btt_upgrade"><span class="btt_upgrade"><b><?php _e( 'Upgrade', 'ithemes-sales-accelerator' ); ?></b> <?php _e( 'to the full experience', 'ithemes-sales-accelerator' ); ?></span></a>
								<?php } ?>
							</div>
						</div>
					</div>
					
				</div>
				
			</div>
			<?php if ( $progress < 100 ) { ?>
			<div class="box_content">
			
				<h4><?php _e( "We’re almost there", 'ithemes-sales-accelerator' ); ?></h4>
				<p><?php _e( 'Get detailed data and e-commerce insights about your online store.', 'ithemes-sales-accelerator' ); ?></p>
				
					<div style="width:70%; margin: 0 auto;"><div class="progress it_rooster_reporting_progressbar">
					  <div id="it_rooster_reporting_import_progressbar_bottom" class="progress-bar active" role="progressbar"
					  aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $progress; ?>%"><?php echo $progress; ?>%
					  </div>
					</div>
					<p class="txt_remaining"><?php echo $time_string; ?></p></div>
				
			</div>
			<?php } ?>
			
			<?php include 'template-parts/box_app.php'; ?>
			
			<?php include 'template-parts/box_help.php'; ?>
		
		</div>
		<div class="col hide-on-med-and-down l2 xl2">&nbsp;</div>
	</div>
</div>
<div class="img_footer"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/sales-acc_footer.svg"></div>	
