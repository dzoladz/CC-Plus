<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
   /**
    * The database table used by the model.
    *
    * @var string
    */
    protected $connection = 'globaldb';
    protected $table = 'books';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Title', 'ISBN'
    ];

    public function titleReports()
    {
        return $this->hasMany('App\TitleReport');
    }
}
