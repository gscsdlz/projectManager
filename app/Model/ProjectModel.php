<?php
/**
 * Created by PhpStorm.
 * User: 南宫悟
 * Date: 2018/2/8
 * Time: 9:00
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ProjectModel extends Model
{
    protected $table = "project";
    protected $primaryKey = "project_id";
    public $timestamps = true;
}