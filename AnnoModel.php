<?php

namespace App\Models\HrdbMain;

use App\Http\Controllers\Common\AnnoCotroller;
use App\Models\CDB;
use Illuminate\Support\Facades\DB;


class AnnoModel 
{
    protected static $dbMajorName      = 'hrdb';
    protected static $dbGroupName      = 'main';
    protected static $tableName        = 'anno_main';

    /**
     * 公告類型管理
     * 1: 全站公告 (未使用)
     * 2: 路徑公告 (未使用)
     * 3: 功能說明 (使用)
     * 4: 入口公告 (使用)
     * 5: 即時訊息 (使用)
     * 6: 最新消息 (使用)
     * @return object $content
     */
    public static function typeAnno()
    {
        $content                 = [
            'OriginalSiteAnno'   =>  1,
            'RouteAnno'          =>  2,
            'Description'        =>  3,
            'Entry'              =>  4,
            'InTime'             =>  5,
            'NewSiteAnno'        =>  6,
        ];
        return (object)$content;
    }

    /**
     * 公告學別管理
     * 1: 小學
     * 2: 中學
     * @return object $content
     */
    public static function educationAnno()
    {
        $content                    = [
            'PrimaryAnno'           =>  1,
            'JuniorAnno'            =>  2,
            'PrimaryJuniorAnno'     =>  3,
        ];
        return (object)$content;
    }

    /**
     * 公告網站管理
     * 8:  人力網
     * 9:  學籍網
     * 10: 學生網
     * @return object $content
     */
    public static function siteAnno()
    {
        $content        =  [
            'H_Anno'    => 'H',
            'R_Anno'    => 'R',
            'S_Anno'    => 'S',
        ];
        return (object)$content;
    }

    /**
     * 公告權限管理
     * 1: 管理者
     * 2: 教育部
     * 3: 縣市端
     * 4: 學校端
     * 5: 縣市端&學校端
     * @return object $content
     */
    public static function typePermission()
    {
        $content            =   [
            'Manage'        =>  1,
            'Edu'           =>  2,
            'County'        =>  3,
            'School'        =>  4,
            'County_School' =>  5,
        ];
        return (object)$content;
    }

    public static function listData($data = NULL)
    {
    	$query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);

        if(isset($data['select']) && $data['select']){
            $query->select($query->raw($data['select']));
        }

        // 置頂文 排序
        if(isset($data['order_sticky']) && is_array($data['order_sticky']))
        {
            $query->orderBy('anno_sticky','1');
        }

		if(isset($data['order']) && is_array($data['order']))
		{
            foreach($data['order'] as $key=>$val)
            {
                if(!$val) $val = 'ASC';
                $query->orderBy($key,$val);
            }
		}
        else
		{
			$query->orderBy('anno_id','ASC');
		}

        // 公告類型
        if(isset($data['anno_type']) && $data['anno_type'])
        {
            $query->where('anno_type','=',$data['anno_type']);
        }

        if(isset($data['anno_category_id']) && $data['anno_category_id'])
        {
            $query->where('anno_category_id','=',$data['anno_category_id']);
        }

        if(isset($data['anno_start_date']) && $data['anno_start_date'])
        {
            $query->where('anno_start_date','=',$data['anno_start_date']);
        }

        if(isset($data['anno_end_date']) && $data['anno_end_date'])
        {
            $query->where('anno_end_date','=',$data['anno_end_date']);
        }

        // 人力網
        if(isset($data['anno_site_h']) && $data['anno_site_h'])
        {
            $query->where('anno_site_h','=',$data['anno_site_h']);
        }
        // 學籍網
        if(isset($data['anno_site_r']) && $data['anno_site_r'])
        {
            $query->where('anno_site_r','=',$data['anno_site_r']);
        }
        // 學生網
        if(isset($data['anno_site_s']) && $data['anno_site_s'])
        {
            $query->where('anno_site_s','=',$data['anno_site_s']);
        }
        // 使用者帳號id
        if(isset($data['anno_manager_id']) && $data['anno_manager_id'])
        {
            $query->where('anno_manager_id','=',$data['anno_manager_id']);
        }

        if(isset($data['anno_title']) && $data['anno_title'])
        {
            $query->where('anno_title', 'like', '%'.$data['anno_title'].'%');
        }

        if(isset($data['anno_content']) && $data['anno_content'])
        {
            $query->where('anno_content', 'like', '%'.$data['anno_content'].'%');
        }

        if(isset($data['anno_authority']) && $data['anno_authority'])
        {
            $query->where('anno_authority','=',$data['anno_authority']);
        }

        if(isset($data['anno_publish']) && $data['anno_publish'])
        {
            $query->where('anno_publish','=',$data['anno_publish']);
        }

        if(isset($data['anno_primary']) && $data['anno_primary'])
        {
            $query->where('anno_primary','=',$data['anno_primary']);
        }

        if(isset($data['anno_junior']) && $data['anno_junior'])
        {
            $query->where('anno_junior','=',$data['anno_junior']);
        }

        if(isset($data['anno_county_only']) && $data['anno_county_only'])
        {
            $query->where('anno_county_only','=',$data['anno_county_only']);
        }

		if(isset($data['pageMode']) && isset($data['listNum'])){
            switch($data['pageMode']){
                case 'original':
                    if(isset($data['limitPage']) && isset($data['listNum']) && $data['limitPage'] !== '' && $data['listNum'] !== ''){
                        $query->skip($data['limitPage'])->take($data['listNum']);
                    }
                    $content = $query->get();
                    break;
                case 'simple':
                    $content = $query->simplePaginate($data['listNum']);
                    break;
                case 'normal':
                default:
                    $content = $query->paginate($data['listNum']);
            }
        }else{
            $content = $query->get();
        }

		CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
		return ($content->first())? $content:false;
    }

    public static function getStartModelNewAnno($data = NULL)
    {
        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);

        $typeAttr   = ManagerDataModel::typeAttr();

        if(isset($data['select']) && $data['select']){
            $query->select($query->raw($data['select']));
        }

        // 置頂文 排序
        if(isset($data['order_sticky']) && is_array($data['order_sticky']))
        {
            $query->orderBy('anno_sticky','1');
        }

        if(isset($data['order']) && is_array($data['order']))
        {
            foreach($data['order'] as $key=>$val)
            {
                if(!$val) $val = 'ASC';
                $query->orderBy($key,$val);
            }
        }
        else
        {
            $query->orderBy('anno_id','ASC');
        }

        // 公告類型
        if(isset($data['anno_type']) && $data['anno_type'])
        {
            $query->where('anno_type','=',$data['anno_type']);
        }

        if(isset($data['anno_site_h']) && $data['anno_site_h'])
        {
            $query->where('anno_site_h','=',$data['anno_site_h']);
        }
        
        if(isset($data['anno_site_r']) && $data['anno_site_r'])
        {
            $query->where('anno_site_r','=',$data['anno_site_r']);
        }

        if(isset($data['anno_site_s']) && $data['anno_site_s'])
        {
            $query->where('anno_site_s','=',$data['anno_site_s']);
        }

        // 教育部給縣市端
        if(isset($data['anno_county_only']) && $data['anno_county_only'] == 1)
        {
            $query->where('anno_county_only','=',$data['anno_county_only']);
        }

        // 國小取教育部或者縣市端給的公告
        if(isset($data['anno_primary']) && $data['anno_primary'] == 1)
        {
            if(isset($data['anno_authority']) && $data['anno_authority'] == $typeAttr->School)  // 表示讀取者是學校端 $typeAttr->School
            {   // 來自管理者或教育部的公告
                $query->where(function ($query2) use ($data){
                    $query2->where('anno_primary','=',$data['anno_primary']);
                    // 管理者或教育部的anno_authority是1/2
                    $query2->whereBetween('anno_authority', [1, 2]);
                });
            }

            if(isset($data['anno_county_id']) && $data['anno_county_id'])
            {   // 來自縣市端的公告
                $query->orWhere(function ($query2) use ($data){
                    $query2->orWhere('anno_county_id','=',$data['anno_county_id']);
                    $query2->where('anno_primary','=',$data['anno_primary']);
                });
            }
        }

        // 國中取教育部或者縣市端給的公告
        if(isset($data['anno_junior']) && $data['anno_junior'] == 1)
        {
            if(isset($data['anno_authority']) && $data['anno_authority'] == $typeAttr->School)  // 表示讀取者是學校端 $typeAttr->School
            {   // 來自管理者或教育部的公告
                $query->where(function ($query2) use ($data){
                    $query2->where('anno_junior','=',$data['anno_junior']);
                    // 管理者或教育部的anno_authority是1/2
                    $query2->whereBetween('anno_authority', [1, 2]);
                });
            }

            if(isset($data['anno_county_id']) && $data['anno_county_id'])
            {   // 來自縣市端的公告
                $query->orWhere(function ($query2) use ($data){
                    $query2->orWhere('anno_county_id','=',$data['anno_county_id']);
                    $query2->where('anno_junior','=',$data['anno_junior']);
                });
            }
        }

        // 某一個學校自己發的公告
        if(isset($data['anno_school_id']) && $data['anno_school_id'])
        {   // 該學校自己發出來的公告
            $query->orWhere('anno_school_id','=',$data['anno_school_id']);
        }

        if(isset($data['pageMode']) && isset($data['listNum'])){
            switch($data['pageMode']){
                case 'original':
                    if(isset($data['limitPage']) && isset($data['listNum']) && $data['limitPage'] !== '' && $data['listNum'] !== ''){
                        $query->skip($data['limitPage'])->take($data['listNum']);
                    }
                    $content = $query->get();
                    break;
                case 'simple':
                    $content = $query->simplePaginate($data['listNum']);
                    break;
                case 'normal':
                default:
                    $content = $query->paginate($data['listNum']);
            }
        }else{
            $content = $query->get();
        }

        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
        return ($content->first())? $content:false;
    }


    // 取公告詳細資料
    public static function detail($data = NULL)
    {
        if(isset($data['anno_id']) && $data['anno_id']){
            $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);

            $query->where('anno_id','=',$data['anno_id']);

            $content = $query->first();
            CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
            return ($content)? $content:false;
        }else{
            return false;
        }
    }

    // 創建一筆新公告
    public static function add($data = NULL)
    {
        if(isset($data['anno_content']) && $data['anno_content']){
    		$query			= CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);

            if(isset($data['anno_type']) && $data['anno_type']){
                $setData['anno_type'] = $data['anno_type'];
            }

            if(isset($data['anno_manager_id']) && $data['anno_manager_id']){
                $setData['anno_manager_id'] = $data['anno_manager_id'];
            }

            if(isset($data['anno_category_id']) && $data['anno_category_id']){
                $setData['anno_category_id'] = $data['anno_category_id'];
            }

            if(isset($data['anno_title']) && $data['anno_title']){
            	$setData['anno_title'] = $data['anno_title'];
            }

            if(isset($data['anno_content']) && $data['anno_content']){
            	$setData['anno_content'] = $data['anno_content'];
            }

            if(isset($data['anno_start_date']) && $data['anno_start_date']){
            	$setData['anno_start_date'] = $data['anno_start_date'];
            }
            if(isset($data['anno_end_date']) && $data['anno_end_date']){
            	$setData['anno_end_date'] = $data['anno_end_date'];
            }

            if(isset($data['anno_publish'])){
            	$setData['anno_publish'] = $data['anno_publish'];
            }

            if(isset($data['anno_authority'])){
                $setData['anno_authority'] = $data['anno_authority'];
            }

            if(isset($data['anno_primary'])){
                $setData['anno_primary'] = $data['anno_primary'];
            }

            if(isset($data['anno_junior'])){
                $setData['anno_junior'] = $data['anno_junior'];
            }

            if(isset($data['anno_county_only'])){
                $setData['anno_county_only'] = $data['anno_county_only'];
            }

            if(isset($data['anno_school_id'])){
                $setData['anno_school_id'] = $data['anno_school_id'];
            }

            if(isset($data['anno_county_id'])){
                $setData['anno_county_id'] = $data['anno_county_id'];
            }

            if(isset($data['anno_site_h'])){
                $setData['anno_site_h'] = $data['anno_site_h'];
            }

            if(isset($data['anno_site_r'])){
                $setData['anno_site_r'] = $data['anno_site_r'];
            }

            if(isset($data['anno_site_s'])){
                $setData['anno_site_s'] = $data['anno_site_s'];
            }

            if(isset($data['anno_sticky'])){
                $setData['anno_sticky'] = $data['anno_sticky'];
            }

            // 創建時間
            $setData['created_at'] = $query->raw('NOW()');

			$tId = $query->insertGetId($setData);

            $setData = NULL; unset($setData); // 清除
			CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
			return $tId;
    	}else{
    		return false;
    	}
    }

    // 更新公告
    public static function up($data = NULL)
    {
        $typeAnno = self::typeAnno();

        if(isset($data['anno_content']) && $data['anno_content']){
            $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);

            if(isset($data['anno_type']) && $data['anno_type']){
                $setData['anno_type'] = $data['anno_type'];
            }

            if(isset($data['anno_title']) && $data['anno_title']){
                $setData['anno_title'] = $data['anno_title'];
            }

            if(isset($data['anno_content']) && $data['anno_content']){
                $setData['anno_content'] = $data['anno_content'];
            }

            if(isset($data['anno_start_date']) && $data['anno_start_date']){
                $setData['anno_start_date'] = $data['anno_start_date'];
            }

            if(isset($data['anno_end_date']) && $data['anno_end_date']){
                $setData['anno_end_date'] = $data['anno_end_date'];
            }

            if(isset($data['anno_publish'])){
                $setData['anno_publish'] = $data['anno_publish'];
            }

            if(isset($data['anno_sticky'])){
                $setData['anno_sticky'] = $data['anno_sticky'];
            }

            if(isset($data['anno_authority'])){
                $setData['anno_authority'] = $data['anno_authority'];
            }

            if(isset($data['anno_primary'])){
                $setData['anno_primary'] = $data['anno_primary'];
            }

            if(isset($data['anno_junior'])){
                $setData['anno_junior'] = $data['anno_junior'];
            }

            if(isset($data['anno_county_only'])){
                $setData['anno_county_only'] = $data['anno_county_only'];
            }

            if(isset($data['anno_school_id'])){
                $setData['anno_school_id'] = $data['anno_school_id'];
            }

            if(isset($data['anno_county_id'])){
                $setData['anno_county_id'] = $data['anno_county_id'];
            }

            if(isset($data['anno_site_h'])){
                $setData['anno_site_h'] = $data['anno_site_h'];
            }

            if(isset($data['anno_site_r'])){
                $setData['anno_site_r'] = $data['anno_site_r'];
            }

            if(isset($data['anno_site_s'])){
                $setData['anno_site_s'] = $data['anno_site_s'];
            }

            if(isset($data['anno_type']) && ($data['anno_type'] == $typeAnno->OriginalSiteAnno || $data['anno_type'] == $typeAnno->Entry || $data['anno_type'] == $typeAnno->InTime)){
                // 全站網站 8,9,10   type 1 4 5
                $setData['anno_category_id'] = null;
            }
            elseif( ($data['anno_type'] == $typeAnno->RouteAnno || $data['anno_type'] == $typeAnno->Description) && $data['anno_category_id'] == null )
            {
                // 學生資源網  anno_type:2 or 3  anno_category_id:null
                $setData['anno_category_id'] = null;
            }
            elseif(($data['anno_type'] == $typeAnno->RouteAnno || $data['anno_type'] == $typeAnno->Description) && ($data['anno_category_id']) != null)
            {
                // 人力資源網或者學籍管理服務  anno_type:2 anno_category_id:not null
                $setData['anno_category_id'] = $data['anno_category_id'];
            }

            // 更新時間
            $setData['updated_at']                          = $query->raw('NOW()');
            $query->where('anno_id','=',$data['anno_id']);
            $tId = $query->update($setData);
            $setData = NULL; unset($setData); // 清除

            CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
            return $tId;
        }else{
            return false;
        }
    }

    // 刪除公告
    public static function del($data = NULL)
    {
        if(isset($data['anno_id']) && $data['anno_id']){
            $query          = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);

            $query->where('anno_id','=',$data['anno_id']);
            $tId = $query->delete();

            CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
            return $tId;
        }else{
            return false;
        }
    }

    public static function cleanPublishedSites( $getData, $tempArr )
    {   // 入口公告 即時訊息使用
        // 清光該網站發布狀態
        $data       = $getData;
        $tArrSite   = $tempArr;
        $typeAnno   = self::typeAnno();
        $siteAnno   = self::siteAnno();
        // 處理要發佈的網站別
        foreach ($tArrSite as $key1 => $site) {
            if(isset($data['anno_type']) && $data['anno_publish'] == 1 && $data['anno_type'] == $typeAnno->Entry)
            {
                switch($site) {
                    case $siteAnno->H_Anno:    // 人力網
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_h','=',1);
                        $query->update(['anno_site_h' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_r','=',0);
                        $query->where('anno_site_s','=',0);
                        $query->update(['anno_publish' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        break;
                    case $siteAnno->R_Anno:    // 學籍網
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_r','=',1);
                        $query->update(['anno_site_r' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_h','=',0);
                        $query->where('anno_site_s','=',0);
                        $query->update(['anno_publish' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        break;
                    case $siteAnno->S_Anno:    // 學生網
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_s','=',1);
                        $query->update(['anno_site_s' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_h','=',0);
                        $query->where('anno_site_r','=',0);
                        $query->update(['anno_publish' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        break;
                }
            }
            elseif(isset($data['anno_type']) && $data['anno_publish'] == 1 && $data['anno_type'] == $typeAnno->InTime)
            {
                switch($site) {
                    case $siteAnno->H_Anno:    // 人力網
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_h','=',1);
                        $query->update(['anno_site_h' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_r','=',0);
                        $query->where('anno_site_s','=',0);
                        $query->update(['anno_publish' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        break;
                    case $siteAnno->R_Anno:    // 學籍網
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_r','=',1);
                        $query->update(['anno_site_r' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_h','=',0);
                        $query->where('anno_site_s','=',0);
                        $query->update(['anno_publish' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        break;
                    case $siteAnno->S_Anno:    // 學生網
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_s','=',1);
                        $query->update(['anno_site_s' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
                        $query->where('anno_type','=',$data['anno_type']);
                        $query->where('anno_publish','=',$data['anno_publish']);
                        $query->where('anno_site_h','=',0);
                        $query->where('anno_site_r','=',0);
                        $query->update(['anno_publish' => 0]);
                        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
                        break;
                }
            }
        }
    }

    public static function getCleanPublish($data = NULL)
    {
        $query = CDB::connection(self::$dbMajorName,self::$dbGroupName)->table(self::$tableName);
        $typeAnno = self::typeAnno();

        if(isset($data['anno_type']) && $data['anno_publish'] == 1 && $data['anno_type'] == $typeAnno->Entry)
        {
            $query->where('anno_type','=',$data['anno_type']);
            $query->where('anno_publish','=',$data['anno_publish']);
        }
        elseif(isset($data['anno_type']) && $data['anno_publish'] == 1 && $data['anno_type'] == $typeAnno->InTime)
        {
            $query->where('anno_type','=',$data['anno_type']);
            $query->where('anno_publish','=',$data['anno_publish']);
        }

        $content = $query->get();
        CDB::disconnect(self::$dbMajorName,self::$dbGroupName);
        return ($content->first())? $content:false;
    }
}
