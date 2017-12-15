<?php

namespace App\Models\HrdbMain;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use App\Models\CDB;


class AnnoVisitorModel
{
    protected static $dbMajorName      = 'hrdb';
    protected static $dbGroupName      = 'main';
    protected static $tableName        = 'anno_visitor';



    public static function listData($data = NULL)
    {
        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);

        if(isset($data['select']) && $data['select']){
            $query->select($query->raw($data['select']));
        }

        if(isset($data['anno_id']) && $data['anno_id']){
            $query->where('anno_id','=',$data['anno_id']);
        }

        if(isset($data['anno_manager_id']) && $data['anno_manager_id']){
            $query->where('anno_manager_id',$data['anno_manager_id']);
        }

        if(isset($data['pageMode']) && isset($data['listNum'])){
            switch($data['pageMode']){
                case 'original':
                    if(isset($data['limitPage']) && isset($data['listNum']) && $data['limitPage'] !== '' && $data['listNum'] !== ''){
                        $query->skip($data['limitPage'])->take($data['listNum']);
                    }
                    $content                    = $query->get();
                    break;
                case 'simple':
                    $content                    = $query->simplePaginate($data['listNum']);
                    break;
                case 'normal':
                default:
                    $content                    = $query->paginate($data['listNum']);
            }
        }else{
            $content                            = $query->get();
        }
        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
        return ($content->first())? $content:false;
    }


    public static function add($data = NULL)
    {
        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);

        if(isset($data['anno_id']) && $data['anno_id'])
        {
        	$setData['anno_id'] = $data['anno_id'];
        }

        if(isset($data['anno_manager_id']) && $data['anno_manager_id'])
        {
        	$setData['anno_manager_id'] = $data['anno_manager_id'];
        }

        $setData['created_at'] = $query->raw('NOW()');
        $tId = $query->insertGetId($setData);

        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
        return $tId;
    }

    // 刪除公告點閱紀錄
    public static function del($data = NULL)
    {
    	$query  = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
        if(isset($data['anno_id']) && $data['anno_id']){
            $query->where('anno_id','=',$data['anno_id']);
            $tId = $query->delete();

            CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
            return $tId;
        }else{
            return false;
        }
    }



}
