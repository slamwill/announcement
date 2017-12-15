@extends('app')
@section('content')
    <div class="container">
        <div id="new_list" class="tabcontent">
            <div class="bluetabs">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#new_form_up" data-toggle="tab"><i class="fa fa-plus"></i>更新最新消息</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="new_form_up">
                        <form id="formData" name="formData" method="post" class="form-horizontal" enctype="multipart/form-data" action="{{ route('code.up',['code'=>$code,'id'=>$data->anno_id,'param1'=>'newAnnoUp']) }}">
                            <table class="table table-bordered">
                                <tr>
                                    <td width="30%">
                                        <div class="form-inline">
                                            <div class="form-group">
                                                <label>發佈時間:</label>
                                                <input value="{{ date('Y-m-d', strtotime($data->anno_start_date)) }}" class="form-control form_date" type="text" data-date-format="yyyy-mm-dd" name="anno_start_date" readonly="">
                                                
                                            </div>
                                        </div>
                                    </td>
                                    <td width="15%" @if(Session::get('User.id_identity') == $Manage || Session::get('User.id_identity') == $Edu) style="" @else style="display: none" @endif>
                                        <div class="form-inline">
                                            <div class="form-group">
                                                <label>類別:</label>
                                                <select class="form-control" name="anno_receiver_id" id="anno_receiver_id" onchange="educationSelection()" value="{{ $anno_receiver_id }}">
                                                    <option value="{!! $School !!}" name="school" id="school" @if($anno_receiver_id == $School) selected @endif>學校端</option>
                                                    <option value="{!! $County !!}" name="county" id="county" @if($anno_receiver_id == $County) selected @endif>縣市端</option>
                                                    <option value="{!! $County_School !!}" name="school_county" id="school_county" @if($anno_receiver_id == $County_School) selected @endif>全部</option>
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td width="15%" id="anno_education" @if($anno_receiver_id == $School || $data->anno_authority == $County) style="" @else style="display: none" @endif>
                                        <div class="form-inline">
                                            <div class="form-group">
                                                <label>學別:</label>
                                                <select class="form-control" name="anno_edu" id="anno_edu" value="{{ $anno_edu }}">
                                                    <option value="">請選擇</option>
                                                    <option value="{!! $PrimaryAnno !!}" name="primary_anno" id="primary_anno" @if($anno_edu == $PrimaryAnno) selected @endif>小學</option>
                                                    <option value="{!! $JuniorAnno !!}" name="secondary_anno" id="secondary_anno" @if($anno_edu == $JuniorAnno) selected @endif>中學</option>
                                                    <option value="{!! $PrimaryJuniorAnno !!}" name="primary_secondary_anno" id="primary_secondary_anno" @if($anno_edu == $PrimaryJuniorAnno) selected @endif>中/小學</option>
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td width="15%" @if(Session::get('User.id_identity') == $Manage || Session::get('User.id_identity') == $Edu) style="" @else style="display: none" @endif>
                                        <div class="col-lg-4">
                                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span>{{-- 網站分類 --}}@lang('anno.site')@lang('anno.type')<span class="caret"></span></button>
                                            <ul class="dropdown-menu" id="dropdown">
                                                <li><a href="#" class="small" data-value="option1" tabIndex="-1"><input type="checkbox" name="selectAll" id="selectAll">{{-- 全選 --}}@lang('anno.selectAll')</a></li>
                                                <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $H_Anno !!}" 
                                                    @if($getSiteArr['publish_H'] == $H_Anno) checked @endif
                                                >人力網</a></li>
                                                <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $R_Anno !!}"
                                                    @if($getSiteArr['publish_R'] == $R_Anno) checked @endif
                                                >學籍網</a></li>
                                                <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $S_Anno !!}"
                                                    @if($getSiteArr['publish_S'] == $S_Anno) checked @endif
                                                >學生網</a></li>

                                            </ul>
                                        </div>
                                    </td>
                                    <td width="10%">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="anno_sticky" id="anno_sticky" @if($data->anno_sticky == '1') checked @endif>
                                                永遠置頂
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            {{ csrf_field() }}
                            <table class="table table-bordered">
                                <tr>
                                    <th width="10%">主旨</th>
                                    <td colspan="5"><input type="text" id="anno_title" name="anno_title" class="form-control" value="{{ $data->anno_title }}"></td>
                                </tr>
                                <tr>
                                    <th>
                                        內容
                                    </th>
                                    <td colspan="5">
                                        <textarea class="form-control" rows="10" id="anno_content" name="anno_content">
                                        	{{ $data->anno_content }}
                                        </textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        {{-- 檔案上傳 --}}@lang('faq.FileUpload')
                                    </th>
                                    <td colspan="5">
                                        <div class="form-group">
                                            <input type="file" id="exampleInputFile" name="image[]" multiple="true" class="form-control">
                                            @if(isset($arrAttachmentData) && $arrAttachmentData != '')
                                                @foreach($arrAttachmentData as $key => $val)
                                                    <li style="display:inline-block; text-align: center; margin-left: 10px;">
                                                        <a download href="{!! $val['fileWebPath'] !!}">{!! $val['file_original_name'] !!}</a>
                                                    </li>
                                                @endforeach
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <div class="text-center">
                                <button type="submit" id="done" class="btn btn-default ubtinverse">@lang('btn.save'){{--儲存--}}</button>
								<button type="button" class="btn btn-default ubtinverse" onclick="location.href='{{ route('code.list',['code'=>$code,'id'=>'newAnnoAdd']) }}';">
				                <i class="glyphicon glyphicon-chevron-left"></i>{{-- 回上一頁 --}}@lang('btn.backpage')</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="newform_list">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
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

	// done
	$(document).ready(function(){
        if(id_identity == county)
        {
            $('#anno_education').show();
        }
	    $("#done").click(function(){
	        var publish;
            var sticky;
	        var selectAll;
	        var selected=[];

	        selectAll = $("#selectAll").prop("checked");

	        $("[name='currSite[]']:checkbox:checked").each(function(){
	            selected.push($(this).val());
	        });

	        if(selectAll  == false && ((selected == '') || (selected == undefined)))
	        {
	            alert('請選擇站別分類！');
	            return false;
	        }

            sticky = $('input[name="sticky"]').prop("checked");
            if(sticky == true)  // 永遠置頂有被勾選
            {
                // alert('該則公告將被置頂');
            }

	        publish = $('input[name="publish"]').prop("checked");
	        if(publish == true)  // 發佈有被勾選
	        {
	            alert('該則公告將被發佈');

	        }
	        else if(publish == false)  // 發佈沒有被勾選
	        {
	            alert('該則公告將不會被發佈並且暫時存放於最新消息列表中');
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

       $( event.target ).blur();

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

    // 載入編輯器替換
    $(function(){
        CKEDITOR.replace( 'anno_content', {  //content is ID
            width: '100%'
        });
         // add_new_reply is a button ID
        @if(isset($token))
            ScrollTo('scroolTo');
              //scroolTo is a div ID
        @endif
    });
</script>
@endsection