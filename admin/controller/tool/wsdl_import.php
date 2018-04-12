<?php
class ControllerToolWsdlImport extends Controller {
	private $error = array();
	private $ssl = 'SSL';

	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->ssl = (defined('VERSION') && version_compare(VERSION,'2.2.0.0','>=')) ? true : 'SSL';
	}


	public function index() {
		$this->load->language('tool/wsdl_import');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('tool/wsdl_import');
        $this->getForm();
	}

	public function settings() {
		$this->load->language('tool/wsdl_import');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('tool/wsdl_import');
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('wsdl_import', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success_settings');
			$this->response->redirect($this->url->link('tool/wsdl_import', 'token=' . $this->session->data['token'], $this->ssl));
		}
		$this->getForm();
	}

    public function import()
    {
        $soapResponse = $this->getSoapResponse();
        $xml = $soapResponse['xml'];

        $json = [];

        if ($soapResponse['error']) {
            $json['error'] = $soapResponse['error'];
        } else {

            $this->load->model('catalog/product');
            $this->load->model('catalog/category');
            $this->load->model('tool/wsdl_import');

            $existingProducts = $this->model_catalog_product->getProducts();
            $existingProductsModels = [];
            $productsToImport = [];
            $categoriesToImport = [];
            foreach ($existingProducts as $existingProduct) {
                $existingProductsModels["{$existingProduct['model']}"] = $existingProduct['product_id'];
            }
            foreach ($xml->children() as $product) {

                $productAttributes = $product->attributes();
                if (!in_array($productAttributes['Artikle'], array_keys($existingProductsModels))) {
                    $productsToImport[] = $product;
                }
                $categoriesToImport[] = isset($productAttributes['Group']) ? trim($productAttributes['Group']->__toString()) : '';
            }
            $categoriesToImport = array_unique($categoriesToImport);
            $this->createCategories($categoriesToImport);

            $this->createProducts($productsToImport);

            $json['message'] = 'Created ' . count($categoriesToImport) . 'categories and ' . count($productsToImport) . ' products';
        }
        $this->response->setOutput(json_encode($json));
	}


    public function getSoapResponse()
    {
        $this->load->model('setting/setting');
        $this->load->language('tool/wsdl_import');
        $settings = $this->model_setting_setting->getSetting('wsdl_import');
        if (!isset($settings['wsdl_import_url']) || $settings['wsdl_import_url'] == ''
            || !isset($settings['wsdl_import_login']) || $settings['wsdl_import_login'] == ''
            || !isset($settings['wsdl_import_pass']) || $settings['wsdl_import_pass'] == '')
        {
            $result['xml'] = false;
            $result['error'] = $this->language->get( 'error_notifications' );
        } else {
            $options = array(
                'login' => $settings['wsdl_import_login'],
                'password' => $settings['wsdl_import_pass']
            );

            try
            {
                $client = new SoapClient($settings['wsdl_import_url'], $options);
                $response = $client->GetRemainsXML(null);
                $result['xml'] = simplexml_load_string($response->return);
                $result['error'] = false;
            }
            catch (Exception $exception)
            {
                $result['xml'] = false;
                $result['error'] = $exception->getMessage();
            }
            finally
            {
                return $result;
            }

        }
	}

    public function createCategories(Array $categories)
    {
        foreach ($categories as $category) {
            if (!$this->model_tool_wsdl_import->getCategoryByName($category)) {
                $this->model_catalog_category->addCategory(array(
                    'parent_id' => 0,
                    'top' => 1,
                    'sort_order' => 0,
                    'status' => 1,
                    'column' => 1,
                    'category_description' => array (
                        '2' => array(
                            'name' => $category,
                            'meta_title' => $category,
                            'meta_keyword' => $category,
                            'meta_description' => '',
                            'description' => ''
                        )
                    ),
                    'category_store' => array(0)
                ));
            }
        }
    }

    public function createProducts(Array $products)
    {
        foreach ($products as $product) {
            $data = array(
                'product_description' => array(
                    '2' => array(
                        'name' => $product['Name'],
                        'description' => '',
                        'meta_title' => $product['Name'],
                        'meta_description' => '',
                        'meta_keyword' => '',
                        'tag' => ''
                    )
                ),
                'model' => $product['Artikle'],
                'sku' => '',
                'upc' => '',
                'ean' => '',
                'jan' => '',
                'isbn' => '',
                'mpn' => '',
                'location' => '',
                'price' => $product['Price'],
                'tax_class_id' => '0',
                'quantity' => $product['Quantity'],
                'minimum' => '1',
                'subtract' => '1',
                'stock_status_id' => '6',
                'shipping' => '1',
                'keyword' => '',
                'date_available' => '',
                'length' => '',
                'width' => '',
                'height' => '',
                'length_class_id' => '1',
                'weight' => '',
                'weight_class_id' => '1',
                'status' => '1',
                'sort_order' => '1',
                'manufacturer' => '',
                'manufacturer_id' => '0',
                'category' => '',
                'product_category' => array(
                    isset($this->model_tool_wsdl_import->getCategoryByName(trim($product['Group']))['category_id']) ?
                        $this->model_tool_wsdl_import->getCategoryByName(trim($product['Group']))['category_id'] : ''
                ),
                'filter' => '',
                'product_store' => [0],
                'download' => '',
                'related' => '',
                'option' => '',
                'image' => '',
                'points' => '',
            );
            $this->model_catalog_product->addProduct($data);
        }
    }

    public function deleteAllProducts()
    {
        $this->load->model('catalog/product');
        $existingProducts = $this->model_catalog_product->getProducts();
        foreach ($existingProducts as $existingProduct) {
            $this->model_catalog_product->deleteProduct($existingProduct['product_id']);
        }
        $this->response->setOutput('Successfully deleted');
    }
    public function sync()
    {
        $soapResponse = $this->getSoapResponse();
        $xml = $soapResponse['xml'];

        $json = [];
        $count = 0;

        if ($soapResponse['error']) {
            $json['error'] = $soapResponse['error'];
        } else {

            $this->load->model('catalog/product');
            $this->load->model('catalog/category');
            $this->load->model('tool/wsdl_import');

            $existingProducts = $this->model_catalog_product->getProducts();
            $existingProductsModels = [];

            $existingCategories = $this->model_catalog_category->getCategories();
            foreach ($existingProducts as $existingProduct) {
                $existingProductsModels["{$existingProduct['model']}"] = $existingProduct['product_id'];
            }
            foreach ($xml->children() as $product) {

                $productAttributes = $product->attributes();
                if (in_array($productAttributes['Artikle'], array_keys($existingProductsModels))) {
                    $data['quantity'] = isset($productAttributes['Quantity']) ? $productAttributes['Quantity'] : false;
                    $data['price'] = isset($productAttributes['Price']) ? $productAttributes['Price'] : false;
                    $this->model_tool_wsdl_import->editProduct($existingProductsModels["{$productAttributes['Artikle']}"], $data);
                    $count++;
                }
                $json['categories'][] = isset($productAttributes['Group']) ? trim($productAttributes['Group']->__toString()) : '';
            }
            $json['count'] = $count;
            $json['message'] = $this->language->get('text_success');
        }
        $json['categories'] = array_unique($json['categories']);
        $this->response->setOutput(json_encode($json));
    }

    protected function getForm() {
        $data = array();
        $data['heading_title'] = $this->language->get('heading_title');

        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('wsdl_import');
        $data['wsdl_import_url'] = isset($settings['wsdl_import_url']) ? $settings['wsdl_import_url'] : '';
        $data['wsdl_import_login'] = isset($settings['wsdl_import_login']) ? $settings['wsdl_import_login'] : '';
        $data['wsdl_import_pass'] = isset($settings['wsdl_import_pass']) ? $settings['wsdl_import_pass'] : '';

        if (!empty($this->session->data['wsdl_import_error']['errstr'])) {
            $this->error['warning'] = $this->session->data['wsdl_import_error']['errstr'];
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        unset($this->session->data['wsdl_import_error']);

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], $this->ssl)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('tool/wsdl_import', 'token=' . $this->session->data['token'], $this->ssl)
        );

        $data['back'] = $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], $this->ssl);
        $data['button_back'] = $this->language->get( 'button_back' );
        $data['settings'] = $this->url->link('tool/wsdl_import/settings', 'token=' . $this->session->data['token'], $this->ssl);
        $data['sync'] = $this->url->link('tool/wsdl_import/sync', 'token=' . $this->session->data['token'], $this->ssl);

        $data['token'] = $this->session->data['token'];

//            $this->document->addStyle('view/stylesheet/wsdl_import.css');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view( ((version_compare(VERSION, '2.2.0.0') >= 0) ? 'tool/wsdl_import' : 'tool/wsdl_import.tpl'), $data));
    }

}
?>