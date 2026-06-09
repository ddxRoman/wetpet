<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title',
        'slug',
        'image',
        'images', // Разрешаем заполнение поля массивом
        'excerpt',
        'content',
        'is_published',
        'views',
        'region_id'
    ];

    // Автоматически преобразует JSON из базы в PHP-массив и наоборот
    protected $casts = [
        'images' => 'array',
    ];
}