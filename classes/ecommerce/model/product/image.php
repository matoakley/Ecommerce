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
				'created' =>  new Field_Timestamp(array(
					'auto_now_create' => TRUE,
					'format' => 'Y-m-d H:i:s',
				)),
				'modified' => new Field_Timestamp(array(
					'auto_now_update' => TRUE,
					'format' => 'Y-m-d H:i:s',
				)),
				'deleted' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
			));
	}

	public static function upload($file_path, $product_id)
	{
		$i = Jelly::factory('product_image');
		$i->product = $product_id;
		$i->save();
	
		// Resize and save the images
		$image = Image::factory($file_path);
		
		// Full Size first
		$full_size_size = Kohana::config('ecommerce.image_sizing.full_size');  // Apologies for the stupid variable naming!
		$image->resize($full_size_size['width'], $full_size_size['height'], Image::INVERSE);
		
		// Crop it for good measure
		$image->crop($full_size_size['width'], $full_size_size['height']);
		
		// Loop through each step of the dir path and check the dir exists or create it
		$directory_parts = array(
			'images',
			'products',
			'full_size',
			date('Y', $i->created),
			date('m', $i->created),
		);
		$directory = DOCROOT.'/';
		foreach ($directory_parts as $dir)
		{
			$directory .= $dir.'/';
			if ( ! is_dir($directory))
			{
				mkdir($directory);
			}
		}
		
		$image->save(DOCROOT . $i->get_filepath('full_size'));
		
		// Then Thumbnail
		$thumbnail_size = Kohana::config('ecommerce.image_sizing.thumbnail');
		$image->resize($thumbnail_size['width'], $thumbnail_size['height'], Image::INVERSE);
		
		// Loop through each step of the dir path and check the dir exists or create it
		$directory_parts = array(
			'images',
			'products',
			'thumb',
			date('Y', $i->created),
			date('m', $i->created),
		);
		$directory = DOCROOT.'/';
		foreach ($directory_parts as $dir)
		{
			$directory .= $dir.'/';
			if ( ! is_dir($directory))
			{
				mkdir($directory);
			}
		}
				
		$image->save(DOCROOT . $i->get_filepath('thumb'));
		
		return $i;
	}

	public function __get($name)
	{
		if ($name == 'full_size_path')
		{
			$path = $this->get_filepath('full_size');
			
			if ( ! file_exists(DOCROOT . $path))
			{
				$path = '/images/products/default_full_size.jpg';
			}
		
			return $path; 
		}
		
		if ($name == 'thumb_path')
		{	
			$path = $this->get_filepath('thumb'); 

			if ( ! file_exists(DOCROOT . $path))
			{
				$path = '/images/products/default_thumb.jpg';
			}

			return $path;
		}
		
		return parent::__get($name);
	}
	
	private function get_filepath($type = 'full_size')
	{
		return '/images/products/' . $type . '/'. date('Y/m/', $this->created) . $this->id . '.jpg';
	}
	
	public function update($data)
	{
		$this->alt_text = $data['alt_text'];
		return $this->save();
	}
	
	// Override standard delete to handle orphaned defualt and thumbnail images
	// and also remove files from teh serverz.
	public function delete($key = FALSE)
	{	
		$product = $this->product;
		
		unlink(DOCROOT . $this->get_filepath('full_size'));
		unlink(DOCROOT . $this->get_filepath('thumb'));
		
		parent::delete($key);
		
		if ( ! $product->default_image->loaded())
		{
			$product->set_default_image();
		}

		if ( ! $product->thumbnail->loaded())
		{
			$product->set_thumbnail();
		}
	}
}