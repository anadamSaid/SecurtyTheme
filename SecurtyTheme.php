<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class SecurtyTheme extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'SecurtyTheme';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'VenVaukt';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('SecurtyTheme');
        $this->description = $this->l('we use this module to secure our theme');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayHeader') ;
    }

    public function uninstall()
    {

        foreach ($this->getConfigFormValues() as $key) {
            Configuration::deleteByName($key);
        }
   
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    private function configFormFieldsValue()
    {
        $result = [];
        foreach ($this->getConfigFormValues() as $key) {
            $result[$key] = Configuration::get($key);
        }

        return $result;
    }

    private function protectContent()
    {
        if (true === (bool) Configuration::get('PRO_DISABLE_RIGHT_CLICK')) {
            $this->context->controller->addJS($this->_path . 'views/js/contextmenu.js');
            $this->context->controller->addJS($this->_path . 'views/js/contextmenu-img.js');
        } 

        if (true === (bool) Configuration::get('PRO_DISABLE_DRAG')) {
            $this->context->controller->addJS($this->_path . 'views/js/dragstart.js');
        }

        if (true === (bool) Configuration::get('PRO_DISABLE_VIEW_PAGE_SOURCE')) {
            $this->context->controller->addJS($this->_path . 'views/js/view-page-source.js');
            $this->context->controller->addJS($this->_path . 'views/js/print.js');
            $this->context->controller->addJS($this->_path . 'views/js/save.js');
            $this->context->controller->addJS($this->_path . 'views/js/devtools-detector.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/console.js') ;
        }

        if (true === (bool) Configuration::get('PRO_DISABLE_TEXT_SELECTION')) {
            $this->context->controller->addJS($this->_path . 'views/js/selectstart.js');
        }   
    }

    private function getConfigFormValues()
    {
        return [
            'PRO_DISABLE_RIGHT_CLICK',
            'PRO_DISABLE_DRAG',
            'PRO_DISABLE_TEXT_SELECTION',
            'PRO_DISABLE_VIEW_PAGE_SOURCE',
        ];        
    }

    
    protected function getConfigFormValues2()
    { 

            $Params = array('PRO_DISABLE_RIGHT_CLICK' => Tools::getValue('PRO_DISABLE_RIGHT_CLICK ', Configuration::get('PRO_DISABLE_RIGHT_CLICK')),

            'PRO_DISABLE_DRAG' => Tools::getValue('PRO_DISABLE_DRAG', Configuration::get('PRO_DISABLE_DRAG')),

            'PRO_DISABLE_TEXT_SELECTION' => Tools::getValue('PRO_DISABLE_TEXT_SELECTION', Configuration::get('PRO_DISABLE_TEXT_SELECTION')),

            'PRO_DISABLE_VIEW_PAGE_SOURCE' => Tools::getValue('PRO_DISABLE_VIEW_PAGE_SOURCE', Configuration::get('PRO_DISABLE_VIEW_PAGE_SOURCE')),

            ) ;

          
        return   $Params;
    }



    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitSecurtyThemeModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSecurtyThemeModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues2(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */

    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Protect Content'),
                    'icon' => 'icon-hand-o-up',
                ],
                'description' => $this->l('The module allows you to disable a list of mouse- and key-events. These settings make it harder for users that manually try to steal your content. These settings will affect the front office only.'),
                'input' => [
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable Image Drag Or Copy'),
                        'name' => 'PRO_DISABLE_DRAG',
                        'is_bool' => true,
                        'desc' => $this->l('Disable drag and drop mouse event.') . ' ' . $this->l('Input and Textarea fields are excluded from this rule.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable text Copy or selection'),
                        'name' => 'PRO_DISABLE_TEXT_SELECTION',
                        'is_bool' => true,
                        'desc' => $this->l('Disable text selection with the mouse and keyboard shortcut (Ctrl + a / ⌘ + a).') . ' ' . $this->l('Input and Textarea fields are excluded from this rule.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ], 
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable right-click'),
                        'desc' => $this->l('Disable right-click mouse event.') . ' ' . $this->l('Input and Textarea fields are excluded from this rule.'),
                        'name' => 'PRO_DISABLE_RIGHT_CLICK',
                        'is_bool' => true,                        
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ]
                    ],                                                                          
                    [
                        'type' => 'switch',
                        'col' => 8,
                        'label' => $this->l('Disable advanced developer tools '),
                        'name' => 'PRO_DISABLE_VIEW_PAGE_SOURCE',
                        'is_bool' => true,
                        'desc' => $this->l("Disable developer tool shortcuts / disable ctrl+s / ctrl+p / can't right in the console "),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
   
    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues2();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
    }

    public function hookDisplayHeader()
    { 
         /* Place your code here. */
        $this->protectContent();
    }
}
