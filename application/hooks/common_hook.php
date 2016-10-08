<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
* 
*/
class Common
{
	private $ci_obj;
	public function __construct()
	{
		$this->ci_obj = &get_instance();
		$this->ci_obj->config->load('hook_config');
		if (!isset($this->ci_obj->view_override)) {
			$this->ci_obj->view_override = TRUE;
		}
	}

	public function auto_verify()
	{
		$directory  = substr($this->ci_obj->router->fetch_directory(), 0, -1);
		$controller = $this->ci_obj->router->fetch_class();
		$function   = $this->ci_obj->router->fetch_method();

		if (!in_array($controller, $this->ci_obj->config->item('not_auth_controller'))) {
			if (session_conf('user')) {
				// echo "string";
			} else {
				// echo "FALSE";
			}
		} else {
			// 免权限验证
		}
	}
	public function view_override()
	{
		if (@$this->ci_obj->view_override) {
			$html = $this->ci_obj->load->view('public/main',  null,  true);
		} else {
			$html = $this->ci_obj->output->get_output();
		}
		$this->ci_obj->output->_display($html);

	}
}

?>