<?php if(!defined('IS_CMS')) die();

/**
 * Plugin:   pdfExport
 * @author:  HPdesigner (hpdesigner[at]web[dot]de)
 * @version: v0.0.2013-10-13
 * @license: GPL
 * @see:     Give thanks to the LORD, for he is good; his love endures forever.
 *           - The Bible
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
**/

class pdfExport extends Plugin {

	public $admin_lang;
	private $cms_lang;

	function getContent($value) {

		global $CMS_CONF;
		global $syntax;
		global $specialchars;
		global $CatPage;
		
		$this->cms_lang = new Language(PLUGIN_DIR_REL . 'pdfExport/sprachen/cms_language_' . $CMS_CONF->get('cmslanguage') . '.txt');
				
		// Druckansicht erzeugen
		if (isset($_GET['pdfexport'])) {

			// template.html laden
			$template_file = PLUGIN_DIR_REL . 'pdfExport/template.html';
			if (!$file = @fopen($template_file, 'r'))
				die($this->cms_lang->getLanguageValue('message_template_error', $template_file));
			$template = fread($file, filesize($template_file));
			fclose($file);
	
			// platzhalter {CONTENT} im template durch den aktuellen content ersetzen 
			$content = $syntax->content;
			preg_match("/---content~~~(.*)~~~content---/Umsi", $content, $match);
			$content = $match[0];
			$content = str_replace('{CONTENT}', $content, $template);
			$syntax->content = $content;

			return;

		} else {
		// Link für aktuelle Seite mit URL-Parameter für pdfExport ausgeben
			
			// zusätzliche url parameter bei sitemap und suche extra mitgeben
			$add_url_param = '';
			// TODO ACTION_REQUEST und QUERY_REQUEST setzen!
			if (isset($_GET['action'])) {
				$add_url_param = '&action=' . $_GET['action'];
				if (isset($_GET['search'])) $add_url_param .= '&search=' . $_GET['search'];
			}

			// get conf
			$conf = array(
				'orientation'	=> ($this->settings->get('orientation') != '') ? $this->settings->get('orientation') : 'Portrait'
			);

			// Link ausgeben
			$link_text = $specialchars->rebuildSpecialChars($this->settings->get('linktext'),true,true);
			$link = "<a href=\"javascript:pdf_url=location.href;location.href='http://pdfmyurl.com?url='+escape(pdf_url+'?pdfexport=true" . $add_url_param . "')+";
			$link .= "'&-O=" . $conf['orientation'] . "'";
			$link .= "\" class=\"pdfexport\" target=\"_blank\" title=\"Seite als PDF exportieren\">" . $link_text . "</a>";

			return $link;
		}

	} // function getContent
	
	
	function getConfig() {

		$config = array();
		
		// content of link tag
		$config['linktext']  = array(
			'type' => 'text',
			'description' => $this->admin_lang->getLanguageValue('config_linktext'),
			'maxlength' => '100',
			'size' => '45',
		);
		
		// orientation
		$config['orientation']  = array(
			'type' => 'select',
			'description' => $this->admin_lang->getLanguageValue('config_orientation'),
			'descriptions' => array(
				'Portrait' => 'Hochformat',
				'Landscape' => 'Querformat'
			),
			'multiple' => false
		);

		return $config;
		
	} // function getConfig    
	
	
	function getInfo() {

		global $ADMIN_CONF;

		$this->admin_lang = new Language(PLUGIN_DIR_REL . 'pdfExport/sprachen/admin_language_' . $ADMIN_CONF->get('language') . '.txt');
				
		$info = array(
			// Plugin-Name + Version
			'<b>pdfExport</b> v0.0.2013-10-13',
			// moziloCMS-Version
			'2.0',
			// Kurzbeschreibung nur <span> und <br /> sind erlaubt
			$this->admin_lang->getLanguageValue('description'), 
			// Name des Autors
			'HPdesigner',
			// Docu-URL
			'http://www.devmount.de/Develop/Mozilo%20Plugins/pdfExport.html',
			// Platzhalter für die Selectbox in der Editieransicht 
			// - ist das Array leer, erscheint das Plugin nicht in der Selectbox
			array(
				'{pdfExport}' => $this->admin_lang->getLanguageValue('placeholder'),
			)
		);
		// return plugin information
		return $info;
		
	} // function getInfo

}

?>