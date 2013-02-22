<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Data model for Shortee tables
 *
 * Some of this code is inspired by the PHURL project:
 * http://code.google.com/p/phurl/
 *
 */
class Shortee extends CI_Model {

	private $settings;

	private $domain_list;

	public $error;

	public function  __construct() {

		$this->get_settings();
	}

	// -----------------------------------------------------------------

	/**
	 * Get the settings for shortee
	 *
	 * @return array
	 */
	public function get_settings() {

		if($this->settings) {
			return $this->settings;
		}

		$query = $this->db->get('shortee_settings');
		$this->settings = $query->row_array();
		$this->domain_list = explode(',',$this->settings['short_domain']);

		return $this->settings;
	}

	// -----------------------------------------------------------------

	/**
	 *
	 * @return <type>
	 */
	public function get_short_domain() {

		return $this->settings['short_domain'];
	}

	// -----------------------------------------------------------------

	/**
	 * Update settings for shortee
	 */
	public function update_settings($data = array()) {

		if(isset($data['short_domain'])) {
			$this->settings['short_domain'] = trim($data['short_domain'],',/		');
		}

		if(isset($data['last_number'])) {
			$this->settings['last_number'] = $data['last_number'];
		}

		$this->db->update('shortee_settings',$this->settings);

	}

	// -----------------------------------------------------------------

	/**
	 * Get the last url number
	 *
	 * @return int
	 */
	private function get_last_number() {
		return $this->settings['last_number'];
	}

	// -----------------------------------------------------------------

	/**
	 * Increase the last url number
	 */
	private function increase_last_number() {
		$this->settings['last_number'] ++;
		$this->update_settings();
	}

	// -----------------------------------------------------------------

	/**
	 * Check a code exists
	 *
	 * @param string $code
	 * @return boolean
	 */
	private function code_exists($code) {
		$code = $this->db->escape_str($code);
		$query = $this->db->query("SELECT COUNT(id) as num_rows FROM exp_shortee_urls WHERE BINARY code = '$code'");

		$row = $query->row_array();

		return ($row['num_rows'] > 0) ? true : false;
	}

	// -----------------------------------------------------------------

	/**
	 * Generate a new code
	 *
	 * @param int $number
	 * @return string
	 */
	public function generate_code($number) {
		$out   = "";
		$codes = "abcdefghjkmnpqrstuvwxyz23456789ABCDEFGHJKMNPQRSTUVWXYZ";

		while ($number > 53) {
			$key    = $number % 54;
			$number = floor($number / 54) - 1;
			$out    = $codes{$key}.$out;
		}

		return $codes{$number}.$out;

	}

	// -----------------------------------------------------------------

	/**
	 * Generate a new, unique URL
	 *
	 * @return string
	 */
	public function new_code() {

		do {
			$code = $this->generate_code($this->get_last_number());
			$this->increase_last_number();

			if ($this->code_exists($code)) {
				continue;
			}

			break;
		} while (1);

		return $code;
	}

	// -----------------------------------------------------------------

	/**
	 * Inset a new URL
	 *
	 * @param string $domain
	 * @param string $url
	 * @param string $code
	 * @return int
	 */
	public function insert_url($domain, $url, $code) {

		$url = $this->db->escape_str($url);
		$code = $this->db->escape_str($code);

		$this->db->query("INSERT INTO exp_shortee_urls (domain, url, code, date_added) VALUES ('$domain','$url', '$code', NOW())");

		return $this->db->insert_id();

	}

	// -----------------------------------------------------------------

	/**
	 *
	 * @param int $id
	 * @param string $code
	 */
	public function update_url($id,$code) {

		$uData = array(
			'code' => $code
		);

		$this->db->where('id',$id);
		$this->db->update('shortee_urls',$uData);
	}

	// -----------------------------------------------------------------

	/**
	 * Get full details from domain/code combo
	 *
	 * @param string $code
	 * @return mixed
	 */
	public function get_url($domain,$code) {

		$where = array(
			'domain' => $domain,
			'code' => $code
		);
		$query = $this->db->where($where)->get('shortee_urls');

		if(!$query->num_rows) {
			return false;
		}

		return $query->row_array();

	}

	// -----------------------------------------------------------------

	/**
	 * Check a custom code is OK
	 *
	 * @param string $domain
	 * @param string $code
	 * @return boolean
	 */
	public function validate_code($domain,$code) {

		if(!in_array($domain,$this->domain_list)) {
			$this->error = $this->lang->line('dm_invalid_domain');
			return false;
		}

		if (!preg_match("/^[a-zA-Z0-9_-]+$/", $code)) {
			$this->error = $this->lang->line('dm_alphadash');
			return false;
		}

		if($this->code_exists($code)) {
			$this->error = $this->lang->line('dm_inuse');
			return false;
		}

		return true;

	}

	// -----------------------------------------------------------------

	/**
	 * Log a view to the db
	 *
	 * @param int $url_id
	 * @param string $ip
	 * @return mixed
	 */
	public function log_view($url_id,$ip) {

		if(!(int) $url_id) {
			return;
		}

		// get country code
		$this->db->select('country');
		$this->db->from('ip2nation');
		$this->db->where("ip < INET_ATON('".trim($ip)."')", '', FALSE);
		$this->db->order_by('ip', 'desc');
		$this->db->limit(1, 0);
		$query = $this->db->get();

		if (!$query->num_rows() ) {
			$cc = '-1';
		} else {
			$row = $query->row_array();
			$cc = $row['country'];
		}

		// insert data
		$iData = array(
			'date_viewed' => date('Y-m-d H:i:s'),
			'url_id' => $url_id,
			'ip_address' => $ip,
			'country_code' => $cc
		);

		$this->db->insert('shortee_views',$iData);

		// increment view count
		$this->db->query('UPDATE exp_shortee_urls SET views = views+1 WHERE id = '. (int) $url_id );
	}

	// -----------------------------------------------------------------

	/**
	 * Get summary of all URLs
	 *
	 * @todo paginate
	 *
	 * @param string $order
	 * @return array
	 */
	public function url_listing($data) {

		$select = 'SELECT * FROM exp_shortee_urls ';
		$where = '';

		if(!empty($data['keywords'])) {
			$keywords = $this->db->escape_str($data['keywords']);
			$where .= " `code` LIKE '%$keywords%'";
		}

		if(!empty($data['date_limit'])) {
			if($where != '' ) $where .=  ' AND ';
			$where.= " `date_added` >= '".$data['date_limit']."'";
		}

		if($where != '') {
			$where = " WHERE $where ";
		}

		// first we count
		$countRS = $this->db->query("SELECT COUNT(*) AS count FROM exp_shortee_urls $where");
		$count = $countRS->row();

		$out = array(
			'total_count' => $count->count
		);

		if($count->count < 1 ) {
			return $out;
		}

		// now we get records
		$sql = "$select $where ORDER BY ".$data['sort'].' '. $data['order'];
		$sql .= ' LIMIT '.$data['offset'].', '.$data['perpage'];

		//echo $sql;

		$out['entries'] = $this->db->query($sql);

		return $out;

	}

	// -----------------------------------------------------------------

	/**
	 * Count total views for a URL
	 *
	 * @return int
	 */
	public function view_count($id) {

		$this->db->select('COUNT(url_id) as total');
		$this->db->where('url_id',$id);
		$query = $this->db->get('shortee_views');

		$result = $query->row_array();

		return $result['total'];
	}

	// -----------------------------------------------------------------

	/**
	 *  Get basic stats for a single record
	 */
	public function get_one($id) {

		// get basic data
		$this->db->where('id',$id);
		$query = $this->db->get('shortee_urls');

		if($query->num_rows() < 1 ) {
			return false;
		}

		$data = $query->row_array();
		$data['short_url'] = $data['domain'].'/'.$data['code'];

		return $data;

	}

	// -----------------------------------------------------------------

	/**
	 * Get traffic counts for a record by date
	 *
	 * @param int $id
	 * @param string $before
	 * @return array
	 */
	public function get_traffic_by_date($id,$before = '') {

		$sql = "SELECT COUNT(*) AS views,
					DATE_FORMAT(date_viewed,'%Y-%m-%d') as date
				FROM exp_shortee_views
				WHERE url_id=".(int) $id;

		if($before != '' ) {
			$sql .= " AND date_viewed >= '$before' ";
		}

		$sql .=" GROUP BY date
				ORDER BY date DESC";

		$query = $this->db->query($sql);

		return $query->result_array();
	}

	// -----------------------------------------------------------------

	/**
	 * Get traffic counts for a record by country
	 *
	 * @param int $id
	 * @return array
	 */
	public function get_traffic_by_country($id,$before = '') {

		$sql = "SELECT COUNT(*) AS views,
					DATE_FORMAT(date_viewed,'%Y-%m-%d') as date,
					country_code
				FROM exp_shortee_views
				WHERE url_id=".(int) $id;
		if($before != '' ) {
			$sql .= " AND date_viewed >= '$before' ";
		}

		$sql .= ' GROUP BY country_code
					ORDER BY country_code ASC';

		$query = $this->db->query($sql);

		return $query->result_array();
	}

	// -----------------------------------------------------------------

	/**
	 * Delete a record from the db
	 *
	 * @param int $id
	 */
	public function delete_url($id) {

		$this->db->where('id',$id);
		$this->db->delete('shortee_urls');

		$this->db->where('url_id',$id);
		$this->db->delete('shortee_views');

	}

	// -----------------------------------------------------------------

	/**
	 * domain_list
	 *
	 * getter for domain_list
	 *
	 * @return array
	 */
	public function domain_list() {

		return $this->domain_list;
	}

	// -----------------------------------------------------------------

	public function raw_view_data($id,$before) {

		$sql = "SELECT  CONCAT(u.domain,'/',u.code) AS full_url, v.*
				FROM exp_shortee_views AS v
				LEFT JOIN exp_shortee_urls AS u ON v.url_id = u.id
				WHERE u.id = " . (int) $id;

		if($before) {
			$sql .= " AND date_viewed >= '$before' ";
		}

		$query = $this->db->query($sql);

		return $query->result_array();

	}

}