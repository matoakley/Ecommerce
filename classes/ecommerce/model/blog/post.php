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
        'categories' => new Field_ManyToMany(array(
					'foreign' => 'blog_category',
					'through' => 'blog_categories_blog_posts',
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
	public static $authors = array(
	   'author' 
	   );
	
	public static $searchable_fields = array(
		'filtered' => array(
			'status' => array(
				'field' => 'status',
			),
			'category' => array(
				'join' => array(
					'blog_categories_blog_posts' => array('blog_post.id', 'blog_categories_blog_posts.blog_post_id'),
					'blog_categories' => array('blog_category.id', 'blog_categories_blog_posts.blog_category_id'),
				),
				'field' => 'blog_category.id',
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
		return Jelly::select('blog_post')->where('user_id', '=', $user_id)->where('status', '=', 'active')->order_by('created', 'DESC')->limit($limit)->execute();
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
		
	
	  if (isset($data['author']))
			{
				$this->author = intval($data['author']);
			}
		else {
		
  		if ( ! $this->author->loaded())
  		{
  			$this->author = Auth::instance()->get_user()->id;
  		}
		}
		
		// Clear down and save categories.
		$this->remove('categories', $this->categories);
		
		if (isset($data['categories']))
		{
			$this->add('categories', $data['categories']);
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
		if ($image_size['width'] > 0 AND $image_size['height'] > 0)
		{
			$image->resize($image_size['width'], $image_size['height'], Image::INVERSE);
			// Crop it for good measure
			$image->crop($image_size['width'], $image_size['height']);
		}
		elseif ($image_size['width'] == 0)
		{
			$image->resize(NULL, $image_size['height']);
		}
		else
		{
			$image->resize($image_size['width'], NULL);
		}
		
		$directory = DOCROOT . 'images/blog-posts';
		if ( ! is_dir($directory))
		{
			mkdir($directory);
		}
		
		$image->save($directory . DIRECTORY_SEPARATOR . $this->id . '.jpg');
	}
}
