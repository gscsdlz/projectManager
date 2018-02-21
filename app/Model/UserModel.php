<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/8
 * Time: 9:00
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = "user";
    protected $primaryKey = "user_id";
    public $timestamps = true;
}