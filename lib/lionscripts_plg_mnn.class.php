<?php

if(!class_exists('lionscripts_plg_mnn'))
{
	class lionscripts_plg_mnn extends lionscripts_plg
	{
		public function __construct($plg_dir)
		{
			global $LIONSCRIPTS, $wpdb;
			
			$this->plg_name 				= 'Maintenance & Noindex Nofollow';
			$this->plg_description 			= '';
			$this->plg_version 				= '2.1';
			$this->plg_hook_version 		= '1';
			$this->plg_identifier 			= 'MNN';
			$this->plg_db_var['noindex_nofollow'] 	= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.str_replace(' ', '_', strtolower($this->plg_name)).'_noindex_nofollow';
			$this->plg_db_var['maintenance_mode'] 	= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.str_replace(' ', '_', strtolower($this->plg_name)).'_mnn_mode';
			$this->plg_db_var['maintenance_text'] 	= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.str_replace(' ', '_', strtolower($this->plg_name)).'_mnn_text';
			$this->plg_db_var['display_attr'] 		= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.str_replace(' ', '_', strtolower($this->plg_name)).'_display_attr';
			
			$this->plg_name_2 				= 'Site Maintenance';
			$this->plg_url_val 				= str_replace(' ', '-', strtolower(str_replace('&', 'and', $this->plg_name)));
			$this->plg_product_url 			= LIONSCRIPTS_HOME_PAGE_URL.'product/'.$this->plg_url_val.'/';
			$this->plg_name_pro 			= $this->plg_name.' Pro';
			$this->plg_heading 				= $this->plg_name;
			$this->plg_short_name 			= $this->plg_name;
	
			$this->site_admin_url_val 		= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'-'.$plg_dir;
			$this->site_admin_url 			= get_admin_url().'admin.php?page='.$this->site_admin_url_val;
			$this->site_admin_dashboard_url = get_admin_url().'admin.php?page='.strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'-dashboard';
			$this->site_base				= array('dir'=>ABSPATH, 'www'=>get_bloginfo('wpurl'));
			$this->plg_base 				= array('dir'=>$this->site_base['dir'].'wp-content'.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$plg_dir.DIRECTORY_SEPARATOR, 'www'=>$this->site_base['www']."/wp-content/plugins/".$plg_dir.'/');
			$this->plg_assets 				= array('dir'=>$this->plg_base['dir'].'assets'.DIRECTORY_SEPARATOR, 'www'=>$this->plg_base['www'].'assets/');
			$this->plg_css 					= array('dir'=>$this->plg_assets['dir'].'css'.DIRECTORY_SEPARATOR, 'www'=>$this->plg_assets['www'].'css/');
			$this->plg_images 				= array('dir'=>$this->plg_assets['dir'].'images'.DIRECTORY_SEPARATOR, 'www'=>$this->plg_assets['www'].'images/');
			$this->plg_javascript 			= array('dir'=>$this->plg_assets['dir'].'js'.DIRECTORY_SEPARATOR, 'www'=>$this->plg_assets['www'].'js/');
			$this->plg_others 				= array('dir'=>$this->plg_assets['dir'].'others'.DIRECTORY_SEPARATOR, 'www'=>$this->plg_assets['www'].'others/');
			$this->plg_attr					= '<font style="font-size:12px;"><center>Site Maintenance Plugin activated by <a href="'.$this->plg_product_url.'" target="_blank">'.$this->plg_name.'</a> from <a href="'.LIONSCRIPTS_HOME_PAGE_URL.'" target="_blank">'.LIONSCRIPTS_SITE_NAME.'</a>.</center></font>';
			
			$this->plg_redirect_const 		= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.strtolower($this->plg_identifier)."_activate_redirect";
			$this->plg_db_version_const 	= strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.strtolower($this->plg_identifier)."_db_version";
	
			add_action( 'admin_menu', array($this, strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_admin_menu') );
	
			register_activation_hook($this->plg_base['dir'].$plg_dir.'.php', array($this, 'install'));
			register_deactivation_hook($this->plg_base['dir'].$plg_dir.'.php', array($this, 'deactivate'));
			register_uninstall_hook($this->plg_base['dir'].$plg_dir.'.php', strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_'.strtolower($this->plg_identifier).'_uninstall');
			
			add_action('admin_init', array($this, 'admin_settings_page'));
			add_action('wp_head', array($this, 'apply_front_action'));
			add_action('wp_footer', array($this, 'attr_display'));

			$plugin_file = plugin_basename($this->plg_url_val.'.php');//$this->plg_url_val.'/'.$this->plg_url_val.'.php';
			add_filter("plugin_action_links_".$plugin_file, array($this, 'settings_link'), 10, 2);
		}
		
		public function print_admin_styles()
		{
			echo '<link rel="stylesheet" href="'.$this->plg_css['www'].'style.css" />';
		}
		
		public function get_configuration()
		{
			global $LIONSCRIPTS;
			$LIONSCRIPTS[$this->plg_identifier]['noindex_nofollow'] = get_option($this->plg_db_var['noindex_nofollow']);
			$LIONSCRIPTS[$this->plg_identifier]['maintenance_mode'] = get_option($this->plg_db_var['maintenance_mode']);
			$LIONSCRIPTS[$this->plg_identifier]['maintenance_text'] = get_option($this->plg_db_var['maintenance_text']);
			$LIONSCRIPTS[$this->plg_identifier]['display_attr'] 	= get_option($this->plg_db_var['display_attr']);
		}
		
		public function save_configuration($data)
		{
			global $LIONSCRIPTS;
			update_option( $this->plg_db_var['noindex_nofollow'], ((isset($data['noindex_nofollow']) && ($data['noindex_nofollow'])) ? $data['noindex_nofollow'] : '0') );
			update_option( $this->plg_db_var['maintenance_mode'], ((isset($data['maintenance_mode']) && ($data['maintenance_mode'])) ? $data['maintenance_mode'] : '0') );
			update_option( $this->plg_db_var['maintenance_text'], ((isset($data['maintenance_text']) && !empty($data['maintenance_text'])) ? $data['maintenance_text'] : '') );
			update_option( $this->plg_db_var['display_attr'], ((isset($data['display_attr']) && ($data['display_attr'])) ? $data['display_attr'] : '0') );
			$this->get_configuration();
		}
		
		public function install()
		{
			global $wpdb;
			add_option($this->plg_db_version_const, $this->plg_version);
			register_setting($this->plg_redirect_const, strtolower($this->plg_identifier).'_activate_redirect');
			add_option($this->plg_redirect_const, true);
		} 
		
		public function deactivate()
		{
			delete_option($this->plg_db_version_const);
			delete_option($this->plg_redirect_const);
		} 
		
		public function settings_link($links)
		{
			$settings_link = '<a href="'.$this->site_admin_url.'">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		public function admin_settings_page()
		{
			if (get_option($this->plg_redirect_const, false)) 
			{
				delete_option($this->plg_redirect_const);
				wp_redirect($this->site_admin_url);
			}
		}
				
		public function lionscripts_admin_menu()
		{
			$this->show_lionscripts_menu();
			add_submenu_page( strtolower(LIONSCRIPTS_SITE_NAME_SHORT), $this->plg_short_name, $this->plg_name_2, 'level_8', $this->site_admin_url_val, array($this, 'lionscripts_plg_f') );
		}
		
		public function show_lionscripts_menu()
		{
			global $menu;
			$lionscripts_menu_available = false;
			
			foreach($menu as $item)
			{
				if( strtolower($item[0]) == strtolower(LIONSCRIPTS_SITE_NAME_SHORT))
					return $lionscripts_menu_available = true;
			}
			
			if($lionscripts_menu_available == false)
			{
				add_menu_page(LIONSCRIPTS_SITE_NAME_SHORT, LIONSCRIPTS_SITE_NAME_SHORT, 'level_8', strtolower(LIONSCRIPTS_SITE_NAME_SHORT), strtolower(LIONSCRIPTS_SITE_NAME_SHORT), $this->plg_images['www'].'ls-icon-16.png');
	
				add_submenu_page( 
					strtolower(LIONSCRIPTS_SITE_NAME_SHORT) 
					, LIONSCRIPTS_SITE_NAME_SHORT.' Dashboard' 
					, 'Dashboard'
					, 'level_8'
					, strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'-dashboard'
					, array($this, strtolower(LIONSCRIPTS_SITE_NAME_SHORT).'_dashboard')
				);
			
				remove_submenu_page( strtolower(LIONSCRIPTS_SITE_NAME_SHORT), strtolower(LIONSCRIPTS_SITE_NAME_SHORT) );
			}
		}
	
		public function lionscripts_dashboard()
		{
			global $LIONSCRIPTS;
			$this->print_admin_styles();
			$this->use_thickbox();
			?>
			<div class="wrap">
				<div class="ls-icon-32">
					<br />
				</div>
				<h2 class="nav-tab-wrapper">
					<a href="<?php echo LIONSCRIPTS_HOME_PAGE_URL; ?>" target="_blank"><?php echo LIONSCRIPTS_SITE_NAME; ?></a>
					<a href="<?php echo $this->site_admin_dashboard_url; ?>" class="nav-tab <?php echo ( (!isset($_GET['tab']) || (trim($_GET['tab']) == '')) ? 'nav-tab-active' : '' ); ?>">Dashboard</a>
					<a href="<?php echo LIONSCRIPTS_HOME_PAGE_URL; ?>" target="_blank" class="nav-tab">Official Website</a>
					<a href="<?php echo LIONSCRIPTS_SUPPORT_PAGE_URL; ?>" target="_blank" class="nav-tab">Technical Support</a>
				</h2>
				<div class="tab_container">
					<div style="width:49%;" class="fluid_widget_container">
						<div class="postbox" id="about_lionscripts">
							<h3><span>About Us</span></h3>
							<div class="inside">
								<div class="">
									<?php
									ksort($LIONSCRIPTS['ABOUT_US']);
									$LIONSCRIPTS['N_ABOUT_US'] = end($LIONSCRIPTS['ABOUT_US']);
									echo $LIONSCRIPTS['N_ABOUT_US'];
									?>
								</div>
							</div>
						</div>
					</div>
					<div style="width:49%;margin-left:1%;" class="fluid_widget_container">
						<div class="postbox" id="more_from_lionscripts">
							<h3><span>Products from our house</span></h3>
							<div class="inside">
								<div class="">
									<p>
										<?php
										ksort($LIONSCRIPTS['WP_PRODUCTS']);
										$LIONSCRIPTS['ALL_WP_PRODUCTS'] = end($LIONSCRIPTS['WP_PRODUCTS']);
										?>
										<ul class="bullet inside">
											<?php
											foreach($LIONSCRIPTS['ALL_WP_PRODUCTS'] as $product_data)
											{
												?>
												<!--<li><a class="thickbox" title="<?php echo $product_data['name']; ?>" href="plugin-install.php?tab=plugin-information&plugin=<?php echo $product_data['wp_url_var']; ?>&TB_iframe=true&width=640&height=500"><?php echo $product_data['name']; ?></a></li>-->
												<li><a target="_blank" title="<?php echo $product_data['name']; ?>" href="<?php echo $product_data['url']; ?>"><?php echo $product_data['name']; ?></a></li>
												<?php
											}
											?>
										</ul>
									</p>
								</div>
							</div>
						</div>
					</div>
					<div class="cl"></div>
					<div style="width:49%;" class="fluid_widget_container">
						<div class="postbox" id="more_from_lionscripts">
							<h3><span>Questions and Support</span></h3>
							<div class="inside">
								<div class="">
									<p>
										<?php echo LIONSCRIPTS_SITE_NAME; ?> provides 24x7 support for all its products and services. So in terms of service, you don't need to worry about the techincal support. 
									</p>
									<p>
										If you have any concern or issue regarding any of our software, please visit <a href="<?php echo LIONSCRIPTS_SUPPORT_PAGE_URL; ?>ask" target="_blank"><?php echo preg_replace('/\/|http\:/i', '', LIONSCRIPTS_SUPPORT_PAGE_URL); ?>/ask</a> and provide complete details of your issue.
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		
		public function lionscripts_plg_f()
		{
			global $LIONSCRIPTS;
			$this->print_admin_styles();
			$this->use_thickbox();

			if($_POST)
			{
				$this->save_configuration($_POST);
				$response = '<center><b><font class="success">Settings has been successfully updated</font></b></center>';
			}
			else
				$this->get_configuration();
			
			?>
			
			<div class="wrap">
				<div class="icon-32">
					<br />
				</div>
				<h2><?php echo LIONSCRIPTS_SITE_NAME_SHORT.': '.$this->plg_heading; ?> - Settings</h2>
				<div class="content_left">
					<div id="lionscripts_plg_settings">
						Plugin Version: <b><font class="version"><?php echo $this->plg_version; ?></font> <font class="lite_version"></font></b>
                        &nbsp; | &nbsp;
                        <b><a href="<?php echo $this->plg_product_url; ?>" target="_blank" title="Visit the plugin official page">Visit Plugin Page</a></b>
                        &nbsp; | &nbsp;
                        <b><a href="<?php echo LIONSCRIPTS_HOME_PAGE_URL; ?>" target="_blank" title="Visit Official Website">Official Website</a></b>
                        &nbsp; | &nbsp;
                        <b><a href="<?php echo LIONSCRIPTS_SUPPORT_PAGE_URL; ?>" target="_blank" title="Get technical support for this plugin">Technical Support</a></b>
						<br /><br />
						
						<?php 
						if(isset($response))
						{
							echo $response.'<br />';
						}
						?>
						<form action="admin.php?page=<?php echo $this->site_admin_url_val; ?>" method="post">
							<p>
								<input type="checkbox" name="noindex_nofollow" id="noindex_nofollow" value="1" <?php if($LIONSCRIPTS[$this->plg_identifier]['noindex_nofollow'] == 1) { echo('checked="checked"'); } ?> />
								<label for="noindex_nofollow"> Show No-Index, No-Follow on All Pages</label>
							</p>
							<p>
								<input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1" <?php if($LIONSCRIPTS[$this->plg_identifier]['maintenance_mode'] == 1) { echo('checked="checked"'); } ?> />
								<label for="maintenance_mode"> Enable Maintenance Mode (Only Below Written Maintenance Notice will be shown to the frontend users)</label>
							</p>
							<p>
								<input type="checkbox" name="display_attr" id="display_attr" value="1" <?php if($LIONSCRIPTS[$this->plg_identifier]['display_attr'] == 1) { echo('checked="checked"'); } ?> />
								<label for="display_attr"> Proudly Display that you are using <?php echo LIONSCRIPTS_SITE_NAME_SHORT; ?> Site Maintenance Plugin</label>
							</p>
							<p>
								<label for="maintenance_text"> Maintenance Mode Note for Users</label>
                                <br />
								<textarea name="maintenance_text" id="maintenance_text" cols="70" rows="10" placeholder="For Example: Our site is in maintenance mode. We will be back soon."><?php if($LIONSCRIPTS[$this->plg_identifier]['maintenance_text'] != '') { echo $LIONSCRIPTS[$this->plg_identifier]['maintenance_text']; } ?></textarea>
							</p>
                            <br />
							<input type="submit" class="button-primary" name="submit_form" value="Save Changes" />
						</form>
		
						<br />
						<div class="lionscripts_plg_footer">
							<p>
								<small>For all kind of Inquiries and Support, please visit at <a href="<?php echo LIONSCRIPTS_SUPPORT_PAGE_URL; ?>ask" target="_blank"><?php echo preg_replace('/\/|http\:/i', '', LIONSCRIPTS_SUPPORT_PAGE_URL); ?>/ask</a>.</small>
							</p>
							<p>
								<ul class="socialicons color">
									<li><a href="<?php echo LIONSCRIPTS_FACEBOOK_LINK; ?>" target="_blank" class="facebook"></a></li>
									<li><a href="<?php echo LIONSCRIPTS_TWITTER_LINK; ?>" target="_blank" class="twitter"></a></li>
									<li><a href="<?php echo LIONSCRIPTS_GOOGLE_PLUS_LINK; ?>" target="_blank" class="gplusdark"></a></li>
									<li><a href="<?php echo LIONSCRIPTS_YOUTUBE_LINK; ?>" target="_blank" class="youtube"></a></li>
									<li><a href="<?php echo LIONSCRIPTS_HOME_PAGE_URL; ?>shop/feed" target="_blank" class="rss"></a></li>
								</ul>
								<div class="cl"></div>
							</p>
						</div>
					</div>
				</div>
				
				<div id="<?php echo str_replace(' ', '_', strtolower($this->plg_name)); ?>_right_container" class="content_right">
					<a href="<?php echo $this->plg_product_url; ?>" target="_blank"><img src="<?php echo $this->plg_images['www']."pro.png"; ?>" border="0" /></a>
				</div>
			</div>
			<?php
		}
			
		public function apply_front_action()
		{
			global $LIONSCRIPTS;
			
			if($LIONSCRIPTS[$this->plg_identifier]['noindex_nofollow'] == 1)
				echo '<meta name="robots" content="noindex, nofollow" />';
		}
		
		public function attr_display($return=false)
		{
			global $LIONSCRIPTS;
			$this->get_configuration();
			
			$display_attr = (($LIONSCRIPTS[$this->plg_identifier]['display_attr'] == 1) ? $this->plg_attr : '');
			
			if($return == true)
				return $display_attr;
			else
				echo $display_attr;
		}
		
		public function plugin_is_active($plugin_var)
		{
			return in_array( $plugin_var. '/' .$plugin_var. '.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
		}
		
	}
}


?>