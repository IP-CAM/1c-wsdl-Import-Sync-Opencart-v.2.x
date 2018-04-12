<?php

static $registry = null;

class ModelToolWsdlImport extends Model {

	private $error = array();
	protected $null_array = array();
	protected $use_table_seo_url = false;
	protected $posted_categories = '';


	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->use_table_seo_url = version_compare(VERSION,'3.0','>=') ? true : false;
	}

    public function editProduct($product_id, $data)
    {
        if (isset($data['quantity']) && $data['quantity'] != false ) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . $this->db->escape($data['quantity']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }
        if (isset($data['price']) && $data['price'] != false ) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET price = '" . $this->db->escape($data['price']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }
	}

    public function getCategoryByName($name)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE name = '" . $name . "'");

        return $query->row;
	}
}


if (version_compare(VERSION,'3.0','>=')) {
	class ModelExtensionWsdlImport extends ModelToolWsdlImport {
	}
}

?>