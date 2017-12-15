@extends('app')
@section('content')
    <div class="container">
        <div class="bluetabs">
            <ul class="nav nav-tabs">
            	<li class="active"><a href="#new_form" data-toggle="tab"><i class="fa fa-plus"></i>
                    @if($view_anno_type == $Entry)
                        新增入口公告
                    @elseif($view_anno_type == $InTime)
                        新增即時訊息
                    @endif
                    </a></li>
                <li><a href="#newform_list" data-toggle="tab"><i class="fa fa-file-text-o"></i>
                    @if($view_anno_type == $Entry)
                        入口公告列表
                    @elseif($view_anno_type == $InTime)
                        即時訊息列表
                    @endif
                </a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane" id="newform_list">
                    <table class="table_texleft table-hover wp_100">
                        <tr>
                        	<th width="10%">發佈</th>
                            <th width="30%" height="20">內容</th>
                            <th width="40%">網站</th>
                            <th width="20%">操作</th>
                        </tr>
                        @if($data)
                            @foreach($data as $key => $val)
                                <tr>
                                	<td width="10%" height="20">
                                		@if($val->anno_publish == 1)
                                			★
                                		@endif
                                	</td>
                                    <td width="30%" height="20">{{$val->anno_content}}</td>
                                    <td width="40%">
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
                                    <td width="20%">
                                        <div class="btn-group">
                                            <button class="btn btn-default bt_plus" 
                                                @if($view_anno_type == $Entry)
                                                    onclick="location.href='{{ route('code.form',['code'=>$code,'action'=>'up','id'=>$val->anno_id,'token'=>'entryAnnoUp']) }}'"
                                                @elseif($view_anno_type == $InTime)
                                                    onclick="location.href='{{ route('code.form',['code'=>$code,'action'=>'up','id'=>$val->anno_id,'token'=>'inTimeAnnoUp']) }}'"
                                                @endif >
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
		        <div class="tab-pane active" id="new_form">
		            <form id="formData" name="formData" method="post" class="form-horizontal" enctype="multipart/form-data" 
                        @if($view_anno_type == $Entry)
                            action="{{ route('code.add',['code'=>$code,'param1'=>'entryAnnoAdd']) }}"
                        @elseif($view_anno_type == $InTime)
                            action="{{ route('code.add',['code'=>$code,'param1'=>'inTimeAnnoAdd']) }}"
                        @endif >
		                {{ csrf_field() }}
		                <div class="alert alert-warning">
		                    未登入前，在登入頁左邊呈現的入口公告。
		                </div>
		                <table class="table table-bordered">
		                    <tr>
		                        <td width="33%">
		                            <div class="col-lg-4">
		                                <button type="button" id="dropdown" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span>{{-- 網站分類 --}}@lang('anno.site')@lang('anno.type')<span class="caret"></span></button>
		                                <ul class="dropdown-menu" id="dropdown">
		                                    <li><a href="#" class="small" data-value="option1" tabIndex="-1"><input type="checkbox" name="selectAll" id="selectAll" />{{-- 全選 --}}@lang('anno.selectAll')</a></li>
                                            <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $H_Anno !!}">人力網</a></li>
                                            <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $R_Anno !!}">學籍網</a></li>
                                            <li><a href="#" class="small" data-value="option2" tabIndex="-1"><input type="checkbox" name="currSite[]" id="currSite[]" value="{!! $S_Anno !!}">學生網</a></li>
		                                </ul>
		                            </div>
		                        </td>
		                        <td width="34%">
		                            <div class="checkbox">
		                                <label>
		                                    <input type="checkbox" name="anno_publish" id="entryPublish">
		                                    {{-- 發佈 --}}@lang('anno.publish')
		                                </label>
		                            </div>
		                        </td>
		                    </tr>
		                </table>
		                <table class="table table-bordered">
		                    <tr>
		                        <td>
		                            <textarea class="form-control" rows="15" id="entryAnno" name="anno_content">
		                            </textarea>
		                        </td>
		                    </tr>
		                </table>
		                <div class="text-center">
		                    <button type="submit" id="new_anno_entry_done" class="btn btn-default ubtinverse">@lang('btn.save'){{--儲存--}}</button>
		                </div>
		            </form>
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
    // done
    $(document).ready(function(){
        $("#new_anno_entry_done").click(function(){
            var entryPublish;
            var param1;
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

            entryPublish = $('input[id="entryPublish"]').prop("checked");

            if(entryPublish == true)  // 發佈有被勾選
            {
                // alert('該則入口公告將被發佈');
            }
            else if(entryPublish == false)  // 發佈沒有被勾選
            {
                // alert('該則入口公告將不會被發佈並且暫時存放於最新消息列表中');
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
            $("input[id='currSite[]']").each(function() {
                $(this).prop("checked", true);
            });
            $("input[id='currSite[]']").attr("disabled", true);
        }
        else
        {
            $("input[id='currSite[]']").each(function() {
                $(this).prop("checked", false);
            });
            $("input[id='currSite[]']").removeAttr("disabled");
        }
    });

    // 在最新消息列表 當頁面再轉換下一頁的時候 頁籤依然留在-最新消息列表
    var url = document.location.toString();
    if (url.match('page=') || url.match('upAnno') || url.match('addAnno')) {
        $('.nav-tabs a[href="#newform_list"]').tab('show');
    }

    // 載入編輯器替換
    $(function(){
        CKEDITOR.replace( 'entryAnno', {  //content is ID
            width: '100%'
        });
    });

</script>
@endsection
