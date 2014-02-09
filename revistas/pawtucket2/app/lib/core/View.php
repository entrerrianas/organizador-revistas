<?php
/** ---------------------------------------------------------------------
 * app/lib/core/View.php : 
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2007-2010 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * @package CollectiveAccess
 * @subpackage Core
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 *
 * ----------------------------------------------------------------------
 */
 
 /**
  *
  */
 
require_once(__CA_LIB_DIR__."/core/BaseObject.php");
 
class View extends BaseObject {
	# -------------------------------------------------------
	private $opa_view_paths;
	
	private $opa_view_vars;
	private $opo_request;
	private $opo_appconfig;
	
	private $ops_character_encoding;
	
	# -------------------------------------------------------
	public function __construct($po_request, $pm_path=null, $ps_character_encoding='UTF8') {
		parent::__construct();
		
		$this->opo_request = $po_request;
		$this->opa_view_paths = array();
		$this->opa_view_vars = array();
		
		$this->opo_appconfig = Configuration::load();
		
		$this->ops_character_encoding = $ps_character_encoding;
		
		if ($pm_path) {
			$this->setViewPath($pm_path);
		}
	}
	# -------------------------------------------------------
	public function __get($ps_key) {
		switch($ps_key) {
			case 'request':
				return $this->opo_request;
				break;
			case 'appconfig':
				return $this->opo_appconfig;
				break;
			default:
				return $this->{$ps_key};
				break;
		}
	}
	# -------------------------------------------------------
	public function setRequest(&$po_request) {
		$this->opo_request = $po_request;
	}
	# -------------------------------------------------------
	public function setViewPath($pm_path) {
		if (is_array($pm_path)) {
			$this->opa_view_paths = $pm_path;
		} else {
			$this->opa_view_paths = array($pm_path);
		}
	}
	# -------------------------------------------------------
	public function addViewPath($pm_path) {
		if (is_array($pm_path)) {
			foreach($pm_path as $vs_path) {
				$this->opa_view_paths[] = $ps_path;
			}
		} else {
			$this->opa_view_paths[] = $pm_path;
		}
	}
	# -------------------------------------------------------
	public function getViewPaths() {
		return $this->opa_view_paths;
	}
	# -------------------------------------------------------
	public function setVar($ps_key, $pm_value) {
		$this->opa_view_vars[$ps_key] = $pm_value;
	}
	# -------------------------------------------------------
	public function getVar($ps_key) {
		return isset($this->opa_view_vars[$ps_key]) ? $this->opa_view_vars[$ps_key] : null;
	}
	# -------------------------------------------------------
	public function getAllVars() {
		return $this->opa_view_vars;
	}
	# -------------------------------------------------------
	public function assignVars($pa_values) {
		foreach($pa_values as $vs_key => $vm_value) {
			$this->opa_view_vars[$vs_key] = $vm_value;
		}
	}
	# -------------------------------------------------------
	/**
	 * Checks if the specified view exists in any of the configured view paths
	 *
	 * @param string $ps_filename Filename of view
	 * @param boolean - return true if view exists, false if not
	 */ 
	public function viewExists($ps_filename) {
		foreach(array_reverse($this->opa_view_paths) as $vs_path) {
			if (file_exists($vs_path.'/'.$ps_filename)) {
				return true;
			}
		}
		return false;
	}
	# -------------------------------------------------------
	/**
	 *
	 */
	public function isCompiled($ps_filepath) {
		$vs_compiled_path = __CA_APP_DIR__."/tmp/caCompiledView".md5($ps_filepath);
		if (!file_exists($vs_compiled_path)) { return false; }
		
		// Check if template change date is newer than compiled
		$va_view_stat = @stat($ps_filepath);
		$va_compiled_stat = @stat($vs_compiled_path);
		if ($va_view_stat['mtime'] > $va_compiled_stat['mtime']) { return false; }
		
		return $vs_compiled_path;
	}
	# -------------------------------------------------------
	/**
	 *
	 */
	public function compile($ps_filepath) {
		if ($vs_compiled_path = $this->isCompiled($ps_filepath)) { 
			return json_decode(file_get_contents($vs_compiled_path));
		}
		$vs_buf = $this->_render($ps_filepath);
		
		$vs_compiled_path = __CA_APP_DIR__."/tmp/caCompiledView".md5($ps_filepath);
		preg_match_all("!\{\{\{([^\}]+)\}\}\}!", $vs_buf, $va_matches);
		
		$va_tags = $va_matches[1];
		if (!is_array($va_tags)) { $va_tags = array(); }
		file_put_contents($vs_compiled_path, json_encode($va_tags));
		return $va_tags;
	}
	# -------------------------------------------------------
	/**
	 *
	 */
	public function getTagList($ps_filename) {
		$vb_output = false;
		
		$vs_locale = $_SESSION['session_vars']['lang'];			// handling the current locale, for example fr_FR
		
		$va_tags = null;
		foreach(array_reverse($this->opa_view_paths) as $vs_path) {
			if (file_exists($vs_path.'/'.$ps_filename.".".$vs_locale)) {
				// if a l10ed view is at same path than normal but having the locale as last extension, display it (eg. splash_intro_text_html.php.fr_FR)
				$va_tags = $this->compile($vs_path.'/'.$ps_filename.".".$vs_locale);
				break;
			}
			elseif (file_exists($vs_path.'/'.$ps_filename)) {
				// if no l10ed version of the view, render the default one which has no locale as last extension (eg. splash_intro_text_html.php)
				$va_tags = $this->compile($vs_path.'/'.$ps_filename);
				break;
			}
		}
		
		return $va_tags;
	}
	# -------------------------------------------------------
	/**
	 *
	 */
	public function render($ps_filename, $pb_dont_do_var_replacement=false) {
		$vb_output = false;
		
		$vs_locale = $_SESSION['session_vars']['lang'];			// handling the current locale, for example fr_FR
		
		$vs_buf = null;
		foreach(array_reverse($this->opa_view_paths) as $vs_path) {
			if (file_exists($vs_path.'/'.$ps_filename.".".$vs_locale)) {
				// if a l10ed view is at same path than normal but having the locale as last extension, display it (eg. splash_intro_text_html.php.fr_FR)
				$vs_buf = $this->_render($vs_path.'/'.$ps_filename.".".$vs_locale);
				$vb_output = true;
				break;
			}
			elseif (file_exists($vs_path.'/'.$ps_filename)) {
				// if no l10ed version of the view, render the default one which has no locale as last extension (eg. splash_intro_text_html.php)
				$vs_buf = $this->_render($vs_path.'/'.$ps_filename);
				$vb_output = true;
				break;
			}
		}
		if (!$vb_output) {
			$this->postError(2400, _t("View %1 was not found", $ps_filename), "View->render()");
		}
		
		if (!$pb_dont_do_var_replacement) {
			$va_compile = $this->compile($vs_path.'/'.$ps_filename);
			$va_vars = $this->getAllVars();
			foreach($va_compile as $vs_var) {
				$vm_val = isset($va_vars[$vs_var]) ? $va_vars[$vs_var] : '';
				$vs_buf = str_replace('{{{'.$vs_var.'}}}', $vm_val, $vs_buf);
				
			}
		}
		
		return $vs_buf;
	}
	# -------------------------------------------------------
	/**
	 *
	 */
	private function _render($ps_filename) {
		ob_start();
		
		require($ps_filename);
		
		return ob_get_clean();
	}
	# -------------------------------------------------------
	# Character encodings
	# -------------------------------------------------------
	public function setEncoding($ps_character_encoding) {
		$this->ops_character_encoding = $ps_character_encoding;
	}
	# -------------------------------------------------------
	public function getEncoding($ps_character_encoding) {
		return $this->ops_character_encoding;
	}
	# -------------------------------------------------------
	# Utils
	# -------------------------------------------------------
	public function escape($ps_text) {
		return htmlspecialchars($ps_text, ENT_QUOTES, $this->ops_character_encoding, false);
	}
	# -------------------------------------------------------
}
?>