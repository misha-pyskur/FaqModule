<?php

require_once(__DIR__ . '/../../classes/FAQ.php');
require_once(__DIR__ . '/../../faqmodule.php');

class AdminFaqModuleController extends ModuleAdminController
{
    public function __construct()
    {
        $this->name = 'faqmodule';
        $this->module = new FaqModule();
        $this->bootstrap = true;
        $this->table = 'faqs';
        $this->identifier = 'id_faq';
        $this->actions = array('edit', 'delete');
        $this->className = 'FAQ';
        $this->controller_type = 'moduleadmin';
        $this->lang = true;

        parent::__construct();

        $this->model = new PrestaShopCollection('FAQ');
        $products = Product::getProducts(1, 0, 0, 'id_product', 'ASC');
        $this->product_options = $products;

        $this->initList();
        $this->displayForm();
    }

    private function initList()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $this->fields_list = array(
            'id_faq' => array(
                'title' => $this->l('id'),
                'type' => 'text',
            ),
            'question' => array(
                'title' => $this->l('Question'),
                'type' => 'text',
            ),
            'answer' => array(
                'title' => $this->l('Answer'),
                'type' => 'text'
            ),
            'friendly_url' => array(
                'title' => $this->l('Friendly URL'),
                'type' => 'text'
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool'
            )
        );

        $helper = new HelperList();

        $helper->languages = Language::getLanguages();
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        return $helper;
    }

    public function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $this->fields_form = array(
                'input'  => array(
                    'question'   => array(
                        'type'     => 'text',
                        'label'    => $this->l('Question'),
                        'name'     => 'question',
                        'required' => true,
                        'lang' => true,
                    ),
                    'answer'   => array(
                        'type'     => 'textarea',
                        'label'    => $this->l('Answer'),
                        'name'     => 'answer',
                        'autoload_rte' => true,
                        'required' => true,
                        'lang' => true,
                    ),
                    'friendly_url'   => array(
                        'type'     => 'text',
                        'label'    => $this->l('Friendly URL'),
                        'name'     => 'friendly_url',
                    ),
                    'active' => array(
                        'type'   => 'switch',
                        'label'  => $this->l('Active'),
                        'name'   => 'active',
                        'values' => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    'associated_products[]' => array(
                        'type' => 'select',
                        'label' => 'Associated Products',
                        'name' => 'associated_products[]',
                        'class' => 'chosen',
                        'multiple' => true,
                        'options' => array(
                            'query' => $this->product_options,
                            'id' => 'id_product',
                            'name' => 'name'
                        )
                    )

                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
        );

        $helper = new HelperForm();
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->languages = Language::getLanguages();
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->fields_value['question_1'] = 'Value';
        $helper->generateForm($this->fields_form);
    }
}