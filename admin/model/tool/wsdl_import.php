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
        if (isset($data['quantity']) && $data['quantity'] !== false ) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . $this->db->escape($data['quantity']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }
        if (isset($data['price']) && $data['price'] != false ) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET price = '" . $this->db->escape($data['price']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        if (isset($data['product_category'])) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
            foreach ($data['product_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
            }
        }
	}

    public function getCategoryByName($name, $parentId = false)
    {
        if ($parentId !== false) {
            $query = $this->db->query(
                "SELECT * FROM " . DB_PREFIX . "category_description LEFT JOIN " . DB_PREFIX . "category ON "
                . DB_PREFIX . "category_description.category_id = " . DB_PREFIX . "category.category_id WHERE "
                . DB_PREFIX . "category_description.name = '" . $name . "' AND "
                . DB_PREFIX . "category.parent_id = '" . $parentId . "'"
            );
        } else {
            $query = $this->db->query(
                "SELECT * FROM " . DB_PREFIX . "category_description WHERE name = '" . $name . "'"
            );
        }
        return $query->row;
	}

    public function getProductsByModel($model)
    {
        $query = $this->db->query(
            "SELECT * FROM " . DB_PREFIX . "product WHERE model = '" . $model . "'"
        );
        return $query->rows;
	}
}


if (version_compare(VERSION,'3.0','>=')) {
	class ModelExtensionWsdlImport extends ModelToolWsdlImport {
	}
}

?>
