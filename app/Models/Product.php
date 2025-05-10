<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Define which attributes are mass assignable
    protected $fillable = [
        'name', 
        'description', 
        'price', 
        'stock_quantity', 
        'category_id', 
        'user_id', 
        'image_url',
        'is_public'
    ];

    // A product belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // A product belongs to a user (seller)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A product can have many product images
    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
}
