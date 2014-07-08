<?php

/**
 * moziloCMS Plugin: pdfExport
 *
 * Generates a pdf export link, to stream the current content page into an PDF file
 * with the help of the pdfmyurl.com webservice.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   HPdesigner <mail@devmount.de>
 * @license  GPL v3+
 * @version  GIT: v0.1.2013-10-13
 * @link     https://github.com/devmount/pdfExport
 * @link     http://devmount.de/Develop/moziloCMS/Plugins/pdfExport.html
 * @see      Give thanks to the LORD, for he is good; his love endures forever.
 *           – The Bible
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

// only allow moziloCMS environment
if (!defined('IS_CMS')) {
    die();
}

/**
 * pdfExport Class
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   HPdesigner <mail@devmount.de>
 * @license  GPL v3+
 * @link     https://github.com/devmount/pdfExport
 */
class pdfExport extends Plugin
{
    // language
    private $_admin_lang;
    private $_cms_lang;

    // plugin information
    const PLUGIN_AUTHOR  = 'HPdesigner';
    const PLUGIN_DOCU
        = 'http://devmount.de/Develop/moziloCMS/Plugins/pdfExport.html';
    const PLUGIN_TITLE   = 'pdfExport';
    const PLUGIN_VERSION = 'v0.1.2013-10-13';
    const MOZILO_VERSION = '2.0';
    private $_plugin_tags = array(
        'tag1' => '{pdfExport}',
    );

    const LOGO_URL = 'http://media.devmount.de/logo_pluginconf.png';

    /**
     * set configuration elements, their default values and their configuration
     * parameters
     *
     * @var array $_confdefault
     *      text     => default, type, maxlength, size, regex
     *      textarea => default, type, cols, rows, regex
     *      password => default, type, maxlength, size, regex, saveasmd5
     *      check    => default, type
     *      radio    => default, type, descriptions
     *      select   => default, type, descriptions, multiselect
     */
    private $_confdefault = array(
        'linktext' => array(
            'PDF Export',
            'text',
            '',
            '',
            '',
        ),
        'orientation' => array(
            'Portrait',
            'select',
            array('portrait','landscape'),
            false,
        ),
    );

    /**
     * creates plugin content
     *
     * @param string $value Parameter divided by '|'
     *
     * @return string HTML output
     */
    function getContent($value)
    {
        global $CMS_CONF;
        global $syntax;
        global $specialchars;
        global $CatPage;

        $this->_cms_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/cms_language_'
            . $CMS_CONF->get('cmslanguage')
            . '.txt'
        );

        // get conf and set default
        $conf = array();
        foreach ($this->_confdefault as $elem => $default) {
            $conf[$elem] = ($this->settings->get($elem) == '')
                ? $default[0]
                : $this->settings->get($elem);
        }

        $export = getRequestValue('pdfexport', 'get', false);
        $action = getRequestValue('action', 'get', false);
        $search = getRequestValue('search', 'get', false);

        // build print template
        if ($export != '') {
            // load template
            $template_file = $this->PLUGIN_SELF_DIR . 'template.html';
            if (!$file = @fopen($template_file, 'r')) {
                return $this->throwError(
                    $this->cms_lang->getLanguageValue(
                        'message_template_error',
                        $template_file
                    )
                );
            }
            $template = fread($file, filesize($template_file));
            fclose($file);

            // fill placeholder {CONTENT} with current page content
            $content = $syntax->content;
            preg_match("/---content~~~(.*)~~~content---/Umsi", $content, $match);
            $content = $match[0];
            $content = str_replace('{CONTENT}', $content, $template);

            // $mysyntax = new Syntax;
            // $content = $syntax->convertContent($content, true);

            $syntax->content = $content;


            // convert in PDF
            // require_once $this->PLUGIN_SELF_DIR . 'html2pdf/html2pdf.class.php';
            // try
            // {
            //     $html2pdf = new HTML2PDF('P', 'A4', 'fr');
            //     $html2pdf->setModeDebug();
            //     $html2pdf->setDefaultFont('Arial');
            //     $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
            //     $html2pdf->Output('exemple00.pdf');
            // }
            // catch(HTML2PDF_exception $e) {
            //     echo $e;
            //     exit;
            // }

            return;

        } else { // build link
            // add get params
            $add_url_param = '';
            if ($action != '') {
                $add_url_param = '&action=' . $action;
                if ($search != '') {
                    $add_url_param .= '&search=' . $search;
                }
            }

            // return link
            $link_text
                = $specialchars->rebuildSpecialChars($conf['linktext'], true, true);
            $link = '<a ';
            $link .= 'href="javascript: pdf_url=location.href;';
            $link .= 'location.href=\'http://pdfmyurl.com?url=\'';
            $link .= '+escape(pdf_url+\'?pdfexport=true' . $add_url_param . '\')';
            $link .= '+\'&orientation=' . $conf['orientation'] . '\'" ';
            $link .= 'class="pdfexport" ';
            $link .= 'target="_blank" ';
            $link .= 'title="Seite als PDF exportieren" ';
            $link .= '>' . $link_text . '</a>';
            // $link = '
            // <a
            //     class="pdfexport"
            //     target="_blank"
            //     title="Seite als PDF exportieren"
            //     href="?pdfexport=true"
            // >' . $link_text . '</a>';

            return $link;
        }

    }

    /**
     * sets backend configuration elements and template
     *
     * @return Array configuration
     */
    function getConfig()
    {
        $config = array();

        // read configuration values
        foreach ($this->_confdefault as $key => $value) {
            // handle each form type
            switch ($value[1]) {
            case 'text':
                $config[$key] = $this->confText(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'textarea':
                $config[$key] = $this->confTextarea(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'password':
                $config[$key] = $this->confPassword(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    ),
                    $value[5]
                );
                break;

            case 'check':
                $config[$key] = $this->confCheck(
                    $this->_admin_lang->getLanguageValue('config_' . $key)
                );
                break;

            case 'radio':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confRadio(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions
                );
                break;

            case 'select':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confSelect(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions,
                    $value[3]
                );
                break;

            default:
                break;
            }
        }

        // read admin.css
        $admin_css = '';
        $lines = file('../plugins/' . self::PLUGIN_TITLE. '/admin.css');
        foreach ($lines as $line_num => $line) {
            $admin_css .= trim($line);
        }

        // add template CSS
        $template = '<style>' . $admin_css . '</style>';

        // build Template
        $template .= '
            <div class="pdfexport-admin-header">
            <span>'
                . $this->_admin_lang->getLanguageValue(
                    'admin_header',
                    self::PLUGIN_TITLE
                )
            . '</span>
            <a href="' . self::PLUGIN_DOCU . '" target="_blank">
            <img style="float:right;" src="' . self::LOGO_URL . '" />
            </a>
            </div>
        </li>
        <li class="mo-in-ul-li ui-widget-content pdfexport-admin-li">
            <div class="pdfexport-admin-subheader">'
            . $this->_admin_lang->getLanguageValue('admin_link')
            . '</div>
            <div style="margin-bottom:5px;">
                <div class="pdfexport-single-conf">
                    {linktext_text}
                </div>
                {linktext_description}
                <span class="pdfexport-admin-default">
                    [' . $this->_confdefault['linktext'][0] .']
                </span>
            </div>
        </li>
        <li class="mo-in-ul-li ui-widget-content pdfexport-admin-li">
            <div class="pdfexport-admin-subheader">'
            . $this->_admin_lang->getLanguageValue('admin_pdf')
            . '</div>
            <div style="margin-bottom:5px;">
                <div class="pdfexport-single-conf">
                    {orientation_select}
                </div>
                {orientation_description}
                <span class="pdfexport-admin-default">
                    [' . $this->_confdefault['orientation'][0] .']
                </span>
        ';

        $config['--template~~'] = $template;

        return $config;
    }

    /**
     * sets default backend configuration elements, if no plugin.conf.php is
     * created yet
     *
     * @return Array configuration
     */
    function getDefaultSettings()
    {
        $config = array('active' => 'true');
        foreach ($this->_confdefault as $elem => $default) {
            $config[$elem] = $default[0];
        }
        return $config;
    }

    /**
     * sets backend plugin information
     *
     * @return Array information
     */
    function getInfo()
    {
        global $ADMIN_CONF;

        $this->_admin_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/admin_language_'
            . $ADMIN_CONF->get('language')
            . '.txt'
        );

        // build plugin tags
        $tags = array();
        foreach ($this->_plugin_tags as $key => $tag) {
            $tags[$tag] = $this->_admin_lang->getLanguageValue('tag_' . $key);
        }

        $info = array(
            '<b>' . self::PLUGIN_TITLE . '</b> ' . self::PLUGIN_VERSION,
            self::MOZILO_VERSION,
            $this->_admin_lang->getLanguageValue(
                'description',
                htmlspecialchars($this->_plugin_tags['tag1'])
            ),
            self::PLUGIN_AUTHOR,
            self::PLUGIN_DOCU,
            $tags
        );

        return $info;
    }

    /**
     * creates configuration for text fields
     *
     * @param string $description Label
     * @param string $maxlength   Maximum number of characters
     * @param string $size        Size
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confText(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for select fields
     *
     * @param string  $description  Label
     * @param string  $descriptions Array Single item labels
     * @param boolean $multiple     Enable multiple item selection
     *
     * @return Array   Configuration
     */
    protected function confSelect($description, $descriptions, $multiple = false)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
            'multiple' => $multiple,
        );
    }

    /**
     * throws styled error message
     *
     * @param string $text Content of error message
     *
     * @return string HTML content
     */
    protected function throwError($text)
    {
        return '<div class="' . self::PLUGIN_TITLE . 'Error">'
            . '<div>' . $this->_cms_lang->getLanguageValue('error') . '</div>'
            . '<span>' . $text. '</span>'
            . '</div>';
    }

}

?>