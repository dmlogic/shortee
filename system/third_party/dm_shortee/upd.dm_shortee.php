<?php if (!defined('BASEPATH')) die('No direct script access allowed');
/**
 * DM Shortee for Expression Engine
 * v 0.3
 *
 * This software is copywright DM Logic Ltd
 * www.dmlogic.com
 *
 * You may use this software on commercial and
 * non commercial websites AT YOUR OWN RISK.
 * No warranty is provided nor liability accepted.
 *
 */
class Dm_shortee_upd {

	var $version	=	'0.3';
	var $module_name=	'Dm_shortee';

	// -----------------------------------------------------------------

	function Dm_shortee_upd(){
		$this->EE =& get_instance();
	}

	// -----------------------------------------------------------------

	/**
	 * Installer
	 *
	 * @return boolean
	 */
	public function install() {

		$this->check_dependencies();

		// install module
		$module = array(	'module_name' => $this->module_name,
							'module_version' => $this->version,
							'has_cp_backend' => 'y',
							'has_publish_fields' => 'n' );

		$this->EE->db->insert('modules', $module);

		// install redirect action
		$data = array(
			'class'	=> $this->module_name,
			'method' => 'redirect'
		);
		$this->EE->db->insert('actions', $data);

		// create data tables
		$sql = "
		CREATE TABLE IF NOT EXISTS `exp_shortee_settings`(
			last_number bigint(20) unsigned NOT NULL default '0',
			short_domain text NOT NULL default '',
			KEY last_number (last_number)
		)
		TYPE = MYISAM
		";
		$this->EE->db->query($sql);
		$this->EE->db->query("INSERT INTO exp_shortee_settings VALUES (1111111,'http://sho.rt')");

		$sql = "
			CREATE TABLE IF NOT EXISTS `exp_shortee_urls` (
				id int(10) unsigned NOT NULL auto_increment,
				url text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
				code varchar(20) CHARACTER SET utf8 COLLATE utf8_bin default '',
				date_added datetime NOT NULL default '0000-00-00 00:00:00',
				views int(11) NOT NULL DEFAULT '0',
				domain tinytext NOT NULL,
				PRIMARY KEY  (id), UNIQUE KEY code (code)
			) TYPE = MYISAM
			";
		$this->EE->db->query($sql);

		$this->install_views_table();

		return TRUE;
	}

	// -----------------------------------------------------------------

	/**
	 * Unintaller
	 *
	 * @return boolean
	 */
	public function uninstall()	{
		// Load dbforge
		$this->EE->load->dbforge();

		// Remove
		$this->EE->dbforge->drop_table('shortee_settings');
		$this->EE->dbforge->drop_table('shortee_urls');

		$this->EE->db->where('module_name', $this->module_name);
		$this->EE->db->delete('modules');
		$this->EE->db->where('class', $this->module_name);
		$this->EE->db->delete('actions');

		return TRUE;
	}

	// -----------------------------------------------------------------

	/**
	 * Update handler
	 *
	 * @param float $current
	 * @return boolean
	 */
	public function update($current = '') {

		if ($current == $this->version) {
			return FALSE;
		}

		// update 0.1 to 0.2
		if ($current < 0.2) {
			$this->install_views_table();

			$this->EE->db->query("ALTER TABLE `exp_shortee_urls` ADD `views` INT NOT NULL DEFAULT '0'");
		}

		// update 0.2 to 0.3
		if ($current < 0.3) {

			$this->EE->db->query("ALTER TABLE `exp_shortee_settings` CHANGE COLUMN `short_domain` `short_domain` TEXT NOT NULL;");
			$this->EE->db->query("ALTER TABLE `exp_shortee_urls` ADD COLUMN `domain` TINYTEXT NOT NULL  AFTER `views` ;");

		}

		return TRUE;
	}

	// -----------------------------------------------------------------

	/**
	 * Ensure we're safe to install this module
	 */
	private function check_dependencies() {

		$this->EE->db->where('module_name','Ip_to_nation');
		$query = $this->EE->db->get('modules');

		if(!$query->num_rows()) {
			$this->EE->output->fatal_error('Shortee requires the IP to Nation module to be installed');
		}
	}

	// -----------------------------------------------------------------

	/**
	 * Add views table
	 */
	private function install_views_table() {

		$sql = "CREATE TABLE IF NOT EXISTS `exp_shortee_views` (
				  `date_viewed` datetime NOT NULL,
				  `url_id` int(11) NOT NULL,
				  `ip_address` tinytext NOT NULL,
				  `country_code` varchar(2) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

		$this->EE->db->query($sql);
	}
}
