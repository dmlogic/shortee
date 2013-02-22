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
class Dm_shortee_mcp {

	private $ajax;

	private $perpage = 50; // default links per page

	// -----------------------------------------------------------------

	/**
	 * Constructor
	 */
	public function Dm_shortee_mcp(){

		$this->EE =& get_instance();

		// is this an ajax request?
		$this->ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');

		// required resources
		$this->EE->load->model('shortee');
		$this->EE->load->helper('form');

		// common link to this modue
		$this->base_url = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=dm_shortee';

		// sub navigation
		$this->EE->cp->set_right_nav(
							array(
								'dm_new_url' => BASE.AMP.$this->base_url,
								'dm_listing' => BASE.AMP.$this->base_url.AMP.'method=listing',
								'dm_settings' => BASE.AMP.$this->base_url.AMP.'method=settings',
								)
							);

		// add our stylesheet
		$this->EE->cp->add_to_head(
			'<link rel="stylesheet" type="text/css" href="'.$this->EE->config->item('theme_folder_url').'third_party/dm_shortee/styles.css" />'
		);
	}

	// -----------------------------------------------------------------

	/**
	 * New URL form
	 *
	 * @return string
	 */
	public function index() {

		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('dm_new_url'));

		$data = array(
			'action_url' => $this->base_url.'&method=submit_form',
			'pages_urls' => $this->lookup_pages(),
			'long_url' => site_url(),
			'template_groups' => $this->lookup_template_groups(),
			'domains' => $this->EE->shortee->domain_list(),
			'qrlink' => BASE.AMP.$this->base_url.AMP.'method=qrcode'
		);

		$this->add_fancybox();

		$this->EE->cp->add_to_head(
			$this->EE->javascript->inline( 'var shortee_base_url = "'.trim(site_url(),'/').'";' ).
			$this->EE->javascript->inline( $this->templates_js() )
		);

		$this->EE->cp->add_to_foot(
			'<script type="text/javascript" src="'.$this->EE->config->item('theme_folder_url').'third_party/dm_shortee/shortee_create.js"></script>'
		);


		return $this->EE->load->view('new_url',$data,true);

	}

	// -----------------------------------------------------------------

	/**
	 * Lookup Pages URLs for this site
	 *
	 * @return array
	 */
	private function lookup_pages() {

		$pages = $this->EE->config->item('site_pages');
		$pages = $pages[$this->EE->session->userdata['site_id']]['uris'];

		if(!is_array($pages)) {
			return array();
		}

		sort($pages);
		return $pages;

	}

	// -----------------------------------------------------------------

	/**
	 * Lookup template groups for this site
	 *
	 * @return array
	 */
	private function lookup_template_groups() {

		$out = array();

		$this->EE->db->select('group_id,group_name,is_site_default');
		$this->EE->db->where('site_id',$this->EE->session->userdata['site_id']);
		$this->EE->db->order_by('group_order');
		$query = $this->EE->db->get('template_groups');

		foreach($query->result() as $group) {
			$out[$group->group_id] = array($group->group_name,$group->is_site_default);
		}

		return $out;
	}

	// -----------------------------------------------------------------

	/**
	 * Lookup templates for this site
	 *
	 * @return array
	 */
	private function lookup_templates() {

		$this->EE->db->select('group_id,template_name');
		$this->EE->db->order_by('group_id');
		$query = $this->EE->db->get('templates');

		foreach($query->result() as $tmpl) {
			$out[$tmpl->group_id][] = $tmpl->template_name;
		}

		return $out;
	}

	// -----------------------------------------------------------------

	/**
	 * Format all templates as a js object
	 *
	 * @return string
	 */
	private function templates_js() {

		$templates = $this->lookup_templates();

		$out = 'var shortee_templates = [];';
		$out .= 'var init_group_text = \'<option value="0">- '.$this->EE->lang->line('dm_select_template').' -</option>\';';

		foreach($templates as $group => $templates) {

			$out .= 'shortee_templates['.$group.'] = ["';
			$out .= implode('","',$templates);
			$out .= '"];';
		}

		return $out;
	}

	// -----------------------------------------------------------------

	/**
	 * Shortee settings form
	 *
	 * @return string
	 */
	public function settings() {

		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('dm_settings'));
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'
			.AMP.'module=dm_shortee', $this->EE->lang->line('dm_shortee'));

		$data = $this->EE->shortee->get_settings();
		$data['action_url']	= $this->base_url.AMP.'method=update_settings';
		$data['action_id'] = $this->EE->functions->insert_action_ids($this->EE->functions->fetch_action_id('Dm_shortee', 'redirect'));

		return $this->EE->load->view('settings',$data,true);

	}

	// -----------------------------------------------------------------

	/**
	 * Update settings
	 */
	public function update_settings() {

		$this->EE->shortee->update_settings($_POST);

		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('dm_updated'));

		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=dm_shortee'.AMP.'method=settings');

	}

	// -----------------------------------------------------------------

	/**
	 * Listing of saved URLs
	 *
	 * @return string
	 */
	public function listing() {

		// required libs
		$this->EE->load->library('pagination');
		$this->EE->load->library('table');
		$this->EE->lang->load('content');
		$this->add_fancybox();


		// values for form elements
		$data = array(
			'filter_form_path' => 'C=addons_modules&M=show_module_cp&module=dm_shortee&method=listing',
		);

		$data['date_select_options'] = $this->date_select_options();

		//$data['perpage_select_options']['2'] = '2 '.$this->EE->lang->line('results'); // good for debug
		$data['perpage_select_options']['10'] = '10 '.$this->EE->lang->line('results');
		$data['perpage_select_options']['25'] = '25 '.$this->EE->lang->line('results');
		$data['perpage_select_options']['50'] = '50 '.$this->EE->lang->line('results');
		$data['perpage_select_options']['75'] = '75 '.$this->EE->lang->line('results');
		$data['perpage_select_options']['100'] = '100 '.$this->EE->lang->line('results');
		$data['perpage_select_options']['150'] = '150 '.$this->EE->lang->line('results');

		// start building page vars
		$data['table_headings'] = array(
			array('data' => 'Short URL', 'title' => 'code'),
			array('data' => 'Long URL', 'title' => 'url'),
			array('data' => 'QR code', 'title' => 'qrcode'),
			array('data' => 'Date', 'title' => 'date_added', 'class' => 'headerSortDown'),
			array('data' => 'Views', 'title' => 'views'),
			'&nbsp;'
		);

		$data['keywords'] = $this->EE->input->get('keywords',true);

		// start bulding query vars
		$filter_data = array();

		$filter_data['keywords'] = $data['keywords'];

		if((int) $d = $this->EE->input->get('date_range')) {
			$filter_data['date_limit'] = date('Y-m-d 00:00:00',strtotime("- $d days"));
		}

		if($w = $this->EE->input->get('keywords',true)) {
			$filter_data['keywords'] = $w;
		}

		$filter_data['offset'] = ( (int) $this->EE->input->get('offset') ) ? $this->EE->input->get('offset') : 0;
		$filter_data['perpage'] = ( (int) $this->EE->input->get('perpage') ) ? $this->EE->input->get('perpage') : $this->perpage;
		$data['perpage'] = $filter_data['perpage'];

		$allowed_sort = array('code','url','date_added','views');

		$filter_data['sort'] = ( $this->EE->input->get('sort') && in_array($this->EE->input->get('sort'), $allowed_sort) ) ? $this->EE->input->get('sort') : 'date_added';
		$filter_data['order'] = ( $this->EE->input->get('order') == 'asc' ) ? 'ASC' : 'DESC';

		// run the query
		$result = $this->EE->shortee->url_listing($filter_data);
		$data['entries'] = array();

		// format any results
		if($result['total_count']) {
			foreach($result['entries']->result_array() as $url) {

				$count = $url['views'];
				$stats = ($count) ? $count. ' -  <a href="'.BASE.AMP.$this->base_url.AMP.'method=stats&url_id='.$url['id'].'">'.$this->EE->lang->line('dm_view_stats').'</a>' : '0';

				$qrlink = BASE.AMP.$this->base_url.AMP.'method=qrcode&id='.$url['id'];

				$data['entries'][] = array(
					'<a class="shortee-link" href="'.$url['domain'].'/'.$url['code'].'">'.$url['domain'].'/'.$url['code'].'</a>',
					'<a class="shortee-link" href="'.$url['url'].'">'.$url['url'].'</a>',
					'<a class="dm_fancy" href="'.$qrlink.'">View</a> | <a class="fancybox" href="'.$qrlink.'&download=true">Download</a>',
					date('Y-m-d H:i',strtotime($url['date_added'])),
					$stats,
					'<a href="'.BASE.AMP.$this->base_url.AMP.'method=delete_url&url_id='.$url['id'].'" class="shortee-delete">'.$this->EE->lang->line('dm_delete').'</a>'
				);
			}
		}

		// get the theme url
		$cp_theme	= ( ! $this->EE->session->userdata('cp_theme')) ? $this->EE->config->item('cp_theme') : $this->EE->session->userdata('cp_theme');
		$cp_theme_url = $this->EE->config->slash_item('theme_folder_url').'cp_themes/'.$cp_theme.'/';

		// pagination
		$this->EE->load->library('pagination');
		$pg['base_url'] = '#';
		$pg['total_rows'] = $result['total_count'];
		$pg['per_page'] = $filter_data['perpage'];
		$pg['page_query_string'] = TRUE;
		$pg['query_string_segment'] = 'offset';
		$pg['full_tag_open'] = '<p id="paginationLinks">';
		$pg['full_tag_close'] = '</p>';
		$pg['prev_link'] = '<img src="'.$cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
		$pg['next_link'] = '<img src="'.$cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
		$pg['first_link'] = '<img src="'.$cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
		$pg['last_link'] = '<img src="'.$cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';

		$this->EE->pagination->initialize($pg);

		$data['pagination'] = $this->EE->pagination->create_links();

		// if in ajax, spit out a simplified json object
		if($this->ajax) {
			$this->ajax_listing($data);
		}

		// still here? Render the full CP page
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('dm_listing'));
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'
			.AMP.'module=dm_shortee', $this->EE->lang->line('dm_shortee'));
		$this->EE->cp->add_to_foot(
			'<script type="text/javascript" src="'.$this->EE->config->item('theme_folder_url').'third_party/dm_shortee/shortee_manage.js"></script>'
		);

		return $this->EE->load->view('url_listing',$data,true);
	}

	// -----------------------------------------------------------------

	/**
	 * Output table contents and pagination strings as a full json object
	 *
	 * @param array $data
	 */
	private function ajax_listing($data) {

		$out = '';
		for($i=0 ; $i < count($data['entries']) ; $i++ ) {

			$class = ($i%2) ? 'even' : 'odd';
			$out .= '<tr class="'.$class.'"><td>';
			$out .= implode('</td><td>',$data['entries'][$i]);
			$out .= '</td></tr>';
		}

		if(!$data['entries']) {
			$out = '<td colspan="5">'.$this->EE->lang->line('dm_no_results').'</td>';
		}

		$this->EE->load->library('javascript');
		echo $this->EE->javascript->generate_json(array('table' => $out,'pagination' => $data['pagination']));
		exit;
	}

	// -----------------------------------------------------------------

	/**
	 * Generate a new code from AJAX request
	 */
	private function generate_code($domain,$url) {

		$exists = $this->EE->shortee->get_url($domain,$url);
		if($exists) {

			$this->json_response('duplicate', $this->EE->lang->line('dm_duplicate'),$exists['domain'].'/'.$exists['code'],$exists['id']);
		}

		$this->json_response('success', $this->EE->shortee->new_code($url));

	}

	// -----------------------------------------------------------------

	/**
	 * New URL form submission controller
	 *
	 * @return string
	 */
	public function submit_form() {

		// we only want a code
		if($this->EE->input->get('generate') == 1) {
			$this->generate_code($this->EE->input->get('domain'),$this->EE->input->get('url'));
		}

		$allowed_domains = $this->EE->shortee->domain_list();

		$url = $this->EE->input->post('long_url',true);
		$domain = $this->EE->input->post('short_domain',true);
		$code = $this->EE->input->post('short_url',true);

		// bad data
		if(!$url || !$code || !$domain) {
			$this->json_response('error',$this->EE->lang->line('dm_long_short'));
		}

		// invalid code
		if(!$this->EE->shortee->validate_code($domain,$code)) {
			$this->json_response('error',$this->EE->shortee->error);
		}

		// insert new record
		$id = $this->EE->shortee->insert_url($domain,$url,$code);

		$this->json_response('success',$this->EE->lang->line('dm_available'),$domain.'/'.$code,$id);

	}

	// -----------------------------------------------------------------

	/**
	 * delete_url
	 *
	 * Delete a record and all stats
	 */
	public function delete_url() {

		if(!$this->EE->input->get('url_id')) {
			exit(' ');
		}

		$this->EE->shortee->delete_url($this->EE->input->get('url_id'));
		exit('success');

	}

	// -----------------------------------------------------------------

	/**
	 * Display stats for a URL
	 *
	 * @return string
	 */
	public function stats() {

		// confirm valid record
		$id = $this->EE->input->get('url_id');
		$data = $this->EE->shortee->get_one($id);
		if(!$data) {
			$this->EE->output->fatal_error($this->EE->lang->line('dm_no_stats'));
		}


		// filter the dates based on user input
		$before_date = '';
		$range = (int) $this->EE->input->get_post('date_range');
		if( $range ) {
			$before_date = date('Y-m-d 00:00:00',strtotime("- $range days"));
		}

		// must be download stats
		if(!empty($_POST)) {
			$raw_data = $this->EE->shortee->raw_view_data($id,$before_date);

			$this->download_stats($raw_data,$this->make_filename($data['domain'],$data['code']));
		}
		// make the traffic table
		$t['rows'] = $this->EE->shortee->get_traffic_by_date($id,$before_date);
		$data['traffic_table'] = $this->EE->load->view('traffic_table',$t,true);

		// make the country table
		include_once(APPPATH.'config/countries.php');
		$data['countries'] = $countries;
		$data['countries'][-1] = $this->EE->lang->line('dm_unknown');
		$data['rows'] = $this->EE->shortee->get_traffic_by_country($id,$before_date);
		$data['country_table'] = $this->EE->load->view('country_table',$data,true);
		$data['qrlink'] = BASE.AMP.$this->base_url.AMP.'method=qrcode&id='.$id;

		// we only want those two tables if ajax request
		if($this->ajax) {

			$this->EE->load->library('javascript');
			echo $this->EE->javascript->generate_json(array('traffic_table' => $data['traffic_table'],'country_table' => $data['country_table']));
			exit;
		}

		// some other template vars
		$data['action_url'] = $this->base_url.'&method=stats&url_id='.$id;
		$data['traffic_select'] = form_dropdown('traffic_select', $this->date_select_options(),0,'id="traffic_select"');

		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('dm_statistics'));
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'
			.AMP.'module=dm_shortee', $this->EE->lang->line('dm_shortee'));
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'
			.AMP.'module=dm_shortee'.AMP.'method=listing', $this->EE->lang->line('dm_listing'));


		$this->add_fancybox();

		// render full page
		return $this->EE->load->view('stats',$data,true);

	}

	// -----------------------------------------------------------------

	/**
	 * download_stats
	 *
	 * force download of stats CSV file
	 *
	 * @param array $data
	 * @param string $filename
	 */
	private function download_stats($data,$filename) {

		$out = '';
		foreach($data as $row) {

			if($out == '') {
				$keys = array_keys($row);
				$out .= implode(',',$keys);
			}

			$out .= "\n\"".implode('","',$row).'"';

		}
		$this->EE->load->helper('download');
		force_download($filename.'.csv',$out);

	}

	// -----------------------------------------------------------------

	/**
	 * qrcode
	 *
	 * Generate and present/download a QR code
	 *
	 * @return mixed
	 */
	public function qrcode() {

		$id = $this->EE->input->get('id');

		if(empty($id)) {
			return $this->listing;
		}

		$code = $this->EE->shortee->get_one($id);

		if(!$code) {
			return $this->listing;
		}

		require_once PATH_THIRD.'dm_shortee/phpqrcode/qrlib.php';

		// if we want to download, do so with a sensible filename
		if($this->EE->input->get('download')) {
			$filename = $this->make_filename($code['domain'],$code['code']);
			header('Content-Disposition: attachment; filename="'.$filename.'.png"');
		}

		QRcode::png($code['short_url'],false,QR_ECLEVEL_L,10);
		exit();
	}

	// -----------------------------------------------------------------

	/**
	 * Spit out very quick & dirty JSON object and quit
	 *
	 * @param string $result
	 * @param string> $message
	 */
	private function json_response($result,$message,$url='',$id='') {

		echo '{"result":"'.$result.'","message":"'.$message.'","url":"'.$url.'","id":"'.$id.'"}';
		exit;
	}

	// -----------------------------------------------------------------

	/**
	 * date_select_options
	 *
	 * Build options for the date select list
	 *
	 * @return array
	 */
	private function date_select_options() {

		$this->EE->lang->load('content');

		return array(
			'' => 'All dates',
			1 => $this->EE->lang->line('past_day'),
			7 => $this->EE->lang->line('past_week'),
			31 => $this->EE->lang->line('past_month'),
			182 => $this->EE->lang->line('past_six_months'),
			365 => $this->EE->lang->line('past_year')
		);

	}

	// -----------------------------------------------------------------

	/**
	 * add_fancybox
	 *
	 * Add all the fancybox resources to the CP
	 */
	private function add_fancybox() {
		$this->EE->cp->add_js_script(array('plugin' => 'fancybox'));
		$this->EE->cp->add_to_head( '<link type="text/css" rel="stylesheet" href="'.BASE.AMP.'C=css'.AMP.'M=fancybox" />' );
		$this->EE->cp->add_to_foot(
			'<script type="text/javascript" src="'.$this->EE->config->item('theme_folder_url').'third_party/dm_shortee/shortee_list.js"></script>'
		);
	}

	// -----------------------------------------------------------------

	/**
	 * make_filename
	 *
	 * Make a safe filename from domain and code
	 *
	 * @param string $domain
	 * @param string $code
	 * @return string
	 */
	private function make_filename($domain,$code) {
		$s = array('http://','https://');
		$r = array('','');
		$domain = str_replace($s,$r,$domain);
		return $domain.'-'.$code;
	}

}