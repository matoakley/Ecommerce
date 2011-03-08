<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Product_Image extends Model_Application
{
    public static function initialize(Jelly_Meta $meta)
    {
        $meta->table('product_images')
            ->fields(array(
                'id' => new Field_Primary,
				'product' => new Field_BelongsTo(array(
					'foreign' => 'product.id',
				)),
				'alt_text' => new Field_String,
				'full_size_path' => new Field_String(array(
					'in_db' => FALSE,
				)),
				'thumb_path' => new Field_String(array(
					'in_db' => FALSE,
				)),
				'created' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
				'modified' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s'
				)),
				'deleted' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s'
				)),
    	));
    }

	public function __get($name)
	{
		if ($name == 'full_size_path')
		{
			return $this->get_filepath('full_size');
		}
		
		if ($name == 'thumb_path')
		{		
			return $this->get_filepath('thumb');
		}
		
		return parent::__get($name);
	}
	
	private function get_filepath($type = 'full_size')
	{
		$path = '/images/products/' . $type . '/'. date('Y/m/', $this->created) . $this->id . '.jpg';
		
		if ( ! file_exists(DOCROOT . $path))
		{
			$path = '/images/products/default_' . $type . '.jpg';
		}
		
		return $path;
	}
	
	public function update($data)
	{
		$this->alt_text = ($data['alt_text'] != '') ? $data['alt_text'] : $this->product->name;
		return $this->save();
	}
}