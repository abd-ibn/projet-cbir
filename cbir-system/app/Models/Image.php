<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'filepath', 'category_id', 'descriptors'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
