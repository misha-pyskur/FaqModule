<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/classes/FAQ.php');
require_once(dirname(__FILE__) . '/../blocktopmenu/blocktopmenu.php');


class FaqModule extends Module
{
    protected $link_id;
    protected $link_name;
    protected $menu_top_links;
    protected $block_top_menu;

    public function __construct()
    {
        $this->name = 'faqmodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Misha Pyskur';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('FAQ Module');
        $this->description = $this->l('My test module');
        $this->dependencies = array('blocktopmenu');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->model = new PrestaShopCollection('FAQ');
        $this->menu_top_links = new MenuTopLinks();
        $this->block_top_menu = ModuleCore::getInstanceByName('blocktopmenu');
    }


    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (Module::isInstalled('blocktopmenu')) {
            $languages = $this->context->controller->getLanguages();
            $shops = Shop::getContextListShopID();

            foreach ($shops as $shop_id) {
                $links_label = array();
                $labels = array();

                foreach ($languages as $val) {
                    $links_label[$val['id_lang']] = $this->context->link->getModuleLink(
                        'faqmodule',
                        'displayfaqs',
                        array('friendly_url' => 'all')
                    );

                    $labels[$val['id_lang']] = 'FAQ';
                }

                $addLink = $this->menu_top_links->add($links_label, $labels, 0, (int) $shop_id);
                if (!$addLink) {
                    return false;
                }

                $sql = 'SELECT * FROM '._DB_PREFIX_.'linksmenutop';
                $topMenuLinks = Db::getInstance()->ExecuteS($sql);

                if ($topMenuLinks) {
                    $lastlink = array_pop($topMenuLinks);
                    $this->link_id = $lastlink['id_linksmenutop'];
                    $this->link_name = 'LNK' . $this->link_id;
                }

                $modBlocktopmenuItems = Configuration::get('MOD_BLOCKTOPMENU_ITEMS');

                if ($modBlocktopmenuItems !== '') {
                    $modBlocktopmenuItems = $modBlocktopmenuItems . ',' . $this->link_name;
                } else {
                    $modBlocktopmenuItems = $this->link_name;
                }

                Configuration::updateValue(
                    'MOD_BLOCKTOPMENU_ITEMS',
                    $modBlocktopmenuItems,
                    false,
                    (int)$shop_group_id,
                    (int)$shop_id
                );
            }
        }

        if (!parent::install() ||
            !$this->registerHook('productTab') ||
            !$this->registerHook('header') ||
            !$this->registerHook('moduleRoutes')
        ) {
            return false;
        } else {
            $this->createTable();
            $this->createAdminTab();
            return true;
        }
    }

    public function uninstall()
    {
        if (Module::isInstalled('blocktopmenu')) {
            $shops = Shop::getContextListShopID();

            $sql = 'SELECT * FROM '._DB_PREFIX_.'linksmenutop';
            $topMenuLinks = Db::getInstance()->ExecuteS($sql);

            if ($topMenuLinks) {
                $lastlink = array_pop($topMenuLinks);
                $this->link_id = $lastlink['id_linksmenutop'];
                $this->link_name = 'LNK' . $this->link_id;
            }

            $modBlocktopmenuItems = Configuration::get('MOD_BLOCKTOPMENU_ITEMS');
            $exploded = explode(',', $modBlocktopmenuItems);

            if ($needle = array_search($this->link_name, $exploded)) {
                unset($exploded[$needle]);
            }

            $exploded = implode(',', $exploded);

            foreach ($shops as $shop_id) {
                $delete_link = $this->menu_top_links->remove($this->link_id, $shop_id);
            }

            Configuration::updateValue('MOD_BLOCKTOPMENU_ITEMS', $exploded, false, (int)$shop_group_id, (int)$shop_id);
        }

        if (!parent::uninstall()) {
            return false;
        } else {
            $this->removeTable();
            $this->removeAdminTab();

            return true;
        }
    }

    protected function createTable()
    {
        $createfaqs = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'faqs (
            id_faq INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            friendly_url VARCHAR(255),
            active BOOLEAN,
            associated_products TEXT
        )';

        $createfaqslang = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'faqs_lang (
            id_faq INT(11) NOT NULL,
            id_lang INT(11) NOT NULL,
            question VARCHAR(255),
            answer TEXT
        )';

        if (!Db::getInstance()->execute($createfaqs) || !Db::getInstance()->execute($createfaqslang)) {
            return false;
        }

        return true;
    }

    protected function removeTable()
    {
        $dropfaqs = 'DROP TABLE ' . _DB_PREFIX_ . 'faqs';
        $dropfaqslang = 'DROP TABLE ' . _DB_PREFIX_ . 'faqs_lang';

        if (!Db::getInstance()->execute($dropfaqs) || !Db::getInstance()->execute($dropfaqslang)) {
            return false;
        }

        return true;
    }

    private function createAdminTab()
    {
        $langs = Language::getLanguages();
        $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $tab = new Tab();
        $tab->class_name = 'AdminFaqModule';
        $tab->module = 'faqmodule';
        $tab->id_parent = 0;

        foreach ($langs as $l) {
            $tab->name[$l['id_lang']] = $this->l('FAQ Module');
        }

        $tab->save();
        $tab_id = $tab->id;
        Configuration::updateValue('FAQ_MODULE_TAB_ID', $tab_id);
        return true;
    }

    public function removeAdminTab()
    {
        $tab = new Tab(Configuration::get('FAQ_MODULE_TAB_ID'));

        $tab->delete();
        Configuration::deleteByName('FAQ_MODULE_TAB_ID');

        return true;
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS(dirname(__FILE__) . '/views/css/main.css');
        $this->context->controller->addCSS(dirname(__FILE__) . '/views/css/modal.css');
        $this->context->controller->addJS(dirname(__FILE__) . '/views/js/main.js');
        $this->context->controller->addJS(dirname(__FILE__) . '/views/js/modal.js');
    }

    public function hookDisplayProductTab()
    {
        if (Tools::getValue('id_product')) {
            $thisProduct = Tools::getValue('id_product');
            $all_faqs = $this->model->getResults();

            $thisAssociatedQuestions = array();
            foreach ($all_faqs as $faqObject) {
                if ($faqObject->associated_products !== 'N;') {
                    $associated_products = unserialize($faqObject->associated_products);
                    if (is_array($associated_products)) {
                        foreach ($associated_products as $item) {
                            if ($item === $thisProduct) {
                                array_push($thisAssociatedQuestions, $faqObject);
                            }
                        }
                    }
                }
            }

            $this->context->smarty->assign(
                array(
                    'faqs' => $thisAssociatedQuestions
                )
            );
            return $this->display(__FILE__, 'views/templates/hook/product.tpl');
        }
    }

    public function hookModuleRoutes()
    {
        return array(
            'module-faqmodule-display' => array(
                'controller' => 'displayfaqs',
                'rule' => 'faqs/{friendly_url}',
                'keywords' => array(
                    'friendly_url' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'friendly_url')
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'faqmodule'
                )
            ),
        );
    }
}
