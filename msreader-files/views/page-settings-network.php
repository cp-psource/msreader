<div class="wrap">

	<h2><?php _e('Reader-Einstellungen', 'wmd_msreader') ?> <a href="<?php echo admin_url('index.php?page=msreader.php'); ?>" class="add-new-h2"><?php _e('Gehe zum Reader', 'wmd_msreader') ?></a></h2>
	<p><?php _e('Lese, entdecke, folge und moderiere Beiträge im Dashboard aller Webseiten in Deinem Netzwerk.', 'wmd_msreader') ?></p>
	<form action="" method="post" >

		<?php
		settings_fields('wmd_msreader_options');
		$options = $this->plugin['site_options'];

		do_settings_sections('wmd_msreader_options_general');
		?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="wmd_msreader_options[location]"><?php _e('Wo soll der Reader sein?', 'wmd_msreader') ?></label>
				</th>

				<td>
					<label><input name="wmd_msreader_options[location]" type="radio" value="add_under_dashboard" <?php checked( 'add_under_dashboard', $options['location']) ?>> <?php _e('Der Reader sollte sich unter Dashboard befinden', 'wmd_msreader') ?> > <span class="reader-menu-page"><?php echo stripslashes(esc_attr($options['name'])); ?></span></label><br/>
					<label><input name="wmd_msreader_options[location]" type="radio" value="replace_dashboard_home" <?php checked( 'replace_dashboard_home', $options['location']) ?>> <?php _e('Der Reader sollte die Standard-Homepage des Dashboards ersetzen.', 'wmd_msreader') ?></label><br/>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wmd_msreader_options[name]"><?php _e('Wie soll die Reader-Seite heißen?', 'wmd_msreader') ?></label>
				</th>

				<td>
					<input type="text" class="regular-text ltr" id="msreader-page-name" name="wmd_msreader_options[name]" value="<?php echo stripslashes(esc_attr($options['name'])); ?>" />
					<p class="description"><?php _e('Dies ist der Name, der im Menü sichtbar sein wird', 'wmd_msreader') ?></p>
				</td>
			</tr>

			<tr id="msreader-control-modules" valign="top">
				<th scope="row">
					<label for="wmd_msreader_options[modules]"><?php _e('Welche Funktionen möchtest Du für den Reader aktivieren?', 'wmd_msreader') ?></label>
				</th>

				<td>
					<?php 
					foreach ($this->available_modules as $slug => $module) {
						$current = $this->helpers->is_module_enabled($module['slug'], $this->plugin['site_options']) ? 'true' : 0;
						echo '<div class="msreader-control-module"><label><input name="wmd_msreader_options[modules]['.$module['slug'].']" data-module="'.$module['slug'].'" class="wmd_msreader_options_modules" type="checkbox" value="true" '.checked( 'true', $current, 0).'> <strong>'.__($module['name'], 'wmd_msreader').'</strong> - '.__($module['description'], 'wmd_msreader').'.</label>';
						$module_options = isset($this->plugin['site_options']['modules_options'][$slug]) ? $this->plugin['site_options']['modules_options'][$slug] : array();
						$module_options = apply_filters('msreader_module_options_'.$module['slug'], '', $module_options);
						if($module_options && count($this->plugin['site_options']['modules_options'][$slug])) {
							echo ' <button class="button button-secondary open-module-options" data-module="'.$slug.'">'.__('Konfigurieren', 'wmd_msreader').'</button><br/>';
							echo '<div data-module="'.$slug.'" class="sub-options"><div class="postbox">'.$module_options.'</div></div>';
						}
						else
							echo '<br/>';
						echo '</div>';
					}
					?>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wmd_msreader_options[posts_from]"><?php _e('Sollten Beiträge von privaten Webseiten in den Reader aufgenommen werden?', 'wmd_msreader') ?></label>
				</th>

				<td>
					<label><input name="wmd_msreader_options[posts_from]" type="radio" value="all" <?php checked( 'all', $options['posts_from']) ?>> <?php _e('Ja, Beiträge von allen Webseiten einbeziehen', 'wmd_msreader') ?></label><br/>
					<label><input name="wmd_msreader_options[posts_from]" type="radio" value="public" <?php checked( 'public', $options['posts_from']) ?>> <?php _e('Nein, nur Beiträge von öffentlichen Webseiten einbeziehen (wo Suchmaschinensichtbarkeit aktiviert ist)', 'wmd_msreader') ?></label><br/>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wmd_msreader_options[location]"><?php _e('Welche Funktion ist auf der Reader-Seite standardmäßig verfügbar?', 'wmd_msreader') ?></label>
				</th>

				<td>
					<?php 
					if($options['modules'] && is_array($options['modules'])) {
						echo '<select id="wmd_msreader_options_default_module" name="wmd_msreader_options[default_module]">';
						foreach ($this->available_modules as $slug => $module) {
							if(isset($this->available_modules[$module['slug']]['can_be_default']) && $this->available_modules[$module['slug']]['can_be_default'] == false)
								continue;

							$display = !$this->helpers->is_module_enabled($module['slug']) ? ' style="display: none;"' : '';
							echo '<option value="'.$module['slug'].'" data-module="'.$module['slug'].'" '.$display.selected( $options['default_module'], $module['slug'], false ).'>'.__($module['name'], 'wmd_msreader').'</option>';
						}
						echo '</select>';
					}
					?>
					<p class="description"><?php _e('Dies ist die Standardfunktion, die beim Öffnen der Reader-Seite verwendet wird', 'wmd_msreader') ?></p>
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" class="button button-primary" value="<?php _e('Änderungen speichern', 'wmd_msreader') ?>" />
		</p>
	</form>

</div>