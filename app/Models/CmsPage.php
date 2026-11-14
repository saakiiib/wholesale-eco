<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CmsPage extends Model {protected $fillable=['title','slug','body','meta_title','meta_description','published'];protected function casts():array{return ['published'=>'boolean'];}}
