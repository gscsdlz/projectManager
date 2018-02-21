<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/8
 * Time: 9:00
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PeopleModel extends Model
{
    protected $table = "member";
    protected $primaryKey = "member_id";
    public $timestamps = true;
}