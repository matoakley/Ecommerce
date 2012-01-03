<?php defined('SYSPATH') or die('No direct script access.');

class Ecommerce_Model_Blog_Post extends Model_Application
{
	public static function initialize(Jelly_Meta $meta)
	{
		$meta->table('blog_posts')
			->fields(array(
				'id' => new Field_Primary,
				'author' => new Field_BelongsTo(array(
					'column' => 'user_id',
					'foreign' => 'user.id',
				)),
				'name' => new Field_String,
				'slug' => new Field_String,
				'body' => new Field_Text,
				'published_on' => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s',
				)),
				'status' => new Field_String,
				'meta_description' => new Field_String,
				'meta_keywords' => new Field_String,
        'featured_image' => new Field_String(array(
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

	public static $statuses = array(
		'active', 'disabled'
	);
	
	public static $searchable_fields = array(
		'filtered' => array(
			'status' => array(
				'field' => 'status',
			),
		),
		'search' => array(
			'name',
			'body',
		),
	);
	
  public function __get($field)
  {
    if ($field == 'featured_image')
    {
      return $this->get_featured_image();
    }

    return parent::__get($field);
  }

	public static function get_posts_by_author($user_id, $limit = 5)
	{
		return Jelly::select('blog_post')->where('user_id', '=', $user_id)->order_by('created', 'DESC')->limit($limit)->execute();
	}

	public function body_summary()
	{
		return Text::limit_words(strip_tags($this->body, '<p><br>') , 100, ' &hellip;');
	}
	
	public function update($data)
	{
		$this->name = $data['name'];
		
		if (isset($data['slug']))
		{
			$this->slug = $data['slug'];
		}

		$this->body = $data['body'];
		$this->status = $data['status'];
		
		if ($this->status == 'active' AND $this->published_on == NULL)
		{
			$this->publised_on = time();
		}
		
		$this->meta_description = $data['meta_description'];
		$this->meta_keywords = $data['meta_keywords'];
		
		if ( ! $this->author->loaded())
		{
			$this->author = Auth::instance()->get_user()->id;
		}
		
		// Ping sitemap to search engines to alert them of content change
		if (IN_PRODUCTION AND $this->status == 'active')
		{
			Sitemap::ping(URL::site(Route::get('sitemap_index')->uri()), TRUE);
		}

		
		return $this->save();
	}

	public function get_featured_image()
	{
		$file_path = '/images/blog-posts/' . $this->id . '.jpg';
		
		if ( ! file_exists(DOCROOT . $file_path))
		{
			$file_path = '/images/blog-posts/default.jpg';
		}
		
		return $file_path;
	}

	public function upload_image($tmp_file)
	{
		// Let's get to work on resizing this image
		$image = Image::factory($tmp_file);
		
		// Full Size first
		$image_size = Kohana::config('ecommerce.blog_image_sizing');
		$image->resize($image_size['width'], $image_size['height'], Image::INVERSE);
		
		// Crop it for good measure
		$image->crop($image_size['width'], $image_size['height']);
		
		$directory = DOCROOT . '/images/blog-posts';
		if ( ! is_dir($directory))
		{
			mkdir($directory);
		}
		
		$image->save($directory . DIRECTORY_SEPARATOR . $this->id . '.jpg');
	}
}
