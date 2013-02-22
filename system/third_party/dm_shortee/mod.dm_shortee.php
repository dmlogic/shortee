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

class Dm_shortee {

	// -----------------------------------------------------------------

	function __construct(){

		$this->EE =& get_instance();

		$this->EE->load->model('shortee');
	}

	// -----------------------------------------------------------------

	/**
	 * Redirect to a saved URL, or home page if not available
	 */
	public function redirect() {

		// @todo. How to handle https?
		$domain = 'http://'.$_SERVER['HTTP_HOST'];
		$data = $this->EE->shortee->get_url($domain,$this->EE->input->get('url',true));

		if(!$data) {
			$loc = $this->EE->config->item('site_url');
		} else {
			$loc = $data['url'];
			$this->EE->shortee->log_view($data['id'],$_SERVER['REMOTE_ADDR']);
		}

		header("Location: ".$loc, true, 302);
		exit;
	}
}