@extends('app')
@section('content')
    <div class="container">
        <div id="new_list" class="tabcontent">
            <div class="bluetabs">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#new_form_up" data-toggle="tab"><i class="fa fa-plus"></i>
                        @if(isset($data)&& $data->anno_type == $Entry)
                            更新入口公告
                        @elseif(isset($data)&& $data->anno_type == $InTime)
                            更新即時訊息
                        @endif</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="new_form_up">
                        <form id="formData" name="formData" method="post" class="form-horizontal" enctype="multipart/form-data" 
                            @if(isset($data)&& $data->anno_type == $Entry)
                                action="{{ route('code.up',['code'=>$code,'id'=>$data->anno_id,'param1'=>'entryAnnoUp']) }}"
                            @elseif(isset($data)&& $data->anno_type == $InTime)
                                action="{{ route('code.up',['code'=>$code,'id'=>$data->anno_id,'param1'=>'inTimeAnnoUp']) }}"
                            @endif >
                            <table class="table table-bordered">
                                <tr>
                                    <td width="30%">
                                        <div class="col-lg-4">
                                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span>{{-- 網站分類 --}}@lang('anno.site')@lang('anno.type')<span class="caret"></span></button>
                                            <ul class="dropdown-menu" id="dropdown">
                                                <li><a href="#" class="small" data-value="option1" tabIndex="-1"><input type="checkbox" name="selectAll" id="selectAll" />{{-- 全選 --}}@lang('anno.selectAll')</a></li>
                                                <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $H_Anno !!}" 
                                                    @if($getSiteArr['publish_H'] == $H_Anno) checked @endif >人力網</a></li>
                                                <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $R_Anno !!}"
                                                    @if($getSiteArr['publish_R'] == $R_Anno) checked @endif >學籍網</a></li>
                                                <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $S_Anno !!}"
                                                    @if($getSiteArr['publish_S'] == $S_Anno) checked @endif >學生網</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td width="15%">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="anno_publish" id="anno_publish" @if($data->anno_publish == '1') checked @endif>
                                                {{-- 發佈 --}}@lang('anno.publish')
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            {{ csrf_field() }}
                            <table class="table table-bordered">
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
                                {{--  --}}
                            </table>
                            <div class="text-center">
                                <button type="submit" id="done" class="btn btn-default ubtinverse">@lang('btn.save'){{--儲存--}}</button>
								<button type="button" class="btn btn-default ubtinverse" onclick="location.href='{{ route('code.list',['code'=>$code]) }}';">
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

	// done
	$(document).ready(function(){
	    $("#done").click(function(){
	        var publish;
	        var param1;
	        var selectAll;
	        var selected=[];
	        var anno_authority;

	        selectAll = $("#selectAll").prop("checked");

	        $("[name='currSite[]']:checkbox:checked").each(function(){
	            selected.push($(this).val());
	        });

	        if(selectAll  == false && ((selected == '') || (selected == undefined)))
	        {
	            alert('請選擇站別分類！');
	            return false;
	        }

	        publish = $('input[name="publish"]').prop("checked");
	        if(publish == true)  // 發佈有被勾選
	        {
	            // alert('該則入口公告將被發佈');

	        }
	        else if(publish == false)  // 發佈沒有被勾選
	        {
	            // alert('該則入口公告將不會被發佈並且暫時存放於入口公告列表中');
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

       if(( idx = options.indexOf( val )) > -1){
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

    // 載入編輯器替換
    $(function(){
        CKEDITOR.replace( 'anno_content', {  //content is ID
            width: '100%'
        });
    });
</script>
@endsection