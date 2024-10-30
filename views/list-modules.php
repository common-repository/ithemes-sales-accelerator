<?php 
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* This shows the available modules list
**/

global $rst_modules;

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
					<h4><?php _e( 'List of Available Modules', 'ithemes-sales-accelerator' ); ?></h4>
					<div class="spinner-wrapper">
            			<div class="sk-fading-circle">
						  <div class="sk-circle1 sk-circle"></div>
						  <div class="sk-circle2 sk-circle"></div>
						  <div class="sk-circle3 sk-circle"></div>
						  <div class="sk-circle4 sk-circle"></div>
						  <div class="sk-circle5 sk-circle"></div>
						  <div class="sk-circle6 sk-circle"></div>
						  <div class="sk-circle7 sk-circle"></div>
						  <div class="sk-circle8 sk-circle"></div>
						  <div class="sk-circle9 sk-circle"></div>
						  <div class="sk-circle10 sk-circle"></div>
						  <div class="sk-circle11 sk-circle"></div>
						  <div class="sk-circle12 sk-circle"></div>
						</div>
						<span><?php _e( 'Saving', 'ithemes-sales-accelerator' ); ?></span>		
					</div>
					<?php foreach ( $rst_modules as $module ) :
						if ( !isset( $module['extends'] ) ||  !$module['extends'] ) :
							$description = ( isset( $module['description'] ) ) ? $module['description'] 		 : '';
							$name        = ( isset( $module['name'] ) )  	   ? $module['name'] 	   			 : '';
							$image       = ( isset( $module['image'] ) ) 	   ? $module['image'] 	   			 : '';
							$slug        = ( isset( $module['slug'] ) )  	   ? $module['slug']  	   			 : '';
							$modal_txt   = ( isset( $module['modal_txt'] ) )   ? $module['modal_txt']  			 : '';
							$external    = ( isset( $module['external'] ) )    ? $module['external']   			 : false;
							$available   = ( isset( $module['available'] ) )   ? $module['available']  			 : true;
							$menu_slug   = ( isset( $module['menu_slug'] ) )   ? $module['menu_slug']  		     : '';
							$pro	     = ( isset( $module['pro'] ) )		   ? '<span class="soon soon_name">Pro</span>' : '';
							$sales_page  = ( isset( $module['sales_page'] ) )  ? $module['sales_page'] 			 : '';
							$menu_name	 = ( isset( $module['menu_name'] ) )   ? $module['menu_name'] 			 : __( 'Configure', 'ithemes-sales-accelerator' );
							$checked     = '';
														
							$menu_url	 = menu_page_url( $menu_slug, false );
																					
							if ( isset( $values['modules'][$module['slug']]['active'] ) && $values['modules'][$module['slug']]['active'] == 1 ) {
			                    $checked = "checked";
				            }
				            							
							?>
							<div style="height: 1x; width: 100%; border-bottom: 1px solid #EAEAEA; margin: 50px 0 30px 0;"></div>
							<div class="row moduleItem">
							<?php if ( $available ) {?>
								<div class="col s2 laterals" style="text-align: left; display: flex; align-items: center;">
									<label class="switch-modules">
										<input type="hidden" name="<?php echo $slug; ?>" value="0">
										<input class="it_rooster_settings_checkbox" type="checkbox" <?php echo $checked; ?> name="<?php echo $slug; ?>" id="<?php echo $slug; ?>">
										<span class="slider-m round-m"></span>
									</label>
								</div>
							<?php } else if ( $sales_page ) { ?>
								<div class="col s2 laterals" style="text-align: left; display: flex; align-items: center;">
									<span class="soon"><?php _e( 'Pro Only', 'ithemes-sales-accelerator' ); ?></span>
								</div>
							<?php } else { ?>
								<div class="col s2 laterals" style="text-align: left; display: flex; align-items: center;">
									<span class="soon"><?php _e( 'SOON', 'ithemes-sales-accelerator' ); ?></span>
								</div>
							<?php } ?>
								<div class="col s7 principal">
									<div class="inline" style="width:100%;">
										<img src="<?php echo $image; ?>">
										<div class="name_desc">
											<h5><?php echo $name; echo $pro;?></h5>
											<p><?php  echo $description; ?></p>
										</div>
									</div>
								</div>
								<div class="col s12 m3 l3 xl3 laterals" style="text-align: right; display: flex; align-items: center; justify-content: flex-end;">
									<div class="click_modal">
										<?php if ( $menu_url && $available ) { ?>
											<a style="text-decoration: none; color: #000000;" href="<?php echo $menu_url; ?>"><span class="last_btts"> <?php echo $menu_name; ?></span></a>
										<?php } else if ( $sales_page ) { ?>
											<a target="_blank" style="text-decoration: none; color: #000000;" href="<?php echo $sales_page; ?>"><span class="last_btts"> <?php _e( 'Upgrade', 'ithemes-sales-accelerator' ); ?></span></a>
										<?php } else { ?>
											<span class="last_btts"><?php _e( 'Learn more', 'ithemes-sales-accelerator' ); ?></span>
										<?php } ?>
										<! --- Modal --- !>
										<div class="modal-modules">
											<div id="modal_content">
												<div class="inline" style="width:100%; position: relative;">
													<img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/reports_grey.svg">
													<div class="name_desc">
														<h5><?php _e( 'Reports', 'ithemes-sales-accelerator'); ?></h5>
														<p><?php _e( 'Simple, concise ecommerce stats.', 'ithemes-sales-accelerator' ); ?></p>
													</div>
													<div style="position: absolute; right: 0;">
														<svg class="close" width="17px" height="17px" viewBox="0 0 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
														    <defs></defs>
														    <g id="Modules" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
														        <g id="Modules---Welcome" transform="translate(-993.000000, -704.000000)" fill-rule="nonzero" fill="#393939">
														            <g id="Modal-Info" transform="translate(418.000000, 664.000000)">
														                <g id="Group-2" transform="translate(575.000000, 40.000000)">
														                    <path d="M8.5,7.08578644 L1.70710678,0.292893219 C1.31658249,-0.0976310729 0.683417511,-0.0976310729 0.292893219,0.292893219 C-0.0976310729,0.683417511 -0.0976310729,1.31658249 0.292893219,1.70710678 L7.08578644,8.5 L0.55714681,15.0286396 C0.166622518,15.4191639 0.166622518,16.0523289 0.55714681,16.4428532 C0.947671102,16.8333775 1.58083608,16.8333775 1.97136037,16.4428532 L8.5,9.91421356 L15.0286396,16.4428532 C15.4191639,16.8333775 16.0523289,16.8333775 16.4428532,16.4428532 C16.8333775,16.0523289 16.8333775,15.4191639 16.4428532,15.0286396 L9.91421356,8.5 L16.7071068,1.70710678 C17.0976311,1.31658249 17.0976311,0.683417511 16.7071068,0.292893219 C16.3165825,-0.0976310729 15.6834175,-0.0976310729 15.2928932,0.292893219 L8.5,7.08578644 L8.5,7.08578644 Z" id="Combined-Shape"></path>
														                </g>
														            </g>
														        </g>
														    </g>
														</svg>
													</div>
												</div>
												<div style="height: 1x; width: 100%; border-bottom: 1px solid #EAEAEA; margin: 30px 0;"></div>
												<div class="content_modal">
													<?php echo $modal_txt; ?>
													<div class="inline newsletter" style="width: 100%; margin-top: 30px;">
														<input type="text"><span class="btt_upgrade"><b><?php _e( 'Subscribe', 'ithemes-sales-accelerator' ); ?></b></span>
													</div>
													<p style="text-align: center;font-size: .8em;">(<?php _e( 'Get email updates when new Modules are launched', 'ithemes-sales-accelerator' ); ?>)</p>
												</div>
											</div>
										</div>
										<! --- Modal --- !>
									</div>
								</div>
							</div>
					<?php endif;
						endforeach;
					?>
					<div style="height: 1x; width: 100%; border-bottom: 1px solid #EAEAEA; margin: 30px 0;"></div>
					<div class="row moduleItem">
						<div class="col s2 laterals" style="text-align: left; display: flex; align-items: center;">
							<span class="soon"><?php _e( 'SOON', 'ithemes-sales-accelerator' ); ?></span>
						</div>
						<div class="col s7 principal">
							<div class="inline" style="width:100%;">
								<img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/marketing_grey.svg">
								<div class="name_desc">
									<h5><?php _e( 'Marketing Automation', 'ithemes-sales-accelerator' ); ?></h5>
									<p><?php _e( 'Grow your sales with powerful features.', 'ithemes-sales-accelerator' ); ?></p>
								</div>
							</div>
						</div>
							<div class="col s12 m3 l3 xl3 laterals" style="text-align: right; display: flex; align-items: center; justify-content: flex-end;">
							<div class="click_modal">
								<span class="last_btts"><?php _e( 'Learn more', 'ithemes-sales-accelerator' ); ?></span>
								<! --- Modal --- !>
								<div class="modal-modules">
									<div id="modal_content">
										<div class="inline" style="width:100%; position: relative;">
											<img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/reports_grey.svg">
											<div class="name_desc">
												<h5><?php _e( 'Marketing Automation', 'ithemes-sales-accelerator' ); ?></h5>
												<p><?php _e( 'Grow your sales with powerful features.', 'ithemes-sales-accelerator' ); ?></p>
											</div>
											<div style="position: absolute; right: 0;">
												<svg class="close" width="17px" height="17px" viewBox="0 0 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
												    <defs></defs>
												    <g id="Modules" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
												        <g id="Modules---Welcome" transform="translate(-993.000000, -704.000000)" fill-rule="nonzero" fill="#393939">
												            <g id="Modal-Info" transform="translate(418.000000, 664.000000)">
												                <g id="Group-2" transform="translate(575.000000, 40.000000)">
												                    <path d="M8.5,7.08578644 L1.70710678,0.292893219 C1.31658249,-0.0976310729 0.683417511,-0.0976310729 0.292893219,0.292893219 C-0.0976310729,0.683417511 -0.0976310729,1.31658249 0.292893219,1.70710678 L7.08578644,8.5 L0.55714681,15.0286396 C0.166622518,15.4191639 0.166622518,16.0523289 0.55714681,16.4428532 C0.947671102,16.8333775 1.58083608,16.8333775 1.97136037,16.4428532 L8.5,9.91421356 L15.0286396,16.4428532 C15.4191639,16.8333775 16.0523289,16.8333775 16.4428532,16.4428532 C16.8333775,16.0523289 16.8333775,15.4191639 16.4428532,15.0286396 L9.91421356,8.5 L16.7071068,1.70710678 C17.0976311,1.31658249 17.0976311,0.683417511 16.7071068,0.292893219 C16.3165825,-0.0976310729 15.6834175,-0.0976310729 15.2928932,0.292893219 L8.5,7.08578644 L8.5,7.08578644 Z" id="Combined-Shape"></path>
												                </g>
												            </g>
												        </g>
												    </g>
												</svg>
											</div>
										</div>
										<div style="height: 1x; width: 100%; border-bottom: 1px solid #EAEAEA; margin: 30px 0;"></div>
										<div class="content_modal">
											<p>The Marketing Automation Module will add powerful, intuitive automations to meet your store's marketing needs so you can grow your sales and save more time.</p>
											<p>We're busy working to add this future module to iThemes Sales Accelerator, so sign up below to be the first to know when these new features become available.</p>
											<div class="inline newsletter" style="width: 100%; margin-top: 30px;">
												<input type="text"><span class="btt_upgrade"><b><?php _e( 'Subscribe', 'ithemes-sales-accelerator' ); ?></b></span>
											</div>
											<p style="text-align: center;font-size: .8em;">(<?php _e( 'Get email updates when new Modules are launched', 'ithemes-sales-accelerator'); ?>)</p>
										</div>
									</div>
								</div>
								<! --- Modal --- !>
							</div>
						</div>
					</div>
					<div style="height: 1x; width: 100%; border-bottom: 1px solid #EAEAEA; margin: 30px 0;"></div>
					<div class="row moduleItem">
						<div class="col s2 laterals" style="text-align: left; display: flex; align-items: center;">
							<span class="soon"><?php _e( 'SOON', 'ithemes-sales-accelerator' ); ?></span>
						</div>
						<div class="col s7 principal">
							<div class="inline" style="width:100%;">
								<img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/email_grey.svg">
								<div class="name_desc">
									<h5><?php _e( 'Email Templates', 'ithemes-sales-accelerator' ); ?></h5>
									<p><?php _e( 'Customize the emails that are sent to your customers.', 'ithemes-sales-accelerator' ); ?></p>
								</div>
							</div>
						</div>
							<div class="col s12 m3 l3 xl3 laterals" style="text-align: right; display: flex; align-items: center; justify-content: flex-end;">
							<div class="click_modal">
								<span class="last_btts"><?php _e( 'Learn more', 'ithemes-sales-accelerator' ); ?></span>
								<! --- Modal --- !>
								<div class="modal-modules">
									<div id="modal_content">
										<div class="inline" style="width:100%; position: relative;">
											<img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/email_grey.svg">
											<div class="name_desc">
												<h5><?php _e( 'Email Templates', 'ithemes-sales-accelerator' ); ?></h5>
												<p><?php _e( 'Customize the emails that are sent to your customers.', 'ithemes-sales-accelerator' ); ?></p>
											</div>
											<div style="position: absolute; right: 0;">
												<svg class="close" width="17px" height="17px" viewBox="0 0 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
												    <defs></defs>
												    <g id="Modules" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
												        <g id="Modules---Welcome" transform="translate(-993.000000, -704.000000)" fill-rule="nonzero" fill="#393939">
												            <g id="Modal-Info" transform="translate(418.000000, 664.000000)">
												                <g id="Group-2" transform="translate(575.000000, 40.000000)">
												                    <path d="M8.5,7.08578644 L1.70710678,0.292893219 C1.31658249,-0.0976310729 0.683417511,-0.0976310729 0.292893219,0.292893219 C-0.0976310729,0.683417511 -0.0976310729,1.31658249 0.292893219,1.70710678 L7.08578644,8.5 L0.55714681,15.0286396 C0.166622518,15.4191639 0.166622518,16.0523289 0.55714681,16.4428532 C0.947671102,16.8333775 1.58083608,16.8333775 1.97136037,16.4428532 L8.5,9.91421356 L15.0286396,16.4428532 C15.4191639,16.8333775 16.0523289,16.8333775 16.4428532,16.4428532 C16.8333775,16.0523289 16.8333775,15.4191639 16.4428532,15.0286396 L9.91421356,8.5 L16.7071068,1.70710678 C17.0976311,1.31658249 17.0976311,0.683417511 16.7071068,0.292893219 C16.3165825,-0.0976310729 15.6834175,-0.0976310729 15.2928932,0.292893219 L8.5,7.08578644 L8.5,7.08578644 Z" id="Combined-Shape"></path>
												                </g>
												            </g>
												        </g>
												    </g>
												</svg>
											</div>
										</div>
										<div style="height: 1x; width: 100%; border-bottom: 1px solid #EAEAEA; margin: 30px 0;"></div>
										<div class="content_modal">
											<p>The Email Templates Module will add the ability to customize the emails that are sent to your customers so you can personalize the way your store communicates.</p>
											<p>We're busy working to add this future module to iThemes Sales Accelerator, so sign up below to be the first to know when these new features become available.</p>
											<div class="inline newsletter" style="width: 100%; margin-top: 30px;">
												<input type="text"><span class="btt_upgrade"><b><?php _e( 'Subscribe', 'ithemes-sales-accelerator'); ?></b></span>
											</div>
											<p style="text-align: center;font-size: .8em;">(<?php _e( 'Get email updates when new Modules are launched', 'ithemes-sales-accelerator' ); ?>)</p>
										</div>
									</div>
								</div>
								<! --- Modal --- !>
							</div>
						</div>
					</div>
				</div>
				
				<?php include 'template-parts/box_help.php'; ?>
				
			</div>
			<div class="col hide-on-med-and-down l2 xl2">&nbsp;</div>
		</div>
	</div>
</form>
<div class="img_footer"><img src="<?php echo IT_RST_PLUGIN_URL; ?>/assets/img/sales-acc_footer.svg"></div>
