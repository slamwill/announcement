@extends('app')
@section('content')
    <div class="container">
        <div class="bluetabs">
            <ul class="nav nav-tabs">
                    <li class="active"><a href="#new_form_add" data-toggle="tab"><i class="fa fa-plus"></i> 新增最新消息</a></li>
                    <li><a href="#newform_list" data-toggle="tab"><i class="fa fa-file-text-o"></i>最新消息列表</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="new_form_add">
                    <form id="formData" name="formData" method="post" class="form-horizontal" enctype="multipart/form-data" action="{{ route('code.add',['code'=>$code,'param1'=>'newAnnoAdd']) }}">
                        <table class="table table-bordered">
                            <tr>
                                <td width="30%">
                                    <div class="form-inline">
                                        <div class="form-group">
                                            <label>發佈時間:</label>
                                            <input value="{{date('Y-m-d')}}" class="form-control form_date" type="text" data-date-format="yyyy-mm-dd" name="anno_start_date" readonly="">
                                        </div>
                                    </div>
                                </td>
                                <td width="15%" @if(Session::get('User.id_identity') == $Manage || Session::get('User.id_identity') == $Edu) style="" @else style="display: none" @endif>
                                    <div class="form-inline">
                                        <div class="form-group">
                                            <label>類別:</label>
                                            <select class="form-control" name="anno_receiver_id" id="anno_receiver_id" onchange="educationSelection()">
                                                <option value="">請選擇</option>
                                                <option value="{!! $School !!}" name="school" id="school">學校端</option>
                                                <option value="{!! $County !!}" name="county" id="county">縣市端</option>
                                                <option value="{!! $County_School !!}" name="school_county" id="school_county" selected>全部</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <td width="15%" id="anno_education" style="display: none">
                                    <div class="form-inline">
                                        <div class="form-group">
                                            <label>學別:</label>
                                            <select class="form-control" name="anno_edu" id="anno_edu">
                                                <option value="">請選擇</option>
                                                <option value="{!! $PrimaryAnno !!}" name="primary_anno" id="primary_anno">小學</option>
                                                <option value="{!! $JuniorAnno !!}" name="junior_anno" id="junior_anno">中學</option>
                                                <option value="{!! $PrimaryJuniorAnno !!}" name="primary_junior_anno" id="primary_junior_anno" selected>中/小學</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                <td width="15%" @if(Session::get('User.id_identity') == $Manage || Session::get('User.id_identity') == $Edu) style="" @else style="display: none" @endif>
                                    <div class="col-lg-4">
                                        <button type="button" id="dropdown" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span>{{-- 網站分類 --}}@lang('anno.site')@lang('anno.type')<span class="caret"></span></button>
                                        <ul class="dropdown-menu" id="dropdown">
                                            <li><a href="#" class="small" data-value="option1" tabIndex="-1"><input type="checkbox" name="selectAll" id="selectAll" />{{-- 全選 --}}@lang('anno.selectAll')</a></li>
                                            <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $H_Anno !!}"/>人力網</a></li>
                                            <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $R_Anno !!}"/>學籍網</a></li>
                                            <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $S_Anno !!}"/>學生網</a></li>
                                        </ul>
                                    </div>
                                </td>
                                <td width="10%">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="anno_sticky" id="anno_sticky">
                                            永遠置頂
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        {{ csrf_field() }}
                        <input type="hidden" id="anno_id" name="anno_id" value="{{-- {{ Session::get('faqDetailData.anno_id') }} --}}" />
                        <table class="table table-bordered">
                            <tr>
                                <th width="10%">主旨</th>
                                <td colspan="5"><input type="text" id="anno_title" name="anno_title" class="form-control" value=""></td>
                            </tr>
                            <tr>
                                <th>
                                    內容
                                </th>
                                <td colspan="5">
                                    <textarea class="form-control" rows="10" id="anno_content" name="anno_content"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    {{-- 檔案上傳 --}}@lang('faq.FileUpload')
                                </th>
                                <td colspan="5">
                                    <div class="form-group">
                                        <input type="file" id="exampleInputFile" name="image[]" multiple="true" class="form-control">
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <div class="text-center">
                            <button type="submit" id="new_anno_done" class="btn btn-default ubtinverse">@lang('btn.save'){{--儲存--}}</button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane" id="newform_list">
                    <table class="table_texleft table-hover wp_100">
                        <tr>
                            <th>置頂</th>
                            <th>發佈時間</th>
                            <th>主旨</th>
                            <th @if(Session::get('User.id_identity') == $Manage || Session::get('User.id_identity') == $Edu) style="" @else style="display: none" @endif>網站</th>
                            <th>操作</th>
                        </tr>
                        @if($data)
                            @foreach($data as $key => $val)
                                <tr>
                                    <td nowrap>
                                        @if($val->anno_sticky == 1)
                                            ★
                                        @endif
                                    </td>
                                    <td nowrap>{{ date('Y-m-d', strtotime($val->anno_start_date)) }}</td>
                                    <td nowrap>{{$val->anno_title}}</td>
                                    <td nowrap 
                                        @if(Session::get('User.id_identity') == $Manage || Session::get('User.id_identity') == $Edu)
                                            style=""
                                        @else
                                            style="display: none"
                                        @endif>
                                        @if($val->anno_site_h == 1)
                                            -人力網-
                                        @endif
                                        @if($val->anno_site_r == 1)
                                            -學籍網-
                                        @endif
                                        @if($val->anno_site_s == 1)
                                            -學生網-
                                        @endif
                                    </td>
                                    <td nowrap>
                                        <div class="btn-group">
                                            <button class="btn btn-default bt_plus" onclick="location.href='{{ route('code.form',['code'=>$code,'action'=>'up','id'=>$val->anno_id,'token'=>'newAnnoUp']) }}'">
                                                <span class="glyphicon glyphicon-pencil">{{-- 修改 --}}@lang('btn.up')</span>
                                            </button>
                                            <button title="刪除" class="btn btn-default bt_delete Delete_Course_not" onclick="if(confirm('@lang('msg.delConfirm')?')){ location.href='{{ route('code.del',['code'=>$code,'id'=>$val->anno_id]) }}' }"><span class="glyphicon glyphicon-remove ">{{-- 刪除 --}}@lang('btn.del')</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5">
                                    @lang('btn.noContent')
                                </td>
                            </tr>
                        @endif
                    </table>
                    @if($data)
                        <div class="pd-0-10">{!! $data->links('') !!}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if(Session::has('msg'))
        <div class="form-group">
            <div id="msg" class="alert alert-danger">
                @foreach(Session::get('msg') as $key=>$val)
                    {{ $val }}<br />
                @endforeach
            </div>
        </div>
    @endif
@endsection
@section('js')
<script src="{{ asset('/web/js/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('/web/js/ckeditor/adapters/jquery.js') }}"></script>
<script src="{{ asset('/web/js/choosen/chosen.jquery.min.js') }}"></script>
<script src="{{ asset('/web/js/choosen/prism.js') }}"></script>
<script>

    // 開啟學等搜尋
    id_identity = '{!! Session::get('User.id_identity') !!}';
    manage      = '{!! $Manage !!}';
    edu         = '{!! $Edu !!}';
    county      = '{!! $County !!}';
    school      = '{!! $School !!}';

    function educationSelection(){
        anno_receiver_id = $('#anno_receiver_id').val();

        if(id_identity == manage || id_identity == edu)
        {
            if(anno_receiver_id == school)
            {
                $('#anno_education').show();
            }
            else
            {
                $('#anno_education').css({
                    display: 'none'
                });
            }            
        }
    }

    $(document).ready(function(){
        if(id_identity == county)
        {
            $('#anno_education').show();
        }

        $("#new_anno_done").click(function(){
            var publish;
            var sticky;
            var selectAll;
            var selected=[];

            if(id_identity == manage || id_identity == edu)
            {                
                selectAll = $("#selectAll").prop("checked");

                $("[name='currSite[]']:checkbox:checked").each(function(){
                    selected.push($(this).val());
                });

                if(selectAll  == false && ((selected == '') || (selected == undefined)))
                {
                    alert('請選擇站別分類！');
                    return false;
                }
            }

            sticky = $('input[name="sticky"]').prop("checked");
            if(sticky == true)  // 永遠置頂有被勾選
            {
                // alert('該則公告將被置頂');
            }

            publish = $('input[name="publish"]').prop("checked");

            if(publish == true)  // 發佈有被勾選
            {
                // alert('該則公告將被發佈');
            }
            else if(publish == false)  // 發佈沒有被勾選
            {
                // alert('該則公告將不會被發佈並且暫時存放於最新消息列表中');
            }
        });
    });

    // 下拉式多選checkbox
    var options = [];
    $( '#dropdown a' ).on( 'click', function( event ) {
       var $target = $( event.currentTarget ),
           val = $target.attr( 'data-value' ),
           $inp = $target.find( 'input' ),
           idx;

       if((idx = options.indexOf( val )) > -1) {
          options.splice( idx, 1 );
          setTimeout( function() { $inp.prop( 'checked', false ) }, 0);
       }
       else{
          options.push( val );
          setTimeout( function() { $inp.prop( 'checked', true ) }, 0);
       }
       $(event.target).blur();
       return false;
    });

    $("#selectAll").click(function() {
        if($("#selectAll").prop("checked"))
        {
            $("input[name='currSite[]']").each(function() {
                $(this).prop("checked", true);
            });
            $("input[name='currSite[]']").attr("disabled", true);
        }
        else
        {
            $("input[name='currSite[]']").each(function() {
                $(this).prop("checked", false);
            });
            $("input[name='currSite[]']").removeAttr("disabled");
        }
    });


    var url = document.location.toString();
    if (url.match('entryAnnoAdd')) {
        $('.tablinks a[href="#new_entry"]').tab('show');
    }

    // 在最新消息列表 當頁面再轉換下一頁的時候 頁籤依然留在-最新消息列表
    if (url.match('page=') || url.match('upAnno') || url.match('addAnno')) {
        $('.nav-tabs a[href="#newform_list"]').tab('show');
    }

    // 載入編輯器替換
    $(function(){
        CKEDITOR.replace( 'anno_content', {  //content is ID
            width: '100%'
        });
    });

</script>
@endsection