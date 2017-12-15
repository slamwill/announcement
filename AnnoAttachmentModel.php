<?php

namespace App\Models\HrdbMain;

use Illuminate\Pagination\Paginator;
use App\Models\CDB;

class AnnoAttachmentModel
{
    protected static $dbMajorName      = 'hrdb';
    protected static $dbGroupName      = 'main';
    protected static $tableName        = 'anno_attachment';

    public static function listData($data = NULL)
    {
        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);

        if(isset($data['select']) && $data['select']){
            $query->select($query->raw($data['select']));
        }
        // 流水號
        if(isset($data['anno_id']) && is_array($data['anno_id'])){
            $query->whereIn('anno_id',$data['anno_id']);
        }elseif(isset($data['anno_id']) && $data['anno_id']){
            $query->where('anno_id','=',$data['anno_id']);
        }

        if(isset($data['anno_file_name']) && is_array($data['anno_file_name'])){
            $query->whereIn('anno_file_name',$data['anno_file_name']);
        }elseif(isset($data['anno_file_name']) && $data['anno_file_name']){
            $query->where('anno_file_name','=',$data['anno_file_name']);
        }

        if(isset($data['anno_attachment_content']) && is_array($data['anno_attachment_content'])){
            $query->whereIn('anno_attachment_content',$data['anno_attachment_content']);
        }elseif(isset($data['anno_attachment_content']) && $data['anno_attachment_content']){
            $query->where('anno_attachment_content','=',$data['anno_attachment_content']);
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

        if(isset($data['anno_attachment_id']) && $data['anno_attachment_id'] !== ''){
            $setData['anno_attachment_id'] = $data['anno_attachment_id'];
        }

        if(isset($data['anno_id']) && $data['anno_id'] !== '')
        {
        	$setData['anno_id']  = $data['anno_id'];
        }

        if(isset($data['anno_file_name']) && $data['anno_file_name'] !== '')
        {
            $setData['anno_file_name']  = $data['anno_file_name'];
        }

        if(isset($data['anno_path']) && $data['anno_path'] !== '')
        {
            $setData['anno_path']  = $data['anno_path'];
        }

        if(isset($data['anno_attachment_content']) && $data['anno_attachment_content'])
        {
        	$setData['anno_attachment_content']  = $data['anno_attachment_content'];
        }

        $setData['created_at'] = $query->raw('NOW()');
        $tId = $query->insertGetId($setData);

        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
        return $tId;
    }

    public static function up($data = NULL)
    {
        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);

        if(isset($data['anno_id']) && $data['anno_id'] !== '')
        {
        	$setData['anno_id']  = $data['anno_id'];
        }
        
        if(isset($data['anno_file_name']) && $data['anno_file_name'] !== '')
        {
            $setData['anno_file_name']  = $data['anno_file_name'];
        }

        if(isset($data['anno_path']) && $data['anno_path'] !== '')
        {
            $setData['anno_path']  = $data['anno_path'];
        }

        if(isset($data['anno_attachment_content']) && $data['anno_attachment_content'])
        {
        	$setData['anno_attachment_content']  = $data['anno_attachment_content'];
        }

        $setData['updated_at']  = $query->raw('NOW()');

        $query->where('anno_attachment_id','=',$data['anno_attachment_id']);
        $query->update($setData);
        
        $setData = NULL; unset($setData); // 清除
        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
        return true;
    }

    // 刪除公告附加檔案
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
