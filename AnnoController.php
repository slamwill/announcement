<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\FaqController;
use App\Models\HrdbMain\AnnoModel;
use App\Models\HrdbMain\AnnoAttachmentModel;
use App\Models\HrdbMain\AnnoVisitorModel;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\StartController;
use App\Http\Controllers\Common\ManageController;
use App\Models\HrdbMain\ManagerDataModel;
use App\Models\HrdbMain\ManagerExtraModel;
use App\Models\HrdbMain\EmployeeDataModel;
use App\Models\HrdbMain\SystemCodeModel; //常用代碼
use App\Models\HrdbMain\SystemFunctionModel; //常用代碼
use App\Models\HrdbMain\FaqReportModel;
use App\Models\HrdbMain\CityDataModel;
use App\Models\HrdbMain\SchoolDataModel;
use App\Models\HrdbMain\SchoolGroupModel;
use App\Models\HrdbMain\SystemGroupModel;
use App\Models\HrdbMain\FaqCategoryStructureModel;
use App\Library\AuthLib;
use App\Library\DateLib;
use App\Library\CommonLib;
use App\Library\FileLib;
use App\Library\MailLib;
use Carbon\Carbon;

use Session;
use Validator;
use Redirect;


class AnnoController extends Controller
{
    // 附件資料夾名稱
    protected static $anno_attachment_folder = 'anno_attachment_folder';

    /**
     * 處理搜尋參數 & Session 之間的關係，分頁不用傳
     * @param string $attr_name 資料名稱
     * @return None, 因為都存在 Session 裡
     */
    public function searchCondProcessor($attr_name = NULL, $req = NULL){
        if(Session::get('keyword_Manager.' . $attr_name) != $req->$attr_name)
        {
            Session::put('keyword_Manager.' . $attr_name, strval($req->$attr_name));
        }
    }

    /**
     * 公告選單列表 (最新消息)
     * @param  string       $code    [description]
     * @return view                  common.manage.anno_message
     */
    public function listAnnoNews($code = NULL, Request $request){
        $typeAnno = AnnoModel::typeAnno();
        $typeAnno = $typeAnno->NewSiteAnno;
        $data = $this->listAnno($typeAnno, $request);
        return view('common.manage.anno_add_form', $data);
    }

    /**
     * 公告選單列表 (入口公告)
     * @param  string       $code    [description]
     * @return view                  common.manage.anno_message
     */
    public function listAnnoEntry($code = NULL, Request $request){
        $typeAnno = AnnoModel::typeAnno();
        $typeAnno = $typeAnno->Entry;
        $data = $this->listAnno($typeAnno, $request);
        return view('common.manage.anno_message', $data);
    }

    /**
     * 公告選單列表 (即時訊息)
     * @param  string       $code    [description]
     * @return view                  common.manage.anno_message
     */
    public function listAnnoIntime($code = NULL, Request $request){
        $typeAnno = AnnoModel::typeAnno();
        $typeAnno = $typeAnno->InTime;
        $data = $this->listAnno($typeAnno, $request);
        return view('common.manage.anno_message', $data);
    }

    /**
     * 公告選單列表
     * @param  string       $code    [用來分辨以下公告類型]
     * 公告分類說明
     * type1, type2: 頁面公告-{全站公告, 功能公告} (沒用到)
     * type3: 功能說明 -> 各個網站&各個路徑上的功能說明
     * type4: 入口公告
     * type5: 即時訊息
     * type6: 最新消息 -> 選網站{人力, 學籍, 學生}(可以多選) -> 選權限{教育部:1, 縣市端:2, 學校端:3}(不可以多選)
     * @return view                  common.manage.anno_list
     */
    public function listAnno($code = NULL, Request $request){
        $defaultGroupCode = Session::get('User.defaultGroupCode');

        $typeAnno       = AnnoModel::typeAnno();
        $siteAnno       = AnnoModel::siteAnno();
        $typePermission = AnnoModel::typePermission();
        $educationAnno  = AnnoModel::educationAnno();
        $typeAttr       = ManagerDataModel::typeAttr();

        $id_identity        = Session::get('User.id_identity');
        $anno_manager_id    = Session::get('User.manager_id');

        // 組織 query 條件
        $listNum  = 8;
        $getData                = [
            'pageMode'          =>  'normal',
            'listNum'           =>  $listNum,
            'order'             =>  ['anno_id'=>'DESC'],
        ];

        /*** 控制公告類型  ***/
        $view_anno_type = $code;

        $getData['anno_type']       = $view_anno_type;
        $getData['anno_manager_id'] = $anno_manager_id;

        // 最新消息
        if($view_anno_type == $typeAnno->NewSiteAnno)
        {
            $getData['order_sticky']  =  ['sticky'=>'1'];
            if($id_identity == $typeAttr->County || $id_identity == $typeAttr->School)
            {
                if($defaultGroupCode == $siteAnno->H_Anno)
                {
                    $getData['anno_site_h'] = '1';
                }
                elseif($defaultGroupCode == $siteAnno->R_Anno)
                {
                    $getData['anno_site_r'] = '1';
                }
                elseif($defaultGroupCode == $siteAnno->S_Anno)
                {
                    $getData['anno_site_s'] = '1';
                }
            }
        }
        elseif($view_anno_type == $typeAnno->Entry)  // 入口公告
        {
            // do noop
        }
        elseif($view_anno_type == $typeAnno->InTime)  // 即時訊息
        {
            // do noop
        }

        $tData = AnnoModel::listData($getData);

        $data['data']               = $tData;
        $data['Manage']             = $typeAttr->Manage;
        $data['Edu']                = $typeAttr->Edu;
        $data['County']             = $typeAttr->County;
        $data['School']             = $typeAttr->School;
        $data['County_School']      = $typePermission->County_School;
        $data['Entry']              = $typeAnno->Entry;
        $data['InTime']             = $typeAnno->InTime;
        $data['PrimaryAnno']        = $educationAnno->PrimaryAnno;
        $data['JuniorAnno']         = $educationAnno->JuniorAnno;
        $data['PrimaryJuniorAnno']  = $educationAnno->PrimaryJuniorAnno;
        $data['H_Anno']             = $siteAnno->H_Anno;
        $data['R_Anno']             = $siteAnno->R_Anno;
        $data['S_Anno']             = $siteAnno->S_Anno;

        $tData            = NULL; unset($tData);
        $tArr             = NULL; unset($tArr);
        $tLocStr          = NULL; unset($tLocStr);

        switch($defaultGroupCode){
            case $siteAnno->H_Anno:
                if($view_anno_type == $typeAnno->NewSiteAnno)
                {
                    return $data;
                }
                elseif($view_anno_type == $typeAnno->Entry)
                {
                    $data['view_anno_type'] = $view_anno_type;
                    return $data;
                }
                elseif($view_anno_type == $typeAnno->InTime)
                {
                    $data['view_anno_type'] = $view_anno_type;
                    return $data;
                }
                break;

            case $siteAnno->R_Anno:
                if($view_anno_type == $typeAnno->NewSiteAnno)
                {
                    return $data;
                }
                break;

            case $siteAnno->S_Anno:
                if($view_anno_type == $typeAnno->NewSiteAnno)
                {
                    return $data;
                }
                break;
        }
    }

    /**
     * 公告選單列表 新增/修改頁面 (最新消息)
     * @param  string  $action add/up
     * @param  integer $id     欲修改的資料id
     * @param  integer $param1 欲修改資料的類型
     * @param  string  $token  驗證用token
     * @return view            common.manage.list_form
     */
    function formAnnoNews($action = NULL, $id, $token = NULL, $param1) {
        $data = $this->formAnno($action, $id, $token, $param1);
        return view('common.manage.anno_up_form', $data);
    }

    /**
     * 公告選單列表 新增/修改頁面 (入口公告)
     * @param  string  $action add/up
     * @param  integer $id     欲修改的資料id
     * @param  integer $param1 欲修改資料的類型
     * @param  string  $token  驗證用token
     * @return view            common.manage.list_form
     */
    function formAnnoEntry($action = NULL, $id, $token = NULL, $param1) {
        $data = $this->formAnno($action, $id, $token, $param1);
        return view('common.manage.anno_up_message', $data);
    }

    /**
     * 公告選單列表 新增/修改頁面 (即時訊息)
     * @param  string  $action add/up
     * @param  integer $id     欲修改的資料id
     * @param  integer $param1 欲修改資料的類型
     * @param  string  $token  驗證用token
     * @return view            common.manage.list_form
     */
    function formAnnoInTime($action = NULL, $id, $token = NULL, $param1) {
        $data = $this->formAnno($action, $id, $token, $param1);
        return view('common.manage.anno_up_message', $data);
    }

    /**
     * 公告選單列表 新增/修改頁面
     * @param  string  $action add/up
     * @param  integer $id     欲修改的資料id
     * @param  integer $param1 欲修改資料的類型
     * @param  string  $token  驗證用token
     * @return $data
     */
    function formAnno($action = NULL, $id, $token = NULL, $param1)
    {
        $typeAnno           = AnnoModel::typeAnno();
        $siteAnno           = AnnoModel::siteAnno();
        $typePermission     = AnnoModel::typePermission();
        $educationAnno      = AnnoModel::educationAnno();
        $typeAttr           = ManagerDataModel::typeAttr();

        switch($token){
            case 'descriptionUp':   // 功能說明修改
                $action = 'descriptionUp';
                break;

            case 'entryAnnoUp':   // 入口公告修改
                $action = 'entryAnnoUp';
                break;

            case 'inTimeAnnoUp':   // 即時訊息修改
                $action = 'inTimeAnnoUp';
                break;

            case 'newAnnoUp':   // 最新消息修改
                $action = 'newAnnoUp';
                break;
        }

        switch($action){
            case 'add':
                break;

            case 'descriptionUp':
            case 'entryAnnoUp':
            case 'inTimeAnnoUp':
            case 'newAnnoUp':
                $annoDetailData = AnnoModel::detail(['anno_id'=>$id]);
                Session::put('annoDetailData',(array)$annoDetailData);

                if($annoDetailData->anno_type == $typeAnno->RouteAnno || $annoDetailData->anno_type == $typeAnno->Description)
                {   //處理公告分類
                    // 路徑公告:anno_type 2   路徑功能說明:anno_type 3
                    $data['functionStructure'] = $this->getCategoryStructureForm(
                        $faq_categories[]       = [
                            'anno_category_id'  =>  $annoDetailData->anno_category_id,
                        ]
                    );
                    $faq_categories = NULL; unset($faq_categories);
                }
                elseif($annoDetailData->anno_type == $typeAnno->OriginalSiteAnno || $annoDetailData->anno_type == $typeAnno->Entry || $annoDetailData->anno_type == $typeAnno->InTime || $annoDetailData->anno_type == $typeAnno->NewSiteAnno)
                {   // 一般公告之 全站公告anno_type 1, 入口公告anno_type 4, 即時訊息anno_type 5, 最新消息anno_type 6    網站路徑的解析
                    $data['functionStructure'] = NULL;

                    if($token == 'newAnnoUp')
                    {
                        $data['anno_receiver_id']       = 0;
                        $data['anno_edu']               = 0;
                        // 接收者
                        if($annoDetailData->anno_county_only == 1 && $annoDetailData->anno_primary == 1 && $annoDetailData->anno_junior == 1)
                        {   // 全部 縣市端和學校端
                            $anno_receiver_id = $typePermission->County_School;
                        }
                        if($annoDetailData->anno_county_only == 1 && $annoDetailData->anno_primary == 0 && $annoDetailData->anno_junior == 0)
                        {   // 縣市端
                            $anno_receiver_id = $typeAttr->County;
                        }
                        if($annoDetailData->anno_county_only == 0 &&( $annoDetailData->anno_primary == 1 || $annoDetailData->anno_junior == 1))
                        {   // 學校端
                            $anno_receiver_id = $typeAttr->School;
                        }

                        // 學等
                        if($annoDetailData->anno_primary == 1 & $annoDetailData->anno_junior== 0)
                        {   // 小學
                            $anno_edu = $educationAnno->PrimaryAnno;
                        }
                        if($annoDetailData->anno_junior == 1 & $annoDetailData->anno_primary== 0)
                        {   // 中學
                            $anno_edu = $educationAnno->JuniorAnno;
                        }
                        if($annoDetailData->anno_primary == 1 && $annoDetailData->anno_junior == 1)
                        {   // 中/小學
                            $anno_edu = $educationAnno->PrimaryJuniorAnno;
                        }
                        if(isset($anno_receiver_id))
                        {
                            $data['anno_receiver_id'] = $anno_receiver_id;
                        }
                        if(isset($anno_edu))
                        {
                            $data['anno_edu'] = $anno_edu;
                        }

                        // 取附加檔案
                        $tData = [
                            'select'        => 'anno_id, anno_path, anno_file_name',
                            'anno_id'     => $id
                        ];

                        $tAttachmentData = AnnoAttachmentModel::listData($tData);

                        if(isset($tAttachmentData) && $tAttachmentData != ''){
                            foreach($tAttachmentData as $key => $val){
                                if(isset($tAttachmentData)){
                                    $arrAttachmentData[$key]['fileContent'] = FileLib::getContent($val->anno_path);
                                    $arrAttachmentData[$key]['fileWebPath'] = FileLib::getWebPath($val->anno_path);
                                    $arrAttachmentData[$key]['file_original_name'] = $val->anno_file_name;
                                }
                            }
                            $data['arrAttachmentData'] = $arrAttachmentData;
                        }

                        $data['Manage']             = $typeAttr->Manage;
                        $data['Edu']                = $typeAttr->Edu;
                        $data['County']             = $typeAttr->County;
                        $data['School']             = $typeAttr->School;
                        $data['County_School']      = $typePermission->County_School;
                        $data['Entry']              = $typeAnno->Entry;
                        $data['InTime']             = $typeAnno->InTime;
                        $data['PrimaryAnno']        = $educationAnno->PrimaryAnno;
                        $data['JuniorAnno']         = $educationAnno->JuniorAnno;
                        $data['PrimaryJuniorAnno']  = $educationAnno->PrimaryJuniorAnno;
                    }

                    if($token == 'newAnnoUp' || $token == 'entryAnnoUp' || $token == 'inTimeAnnoUp')
                    {   // 給newAnnoUp使用 記錄下 該被更改公告發佈在哪些網站之中 傳到前端以便呈現在checkbox中
                        $getSiteArr       =   [
                            'publish_H'   =>  '0',
                            'publish_R'   =>  '0',
                            'publish_S'   =>  '0',
                        ];

                        // 處理網站別
                        if($annoDetailData->anno_site_h == 1)
                        {
                            $getSiteArr['publish_H'] = $siteAnno->H_Anno;
                        }
                        if($annoDetailData->anno_site_r == 1)
                        {
                            $getSiteArr['publish_R'] = $siteAnno->R_Anno;
                        }
                        if($annoDetailData->anno_site_s == 1)
                        {
                            $getSiteArr['publish_S'] = $siteAnno->S_Anno;
                        }
                        $data['H_Anno']     = $siteAnno->H_Anno;
                        $data['R_Anno']     = $siteAnno->R_Anno;
                        $data['S_Anno']     = $siteAnno->S_Anno;
                    }

                    $tData = NULL; unset($tData);
                    $tAttachmentData = NULL; unset($tAttachmentData);
                    $arrAttachmentData = NULL; unset($arrAttachmentData);
                }
                $data['data']       = $annoDetailData;
                $data['getSiteArr'] = $getSiteArr;
                break;
        }

        $data['action']                 = $action;
        $data['screen']                 = 'normal';

        if($param1 == 'descriptionAdd' || $param1 == 'entryAnnoAdd' || $param1 == 'inTimeAnnoAdd' ) {
            $data['param1'] = $param1;
        }
        if($token == 'descriptionUp' || $token == 'entryAnnoUp' || $token == 'inTimeAnnoUp' || $token == 'newAnnoUp' ) {
            $data['param1'] = $token;
        }
        if($param1 == 'descriptionAdd' || $token == 'descriptionUp') {   // 功能說明
            $data['action'] = 'type3'; // type3 useing $param1 and token
            return view('common.manage.anno_description_form', $data);
        }
        elseif($token == 'newAnnoUp')
        {   // 最新消息
            return $data;
        }
        elseif($token == 'entryAnnoUp' || $token == 'inTimeAnnoUp')
        {
            $data['Entry'] = $typeAnno->Entry;
            $data['InTime'] = $typeAnno->InTime;
            return $data;
        }
    }

    /**
     * 刪除公告列表 (最新消息)
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return route to list table
     */
    function delAnnoNews($code, Request $request) {
        $tId = $this->delAnno($code, $request);
        if($tId == true)
        {
            return redirect()->back();
        }
        else{
            $request->session()->flash('msg',[trans('msg.E00000')]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * 刪除公告列表 (入口公告)
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return route to list table
     */
    function delAnnoEntry($code, Request $request) {
        $tId = $this->delAnno($code, $request);
        if($tId == true)
        {
            return redirect()->back();
        }
        else{
            $request->session()->flash('msg',[trans('msg.E00000')]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * 刪除公告列表 (即時訊息)
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return route to list table
     */
    function delAnnoInTime($code, Request $request) {
        $tId = $this->delAnno($code, $request);
        if($tId == true)
        {
            return redirect()->back();
        }
        else{
            $request->session()->flash('msg',[trans('msg.E00000')]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * 刪除公告列表
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return boolean
     */
    function delAnno($code = NULL, Request $request)
    {
        $getData = [
          'anno_id'     => $request->id,
        ];

        $tAttachmentData = AnnoAttachmentModel::listData($getData);
        if($tAttachmentData){
            foreach ($tAttachmentData as $key1 => $value1) {
                $tId = FileLib::delete($value1->anno_path, 'ftp', 'get');
                $tId = AnnoAttachmentModel::del($getData);
            }                    
        }

        AnnoVisitorModel::del(['anno_id'=>$request->id]);
        $tId = AnnoModel::del(['anno_id'=>$request->id]);

        if($tId){
            return true;
        }
        else{
            $request->session()->flash('msg',[trans('msg.E00000')]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * 新增公告列表 (最新消息)
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return route to list table
     */
    function addAnnoNews($code, Request $request) {
        $tId = $this->addAnno($code, $request);

        if($tId == true)
        {
            return redirect()->route('code.list',['code'=>$code, 'id'=>'addAnno']);
        }
        else{
            $request->session()->flash('msg',[trans('msg.E00000')]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * 新增公告列表 (入口公告)
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return route to list table
     */
    function addAnnoEntry($code, Request $request) {
        $tId = $this->addAnno($code, $request);

        if($tId == true)
        {
            return redirect()->route('code.list',['code'=>$code, 'id'=>'addAnno']);
        }
        else{
            $request->session()->flash('msg',[trans('msg.E00000')]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * 新增公告列表 (即時訊息)
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return route to list table
     */
    function addAnnoInTime($code, Request $request) {
        $tId = $this->addAnno($code, $request);

        if($tId == true)
        {
            return redirect()->route('code.list',['code'=>$code, 'id'=>'addAnno']);
        }
        else{
            $request->session()->flash('msg',[trans('msg.E00000')]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * 新增公告列表
     * @param string  $code    [description]
     * @param Request $request [description]
     * @param Request $request->param1 路徑功能說明使用的參數
     *                param1如果是staAdd 把
     * @return boolean
     */
    function addAnno($code, Request $request) {
        $typeAnno       = AnnoModel::typeAnno();
        $typePermission = AnnoModel::typePermission();
        $educationAnno  = AnnoModel::educationAnno();
        $siteAnno       = AnnoModel::siteAnno();
        $typeAttr       = ManagerDataModel::typeAttr();
        
        $id_identity        = Session::get('User.id_identity');
        $id_city            = Session::get('User.id_city');
        $school_id          = Session::get('User.school_id');
        $anno_manager_id    = Session::get('User.manager_id');

        $validator   = Validator::make(
            $request->all(),
            [
                'anno_content'                =>  'required|string',
                'system_function_level_root'  =>  'nullable|integer',
                'final_category_id'           =>  'nullable|integer',
            ],
            [
                'anno_title.required'         =>  trans('validation.required',['attribute'=>trans('common.title')]),
                'anno_title.string'           =>  trans('validation.string',['attribute'=>trans('common.title')]),
                'anno_content.required'       =>  trans('validation.required',['attribute'=>trans('common.content')]),
                'anno_content.string'         =>  trans('validation.string',['attribute'=>trans('common.content')]),
            ]
        );

        if($validator->fails()){   // 基本驗證失敗
            $request->session()->flash('msg',$validator->errors()->all());
            return redirect()->back()->withInput();
        }
        else{
            $setData                  = [
                'anno_type'           =>  $request->system_function_level_root,
                'anno_category_id'    =>  $request->final_category_id,
                'anno_id'             =>  $request->anno_id,
                'anno_title'          =>  $request->anno_title,
                'anno_content'        =>  $request->anno_content,
                'anno_start_date'     =>  $request->anno_start_date,
                'anno_end_date'       =>  $request->anno_end_date,
                'anno_publish'        =>  $request->anno_publish,
                // 'anno_publish'        =>  '0',
            ];

            $defaultGroupCode = Session::get('User.defaultGroupCode');

            switch($request->param1) {
                case 'descriptionAdd':    // 功能說明
                    $setData['anno_type'] = $typeAnno->Description;
                    break;
                case 'entryAnnoAdd':    // 入口公告
                    $setData['anno_type'] = $typeAnno->Entry;
                    $upData['anno_type']  = $typeAnno->Entry;
                    break;
                case 'inTimeAnnoAdd':    // 即時公告
                    $setData['anno_type'] = $typeAnno->InTime;
                    $upData['anno_type']  = $typeAnno->InTime;
                    break;
                case 'newAnnoAdd':    // 最新消息
                    $setData['anno_type'] = $typeAnno->NewSiteAnno;
                    $upData['anno_type']  = $typeAnno->NewSiteAnno;
                    break;
            }

            if($request->anno_publish == 'on') {  // 要發佈, 目前只有入口公告 即時訊息會用到
                $setData['anno_publish'] = 1;
                if($request->param1 == 'entryAnnoAdd' || $request->param1 == 'inTimeAnnoAdd')
                {
                    $upData['anno_publish']  = 1;

                    if($request->selectAll == 'on') {
                        $tArr = array($siteAnno->H_Anno, $siteAnno->R_Anno, $siteAnno->S_Anno);
                    }
                    else
                    {
                        $tArr = $request->currSite;
                    }

                    // 清除發布過的網站
                    $tUp        = AnnoModel::cleanPublishedSites($upData, $tArr);   // $upData including  anno_type & anno_publish
                    $selectAll  = $request->selectAll;
                    $currSite   = $request->currSite;
                    $setData    = self::selectMultiSite($selectAll, $currSite, $setData);
                }
            }

            if($request->anno_sticky == 'on')  // type6 會用到
            {   // 置頂文
                $setData['anno_sticky'] = 1;
            }
            else
            {
                $setData['anno_sticky'] = 0;
            }

            if($request->param1 == 'newAnnoAdd')  // type6 會用到
            {
                $setData['anno_authority'] = $id_identity;
                if($id_identity == $typeAttr->Manage || $id_identity == $typeAttr->Edu)
                {   // 發送者是管理者或者教育部
                    if($request->anno_receiver_id == $typePermission->County_School)
                    {   // 發送給全部 縣市端&學校端
                        $setData['anno_county_only']    = 1;
                        $setData['anno_primary']        = 1;
                        $setData['anno_junior']         = 1;
                    }
                    elseif($request->anno_receiver_id == $typeAttr->County)
                    {   // 發送給全部 縣市端
                        $setData['anno_county_only']    = 1;
                        $setData['anno_primary']        = 0;
                        $setData['anno_junior']         = 0;
                    }
                    elseif($request->anno_receiver_id == $typeAttr->School)
                    {   // 發送給全部 學校端
                        // 判斷 小學 中學 中/小學
                        $setData['anno_county_only']    = 0;
                        if($request->anno_edu == $educationAnno->PrimaryAnno)
                        {   // 小學
                            $setData['anno_primary']        = 1;
                            $setData['anno_junior']         = 0;
                        }
                        elseif($request->anno_edu == $educationAnno->JuniorAnno)
                        {   // 中學
                            $setData['anno_primary']        = 0;
                            $setData['anno_junior']         = 1;
                        }
                        elseif($request->anno_edu == $educationAnno->PrimaryJuniorAnno)
                        {   // 中/小學
                            $setData['anno_primary']        = 1;
                            $setData['anno_junior']         = 1;
                        }
                    }
                }
                elseif($id_identity == $typeAttr->County)
                {   // 發送者是縣市端
                    $setData['anno_county_id']         = $id_city;
                    // 判斷 小學 中學 中/小學
                    $setData['anno_county_only']    = 0;
                    if($request->anno_edu == $educationAnno->PrimaryAnno)
                    {   // 小學
                        $setData['anno_primary']        = 1;
                        $setData['anno_junior']         = 0;
                    }
                    elseif($request->anno_edu == $educationAnno->JuniorAnno)
                    {   // 中學
                        $setData['anno_primary']        = 0;
                        $setData['anno_junior']         = 1;
                    }
                    elseif($request->anno_edu == $educationAnno->PrimaryJuniorAnno)
                    {   // 中/小學
                        $setData['anno_primary']        = 1;
                        $setData['anno_junior']         = 1;
                    }
                }
                elseif($id_identity == $typeAttr->School)
                {   // 發送者是縣市端
                    $setData['anno_school_id']         = $school_id;  //該學校的ID
                    $setData['anno_county_only']    = 0;
                    $setData['anno_primary']        = 0;
                    $setData['anno_junior']         = 0;
                }

                // 處理網站
                if(($id_identity == $typeAttr->Manage || $id_identity == $typeAttr->Edu)){
                    $selectAll = $request->selectAll;
                    $currSite = $request->currSite;
                    $setData = self::selectMultiSite($selectAll, $currSite, $setData);
                }
                elseif(($id_identity == $typeAttr->County || $id_identity == $typeAttr->School) && $defaultGroupCode == $siteAnno->H_Anno){
                    // 縣市端or學校端  在人力網
                    $setData['anno_site_h'] = 1;
                    $setData['anno_site_r'] = 0;
                    $setData['anno_site_s'] = 0;
                }
                elseif(($id_identity == $typeAttr->County || $id_identity == $typeAttr->School) && $defaultGroupCode == $siteAnno->R_Anno){
                    // 縣市端or學校端  在學籍網
                    $setData['anno_site_h'] = 0;
                    $setData['anno_site_r'] = 1;
                    $setData['anno_site_s'] = 0;
                }
                elseif(($id_identity == $typeAttr->County || $id_identity == $typeAttr->School) && $defaultGroupCode == $siteAnno->S_Anno){
                    // 縣市端or學校端  在學生網
                    $setData['anno_site_h'] = 0;
                    $setData['anno_site_r'] = 0;
                    $setData['anno_site_s'] = 1;
                }
            }

            $setData['anno_manager_id'] = $anno_manager_id;

            $tArr = NULL; unset($tArr);
            $tData = NULL; unset($tData);
            $systemFunctionId = NULL; unset($systemFunctionId);

            Session::put('detailData',$request->all());

            $anno_id = AnnoModel::add($setData);

            // 再處理上傳檔案
            $attachId = 0;  // 如果沒有上傳檔案 用預設值0
            if(isset($request->image) && $request->image != ''){
                foreach($request->image as $key => $file){
                    $fileData = FileLib::upload('file',$file);
                    if($fileData->Result == 1){
                        $files[$key]['file_loc']         = $fileData->path;
                        $files[$key]['ori_filename']     = $fileData->originalName;
                        $files[$key]['ext']              = $fileData->fileExtension;
                        $newFullPath = FileLib::move($fileData->path, self::$anno_attachment_folder, 'ftp');

                        $setData                = [
                            'anno_id'           =>  $anno_id,
                            'anno_path'         =>  $newFullPath,
                            'anno_file_name'    =>  $fileData->originalName,
                        ];
                        $anno_attachment_id = AnnoAttachmentModel::add($setData);
                    }
                }
            }

            if($anno_id){
                Session::forget('detailData');
                if($request->param1 == "descriptionAdd") {
                    return redirect()->route('code.list',['code'=>$code,'id'=>'func_description_version']);
                }
                elseif($request->param1 == "entryAnnoAdd") {
                    return true;
                }
                elseif($request->param1 == "inTimeAnnoAdd") {
                    return true;
                }
                elseif($request->param1 == "newAnnoAdd") {
                    return true;
                }
                else {
                    return redirect()->route('code.list',['code'=>$code]);
                }
            }
            else {
                $request->session()->flash('msg',[trans('msg.E00000')]);
                return redirect()->back()->withInput();
            }
        }
    }

    /**
     * 修改公告列表 (最新消息)
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return route to list table
     */
    function upAnnoNews($code, Request $request) {
        $tId = $this->upAnno($code, $request);
        if($tId == true)
        {
            return redirect()->route('code.list',['code'=>$code, 'id'=>'upAnno']);
        }
        else{
            $request->session()->flash('msg',[trans('msg.E00000')]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * 修改公告列表 (入口公告)
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return route to list table
     */
    function upAnnoEntry($code, Request $request) {
        $tId = $this->upAnno($code, $request);
        if($tId == true)
        {
            return redirect()->route('code.list',['code'=>$code, 'id'=>'upAnno']);
        }
        else{
            $request->session()->flash('msg',[trans('msg.E00000')]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * 修改公告列表 (即時訊息)
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return route to list table
     */
    function upAnnoInTime($code, Request $request) {
        $tId = $this->upAnno($code, $request);
        if($tId == true)
        {
            return redirect()->route('code.list',['code'=>$code, 'id'=>'upAnno']);
        }
        else{
            $request->session()->flash('msg',[trans('msg.E00000')]);
            return redirect()->back()->withInput();
        }
    }

    /**
     * 修改公告列表
     * @param string  $code    [description]
     * @param Request $request [description]
     * @return boolean
     */
    function upAnno($code, Request $request)
    {
        $typeAnno           = AnnoModel::typeAnno();
        $siteAnno           = AnnoModel::siteAnno();
        $educationAnno      = AnnoModel::educationAnno();
        $typePermission     = AnnoModel::typePermission();
        $typeAttr           = ManagerDataModel::typeAttr();
        $defaultGroupCode   = Session::get('User.defaultGroupCode');

        $validator      = Validator::make(
            $request->all(),
            [
                'anno_content'                =>  'required|string',
                'system_function_level_root'  =>  'nullable|integer',
                'final_category_id'           =>  'nullable|integer',
            ],
            [
                'anno_title.required'         =>  trans('validation.required',['attribute'=>trans('common.title')]),
                'anno_title.string'           =>  trans('validation.string',['attribute'=>trans('common.title')]),
                'anno_content.required'       =>  trans('validation.required',['attribute'=>trans('common.content')]),
                'anno_content.string'         =>  trans('validation.string',['attribute'=>trans('common.content')]),
            ]
        );

        if($validator->fails()){    // 基本驗證失敗
            $request->session()->flash('msg',$validator->errors()->all());
            return redirect()->back()->withInput();
        }
        else{
            $setData                  = [
                'anno_id'             =>  $request->id,
                'anno_type'           =>  $request->system_function_level_root,
                'anno_category_id'    =>  $request->final_category_id,
                'anno_title'          =>  $request->anno_title,
                'anno_content'        =>  $request->anno_content,
                'system_function_id'  =>  '12',
                'anno_start_date'     =>  $request->anno_start_date,
                'anno_end_date'       =>  $request->anno_end_date,
                'anno_sticky'         =>  $request->anno_sticky,
                'anno_publish'        =>  '0',
            ];

            if($request->param1 == "descriptionUp") {  // 功能說明 type是3
                $setData['anno_type'] = $typeAnno->Description;
            }
            elseif($request->param1 == "entryAnnoUp") {  // 功能說明 type是4
                $setData['anno_type'] = $typeAnno->Entry;
            }
            elseif($request->param1 == "inTimeAnnoUp") {  // 功能說明 type是5
                $setData['anno_type'] = $typeAnno->InTime;
            }
            elseif($request->param1 == "newAnnoUp") {  // 最新消息 type是6
                $setData['anno_type'] = $typeAnno->NewSiteAnno;

                if($request->anno_publish == 'on')
                {
                    $setData['anno_publish'] = 1;
                }

                if($request->anno_sticky == 'on')
                {
                    $setData['anno_sticky'] = 1;
                }
                else
                {
                    $setData['anno_sticky'] = 0;
                }

                $id_identity = Session::get('User.id_identity');
                // 如果需要的話在處理 教育部對   縣市端 學校端 局部發佈公告
                $id_city     = Session::get('User.id_city');
                $school_id   = Session::get('User.school_id');

                $setData['anno_authority'] = $id_identity;

                if($id_identity == $typeAttr->Manage || $id_identity == $typeAttr->Edu)
                {   // 發送者是管理者或者教育部
                    if($request->anno_receiver_id == $typePermission->County_School)
                    {   // 發送給全部 縣市端&學校端
                        $setData['anno_county_only']    = 1;
                        $setData['anno_primary']        = 1;
                        $setData['anno_junior']         = 1;
                    }
                    elseif($request->anno_receiver_id == $typeAttr->County)
                    {   // 發送給全部 縣市端
                        $setData['anno_county_only']    = 1;
                        $setData['anno_primary']        = 0;
                        $setData['anno_junior']         = 0;
                    }
                    elseif($request->anno_receiver_id == $typeAttr->School)
                    {   // 發送給全部 學校端
                        // 判斷 小學 中學 中/小學
                        $setData['anno_receiver_id']    = $typeAttr->School;
                        $setData['anno_county_only']    = 0;
                        if($request->anno_edu == $educationAnno->PrimaryAnno)
                        {   // 小學
                            $setData['anno_primary']        = 1;
                            $setData['anno_junior']         = 0;
                            $setData['anno_edu']            = $educationAnno->PrimaryAnno;
                        }
                        elseif($request->anno_edu == $educationAnno->JuniorAnno)
                        {   // 中學
                            $setData['anno_primary']        = 0;
                            $setData['anno_junior']         = 1;
                        }
                        elseif($request->anno_edu == $educationAnno->PrimaryJuniorAnno)
                        {   // 中/小學
                            $setData['anno_primary']        = 1;
                            $setData['anno_junior']         = 1;
                        }
                    }
                }
                elseif($id_identity == $typeAttr->County)
                {   // 發送者是縣市端
                    $setData['anno_county_id']     = $id_city;
                    // 判斷 小學 中學 中/小學
                    $setData['anno_county_only']    = 0;
                    if($request->anno_edu == $educationAnno->PrimaryAnno)
                    {   // 小學
                        $setData['anno_primary']        = 1;
                        $setData['anno_junior']         = 0;
                    }
                    elseif($request->anno_edu == $educationAnno->JuniorAnno)
                    {   // 中學
                        $setData['anno_primary']        = 0;
                        $setData['anno_junior']         = 1;
                    }
                    elseif($request->anno_edu == $educationAnno->PrimaryJuniorAnno)
                    {   // 中/小學
                        $setData['anno_primary']        = 1;
                        $setData['anno_junior']         = 1;
                    }
                }
                elseif($id_identity == $typeAttr->School)
                {   // 發送者是學校端
                    $setData['anno_school_id']         = $school_id;  //該學校的ID
                    $setData['anno_county_only']    = 0;
                    $setData['anno_primary']        = 0;
                    $setData['anno_junior']         = 0;
                }

                // 處理網站
                if(($id_identity == $typeAttr->Manage || $id_identity == $typeAttr->Edu)){
                    $selectAll  = $request->selectAll;
                    $currSite   = $request->currSite;
                    $setData    = self::selectMultiSite($selectAll, $currSite, $setData);
                }
                elseif(($id_identity == $typeAttr->County || $id_identity == $typeAttr->School) && $defaultGroupCode == $siteAnno->H_Anno){
                    // 縣市端or學校端  在人力網
                    $setData['anno_site_h'] = 1;
                    $setData['anno_site_r'] = 0;
                    $setData['anno_site_s'] = 0;
                }
                elseif(($id_identity == $typeAttr->County || $id_identity == $typeAttr->School) && $defaultGroupCode == $siteAnno->R_Anno){
                    // 縣市端or學校端  在學籍網
                    $setData['anno_site_h'] = 0;
                    $setData['anno_site_r'] = 1;
                    $setData['anno_site_s'] = 0;
                }
                elseif(($id_identity == $typeAttr->County || $id_identity == $typeAttr->School) && $defaultGroupCode == $siteAnno->S_Anno){
                    // 縣市端or學校端  在學生網
                    $setData['anno_site_h'] = 0;
                    $setData['anno_site_r'] = 0;
                    $setData['anno_site_s'] = 1;
                }
            }

            // 確保 type 4 5 重選 且發佈唯一性  如果確定發佈 把該type裡面其他已經發佈的 清除掉
            if(($request->param1 == "entryAnnoUp" || $request->param1 == "inTimeAnnoUp") && $request->anno_publish == 'on') {  // type 4 5
                $setData['anno_publish'] = 1;
                if($setData['anno_publish'] == 1) {
                    $upData['anno_publish']   = 1;
                    $upData['anno_type'] = $setData['anno_type'];

                    if($request->selectAll == 'on') {
                        $tArr = array($siteAnno->H_Anno, $siteAnno->R_Anno, $siteAnno->S_Anno);
                    }
                    else
                    {
                        $tArr = $request->currSite;
                    }

                    AnnoModel::cleanPublishedSites($upData, $tArr);   // $upData including

                    $selectAll = $request->selectAll;
                    $currSite = $request->currSite;
                    $setData = self::selectMultiSite($selectAll, $currSite, $setData);
                }
            }
            elseif($request->anno_publish == null || $request->anno_publish == '') {
                $setData['anno_publish'] = 0;
            }

            if($request->final_category_id != null) {  // the user changes the category id
                $setData['anno_category_id'] = $request->final_category_id;
            }
            elseif($request->current_anno_category_id != null) {
                // the user didnt change the category id so we use the current category id
                if( ($request->system_function_level_root == 1)  || ($request->system_function_level_1 == $siteAnno->S_Anno) ) {
                // 全站公告把category id改成null      學生資源網也把category id改成null 
                    $setData['anno_category_id'] = null;
                    $getData['anno_category_id'] = null;
                }
                else {
                    $setData['anno_category_id'] = $request->current_anno_category_id;
                }
            }

            $site = NULL; unset($site);
            $tArr = NULL; unset($tArr);
            $tData = NULL; unset($tData);
            $defaultGroupCode = NULL; unset($defaultGroupCode);

            $tId  = AnnoModel::up($setData);

            // 再處理上傳檔案
            $attachId = 0;  // 如果沒有上傳檔案 用預設值0
            if(isset($request->image) && $request->image != ''){
                // 如果有要更新上傳檔案  先刪除原先的檔案
                // 取附加檔案
                $getData = [
                  'anno_id'     => $request->id,
                ];

                $tAttachmentData = AnnoAttachmentModel::listData($getData);
                if($tAttachmentData){
                    foreach ($tAttachmentData as $key1 => $value1) {
                        $tId = FileLib::delete($value1->anno_path, 'ftp', 'get');
                        $tId = AnnoAttachmentModel::del($getData);
                    }                    
                }
        
                // 再把新的附加檔案加入
                foreach($request->image as $key => $file){
                    $fileData = FileLib::upload('file',$file);
                    if($fileData->Result == 1){
                        $newFullPath = FileLib::move($fileData->path, self::$anno_attachment_folder, 'ftp');
                        $setData                = [
                            'anno_id'           =>  $request->id,
                            'anno_path'         =>  $newFullPath,
                            'anno_file_name'    =>  $fileData->originalName,
                        ];
                        $tId = AnnoAttachmentModel::add($setData);
                    }
                }
             }

            if($tId){
                if($request->param1 == "descriptionUp") {
                    return redirect()->route('code.list',['code'=>$code,'id'=>'func_description_version']);
                }
                else {
                    return true;
                }
            }
            else{
                $request->session()->flash('msg',[trans('msg.E00000')]);
                return redirect()->back()->withInput();
            }
        }
    }

    /**
     * 新增選單
     * @param string  $code    [description]
     * @param Request $request [description]
     * 目前未使用到此function
     */
    function detailAnno($code, Request $request)
    {
        // no op
    }

    /**
     * 公告內文詳細頁檢視
     * @param Request $request [description]
     * @return $data 到 anno_all_sites
     * 目前未使用到此function(保留)
     */
    public function apiGetAnno(Request $request = NULL){
        $tData        = [
            'anno_id' => $request->get('anno_id')
        ];

        $annoDetailData = AnnoModel::detail($tData);
        $data['data']   = $annoDetailData;
        $data['action'] = $request->get('action');
        $tData          = NULL; unset($tData);
        $annoDetailData = NULL; unset($annoDetailData);

        return view('common.manage.anno_view', $data);
    }

    /**
     * 站別設定
     * @param string  $selectAll    [三站全選]
     * @param array  $currSite    [三站的陣列 可以多選]
     * @param string  $setData    [要存到資料庫的 網站參數]
     * @return $setData
     */
    public static function selectMultiSite($selectAll, $currSite, $setData)
    {
        $siteAnno = AnnoModel::siteAnno();
        if(isset($selectAll) && $selectAll == 'on'){
            $setData['anno_site_h'] = 1;
            $setData['anno_site_r'] = 1;
            $setData['anno_site_s'] = 1;
        }
        elseif(isset($currSite) && is_array($currSite)){
            $setData['anno_site_h'] = 0;
            $setData['anno_site_r'] = 0;
            $setData['anno_site_s'] = 0;
            foreach ($currSite as $key => $value) {
                switch($value) {
                    case $siteAnno->H_Anno:    // 學校端
                        $setData['anno_site_h'] = 1;
                        break;
                    case $siteAnno->R_Anno:    // 縣市端
                        $setData['anno_site_r'] = 1;
                        break;
                    case $siteAnno->S_Anno:    // 教育部
                        $setData['anno_site_s'] = 1;
                        break;
                }
            }
        }
        return $setData;
    }

    /**
     * @param Request $request [description]
     * @param  string $param1 [分辨入口公告與即時訊息]
     * 即時訊息 入口公告 顯示於首頁
     * @return data 到 home.blade 目前呈現未定
     */
    public function getUrgentAnno($param1)
    {
        $defaultGroupCode = Session::get('User.defaultGroupCode');

        $typeAnno = AnnoModel::typeAnno();
        $siteAnno = AnnoModel::siteAnno();

        $keyword            = [
            'select'        =>  'anno_id, anno_title, anno_content, anno_start_date',
            'anno_publish'  =>  1,
        ];

        switch($defaultGroupCode)
        {
            case $siteAnno->H_Anno:
                $keyword['anno_site_h'] = 1;
                break;
            case $siteAnno->R_Anno:
                $keyword['anno_site_r'] = 1;
                break;
            case $siteAnno->S_Anno:
                $keyword['anno_site_s'] = 1;
                break;
        }

        if($param1 == 'entryAnno') {   // 入口公告 type 4
            $keyword['anno_type']  = $typeAnno->Entry;
        }
        elseif($param1 == 'inTimeAnno') {   // 即時訊息 type 5
            $keyword['anno_type'] = $typeAnno->InTime;
        }

        $tData          = AnnoModel::listData($keyword);
        $data['data']   = $tData;

        $keyword            = NULL; unset($keyword);
        $tData              = NULL; unset($tData);
        $defaultGroupCode   = NULL; unset($defaultGroupCode);

        return $data;
    }

    /**
     * @param Request $request [description]
     * @param  string $param1 [首頁存取參數]
     * 網站首頁最新消息的詳細資料 由home.blade來抓取
     * @return data 到 home.blade  由 anno_home_view_detail 呈現
     */
    public function getDetailAnno(Request $request = NULL, $param1)
    {
        $id_identity            = Session::get('User.id_identity');
        $anno_manager_id        = Session::get('User.manager_id');

        /**
         * 如果該使用者點看詳細資料 則記錄下其帳號
         * 取出anno_id紀錄 使用者帳號id
         * 檢查是否有該使用者 如果沒有 就加入該使用者到資料庫中
         */
        $userData               = [
            'anno_id'           => $request->anno_id,
            'anno_manager_id'   => $anno_manager_id,
        ];

        $user_list = AnnoVisitorModel::listData($userData);

        // 如果搜尋不到 就把該使用者點閱的紀錄加入
        if($user_list == false)
        {
            AnnoVisitorModel::add($userData);
        }

        $keyword      = [
            'select'  => 'anno_type, anno_path, anno_title, anno_content, anno_start_date',
            'anno_id' => $request->anno_id,
        ];

        $annoDetailData     = AnnoModel::detail($keyword);
        
        $keyword      = [
            'select'  => 'anno_id, anno_path, anno_file_name',
            'anno_id' => $request->anno_id,
        ];

        $tAttachmentData    = AnnoAttachmentModel::listData($keyword);

        if(isset($tAttachmentData) && $tAttachmentData != ''){
            foreach($tAttachmentData as $key => $val){
                if(isset($tAttachmentData)){
                    $arrAttachmentData[$key]['fileContent'] = FileLib::getContent($val->anno_path);
                    $arrAttachmentData[$key]['fileWebPath'] = FileLib::getWebPath($val->anno_path);
                    $arrAttachmentData[$key]['file_original_name'] = $val->anno_file_name;
                    // $data['tAttachmentData'][$key]->file_icon = (new FaqController)->fileIcon((isset($val->ext)) ? $val->ext : 'empty', $val->file_loc, $val->ori_filename);
                }
            }
            $data['arrAttachmentData']  = $arrAttachmentData;
        }
        
        $data['data']               = $annoDetailData;

        $keyword            = NULL; unset($keyword);
        $annoDetailData     = NULL; unset($annoDetailData);
        $tAttachmentData    = NULL; unset($tAttachmentData);
        $arrAttachmentData = NULL; unset($arrAttachmentData);

        return view('common.manage.anno_home_view_detail', $data);
    }

    /**
     * 呈現在首頁 網站最新消息 由startController來抓取  
     * @param  string $param1 [首頁存取參數]
     * 抓取最新消息(type6) 三個網站分開放
     * @return data 到 startController 由home.blade呈現
     */
    public function getStartControllerNewAnno($param1)
    {
        $typeAnno   = AnnoModel::typeAnno();
        $siteAnno   = AnnoModel::siteAnno();
        $typeLevel  = SchoolDataModel::typeLevel();
        $typeAttr   = ManagerDataModel::typeAttr();

        $id_identity        = Session::get('User.id_identity');
        $defaultGroupCode   = Session::get('User.defaultGroupCode');
        $school_level       = Session::get('User.school_level');
        $school_id          = Session::get('User.school_id');
        $id_city            = Session::get('User.id_city');

        $listNumHomePage  = 6;
        
        if($param1 == 'getStartControllerNewAnno')
        {
            $keyword                = [
                'select'            => 'anno_id, anno_title, anno_start_date, anno_authority',
                'order'             =>  ['anno_id'=>'DESC'],
                'pageMode'          =>  'normal',
                'listNum'           =>  $listNumHomePage,
                'anno_type'         =>  $typeAnno->NewSiteAnno,
                'anno_authority'    =>  $id_identity,
                'anno_primary'      =>  null,
                'anno_junior'       =>  null,
                'anno_county_only'  =>  null,
                'anno_school_id'    =>  null,
            ];
        }

        switch($defaultGroupCode)
        {
            case $siteAnno->H_Anno:
                $keyword['anno_site_h'] = 1;
                break;
            case $siteAnno->R_Anno:
                $keyword['anno_site_r'] = 1;
                break;
            case $siteAnno->S_Anno:
                $keyword['anno_site_s'] = 1;
                break;
        }

        if($id_identity == $typeAttr->School)
        {
            $keyword['anno_county_id'] = $id_city;
            if($school_level == $typeLevel->SchoolPrimary || $school_level == $typeLevel->SchoolPrimaryAppend)
            {   //小學
                $keyword['anno_primary']    = 1;
                $keyword['anno_school_id']     = $school_id;
            }
            elseif($school_level == $typeLevel->SchoolJunior || $school_level == $typeLevel->SchoolJuniorAppend)  //中學
            {
                $keyword['anno_junior'] = 1;
                $keyword['anno_school_id'] = $school_id;
            }
        }
        elseif($id_identity == $typeAttr->County)
        {
            $keyword['anno_county_only'] = 1;
        }
        elseif($id_identity == $typeAttr->Manage || $id_identity == $typeAttr->Edu)
        {
            //不取 以下條件故意設定一組 無法取到資料的組合
            // $keyword['anno_type'] = 7;
            $keyword['anno_site_h'] = 2;
            $keyword['anno_site_r'] = 2;
            $keyword['anno_site_s'] = 2;
            $keyword['anno_county_only'] = 2;
        }

        $anno_manager_id   = Session::get('User.manager_id');
        // 蒐集點閱過的 anno_id
        $userData               = [
            'select'            => 'anno_manager_id, anno_id',
            'anno_manager_id'   => $anno_manager_id,
        ];

        $user_list = AnnoVisitorModel::listData($userData);

        if($user_list)
        {
            foreach ($user_list as $key => $value) {
                $readArrAnnoId[$key] = $user_list[$key]->anno_id;
            }
            $data['readArrAnnoId'] = $readArrAnnoId;
        }

        $tData    = AnnoModel::getStartModelNewAnno($keyword);

        $keyword  = NULL; unset($keyword);

        $data['data']   = $tData;
        $data['Manage'] = $typeAttr->Manage;
        $data['Edu']    = $typeAttr->Edu;
        $data['County'] = $typeAttr->County;
        $data['School'] = $typeAttr->School;
        
        return $data;
    }

    /**
     * @param Request $request [description]
     * @param integer $id [searching for announcements by relative path]
     * @param string $param1 [分辨公告類別]
     * @return $data
     * 相關公告顯示 & 功能說明
     * 目前未使用到此function(保留)
     */
    public function apiRelatedAnno(Request $request = NULL, $id, $param1)
    {
        $typeAnno       = AnnoModel::typeAnno();
        $currentTime    = Carbon::now();

        $keyword          = [
            'relativeId'  =>  $request->id,
            'currentTime' =>  $currentTime,
        ];

        if($request->param1 == 'textRelatedAnno') {   // 在路徑上的相關公告
            $keyword['action'] = 'textRelatedAnno';
        }
        elseif($request->param1 == 'textDescription') {   // 在路徑上的功能說明
            $keyword['action']        = 'textDescription';
            $keyword['descript_type'] = $typeAnno->Description;
        }
        elseif($request->param1 == 'entryAnno') {   // 入口公告 type 4
            $keyword['action']        = 'entryAnno';
            $keyword['descript_type'] = $typeAnno->Entry;
            $keyword['publish']       = '1';
        }

        // 抓取該目錄下的
        $tData      = AnnoModel::listData($keyword);
        $keyword    = NULL; unset($keyword);

        $data['data'] = $tData;
        return $data;
    }

    /**
     * 搜尋列表
     * @param Request $request [description]
     * @param  string $code    [description]
     * @return view                  common.manage.anno_list
     * 目前未使用到此function(保留)
     */
    public function apiSearchAnno($code = NULL, Request $request){
        // 取 anno 列表
        $listNum = 20;
        $action  = strval($request->action); // 取 action

        $keyword_Manager = Session::get('keyword_Manager.anno_search');

        if($request->isMethod('post')) {
            // 如果是清除搜尋結果
            if($action == 'clear') {
                Session::forget('keyword_Manager');
                $request->anno_search = NULL;
                $data['btnClear']     = false;
            }
            // 處理標題搜尋
            if((isset($request->anno_search) && $request->anno_search != '') || $keyword_Manager != '') {   // 如果跟之前的搜尋條件不同則取代
                if($keyword_Manager != $request->anno_search) {
                    $this->searchCondProcessor('anno_search', $request);
                }
                $data['btnClear'] = true;
            }
            $CategoryArr = NULL; unset($CategoryArr);
        }
        elseif($request->page) {   // 處理標題搜尋
            if($keyword_Manager != ''){
                $data['btnClear'] = true;
            }
        }

        // 組織 query 條件
        $keyword          = [
            'anno_search' =>  $keyword_Manager ? $keyword_Manager :'',
            'pageMode'    =>  'normal',
            'listNum'     =>  $listNum,
            'order'       =>  ['anno_id'=>'DESC'],
        ];

        $tData        = AnnoModel::listData($keyword);
        $data['data'] = $tData;
        $data['code'] = $code;
        $tData        = NULL; unset($tData);

        return view('common.manage.anno_list', $data);
    }
}