<?php

namespace App\Repositories\Eloquent\Models;

use App\Repositories\Eloquent\Enums\BlogType;
use App\Repositories\Eloquent\Enums\Highlighted;
use App\Repositories\Eloquent\Enums\Status;
use App\Repositories\Eloquent\Traits\Commentable;
use App\Repositories\Eloquent\Traits\EloquentEntityNameTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Repositories\Eloquent\Models\Blog
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Repositories\Eloquent\Models\BlogAttribute[] $attributes
 * @property-read \App\Repositories\Eloquent\Models\User $author
 * @property-read \App\Repositories\Eloquent\Models\Language $language
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Repositories\Eloquent\Models\Tag[] $tags
 * @mixin \Eloquent
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $slug
 * @property int|null $language_id
 * @property int|null $author_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int|null $is_active
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereUpdatedAt($value)
 * @property int $type
 * @property-read string $stringType
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereType($value)
 * @property int $status
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property-read \App\Repositories\Eloquent\Models\User|null $createdByUser
 * @property-read \App\Repositories\Eloquent\Models\User|null $updatedByUser
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereUpdatedBy($value)
 * @property int|null $origin_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Repositories\Eloquent\Models\Blog[] $children
 * @property-read \App\Repositories\Eloquent\Models\Blog|null $origin
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereOriginId($value)
 * @property int|null $category_id
 * @property string|null $image
 * @property string|null $thumbnail
 * @property int $is_highlighted
 * @property-read \App\Repositories\Eloquent\Models\PostCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Repositories\Eloquent\Models\Comment[] $comments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereIsHighlighted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Blog whereThumbnail($value)
 */
class Blog extends Model
{
    use Commentable, EloquentEntityNameTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['title', 'content', 'slug', 'image', 'thumbnail', 'language_id', 'type', 'category_id', 'origin_id', 'is_highlighted', 'status', 'created_by', 'updated_by'];

    const IMAGES_FOLDER = 'images/blog';
    const THUMBNAILS_FOLDER = 'images/blog/thumbnails';

    /**
     * Table name which is associated with this model
     *
     * @var string
     */
    public $table = 'blog';

    public static function getAllStatuses()
    {
        return [
            Status::ACTIVE => 'Active',
            Status::INACTIVE => 'Inactive',
            Status::DELETED => 'Deleted',
        ];
    }

    public static function getChangableStatuses()
    {
        return [
            Status::ACTIVE => 'Active',
            Status::INACTIVE => 'Inactive',
        ];
    }

    public static function getHighlightedTypes()
    {
        return [
            Highlighted::NO => 'No',
            Highlighted::YES => 'Yes'
        ];
    }

    public static function getHighlightedTypeString($type)
    {
        if (array_key_exists($type, self::getHighlightedTypes())) {
            return self::getHighlightedTypes()[$type];
        }
        return 'Unknown';
    }

    public static function getThumbnailWidth()
    {
        return config('filesystems.images.blog.thumbnail_width');
    }

    /**
     * Get blog post language
     *
     */
    public function language()
    {
        return $this->hasOne(__NAMESPACE__ . '\Language', 'id', 'language_id');
    }

    /**
     * Get blog post author
     *
     */
    public function createdByUser()
    {
        return $this->belongsTo(__NAMESPACE__ . '\User', 'created_by', 'id');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(__NAMESPACE__ . '\User', 'updated_by', 'id');
    }

    public function category()
    {
        return $this->hasOne(PostCategory::class, 'id', 'category_id');
    }

    public function origin()
    {
        return $this->belongsTo(self::class, 'origin_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'origin_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'blog_tag', 'blog_id', 'tag_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(BlogAttribute::class, 'attribute_blog', 'blog_id', 'attribute_id')
            ->withTimestamps()
            ->withPivot('value');
    }

    /**
     * Get blog post as string
     *
     * @return string
     */
    public function getTagsAsString(): string
    {
        $tagsAsArray = array_column($this->tags->toArray(), 'tag');

        return implode(',', $tagsAsArray);
    }

    public function hasOrigin()
    {
        return $this->origin != null;
    }

    /**
     * @return string
     */
    public function getStringType()
    {
        switch ($this->type) {
            case BlogType::REGULAR:
                return __('admin.regular');
            case BlogType::AUTOTRANSLATED:
                return __('admin.autotranslated');
            case BlogType::COPIED:
                return __('admin.copied');
        }
        return 'Unknown';
    }

    public function getUrl()
    {
        return route('blog.view', ['slug' => $this->slug]);
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get posts by category name
     * @param string $name
     */
    public function scopeByCategoryName($query, string $name)
    {
        $query->whereHas('category', function ($query) use ($name) {
            $query->where('name', $name);
        });
    }


    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_blog', 'blog_id', 'id', 'id', 'attribute_blog_id');
    }
    /**
     * Get posts by currency using attributes
     * @param Currency $currency
     */
    public function scopeByCurrency($query, Currency $currency)
    {
        $query->whereHas('attributeValues', function ($query) use ($currency) {
            $query->where('attribute_values.value', $currency->iso_code)
                ->orWhere('attribute_values.value', $currency->title);
        });
    }

}
