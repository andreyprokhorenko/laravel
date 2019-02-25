<?php

namespace App\Repositories\Eloquent\Models;

use App\Repositories\Eloquent\Enums\AttributeType;
use App\Repositories\Eloquent\Enums\Status;
use App\Repositories\Eloquent\Traits\EloquentEntityNameTrait;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Repositories\Eloquent\Models\Attribute
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Repositories\Eloquent\Models\Review[] $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Repositories\Eloquent\Models\AttributeValue[] $values
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Attribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Repositories\Eloquent\Models\Attribute whereName($value)
 */
class Attribute extends Model
{
    use Translatable, EloquentEntityNameTrait;

    public static function getRouteName()
    {
        return 'reviewsattributes';
    }

    public $translatedAttributes = ['translated_name'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'type', 'status', 'created_by', 'updated_by'];

    public static function getAllStatuses()
    {
        return [
            Status::ACTIVE   => 'Active',
            Status::INACTIVE => 'Inactive'
        ];
    }

    public static function getAvailableTypes()
    {
        return [
            AttributeType::ARRAY_LIST => 'array_list',
            AttributeType::BOOLEAN => 'boolean',
            AttributeType::DETAILED => 'detailed',
            AttributeType::ENUM => 'enum',
            AttributeType::INTEGER => 'integer',
            AttributeType::NORMAL => 'normal',
        ];
    }

    public static function getTypeNameByValue(int $value)
    {
        $allTypes = self::getAvailableTypes();
        if (array_key_exists($value, $allTypes)) {
            return $allTypes[$value];
        }
        return 'unknown';
    }

    public function createdByUser()
    {
        return $this->belongsTo(__NAMESPACE__ . '\User' , 'created_by', 'id');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(__NAMESPACE__ . '\User' , 'updated_by', 'id');
    }

    public function reviews()
    {
        return $this->belongsToMany(Review::class, 'attribute_review', 'attribute_id', 'review_id')
                    ->withPivot('value');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reviewTypes()
    {
        return $this->belongsToMany(ReviewType::class, 'review_type_attribute', 'attribute_id', 'review_type_id');
    }

    public function postIds()
    {
        return $this->hasMany(AttributeReview::class, 'attribute_id', 'id');
    }

    public function values()
    {
        return $this->hasMany(__NAMESPACE__ . '\AttributeValue' , 'attribute_review_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(__NAMESPACE__ . '\AttributeDetail' , 'review_attribute_id', 'id')->orderBy('position');
    }

    public function scopeActive()
    {
        return $this->where('status', Status::ACTIVE);
    }

    public function scopeInActive()
    {
        return $this->where('status', Status::INACTIVE);
    }
}
