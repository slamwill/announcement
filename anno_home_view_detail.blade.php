<div id="new_content" class="col-lg-10">
    <h3 class="pull-left"><i class="fa fa-file-text-o"></i>{{$data->anno_title}}</h3>
    <div class="pull-right new_day"><i class="fa fa-caret-right"></i> {{$data->anno_start_date}}</div>
    <div class="content_text">
        {{$data->anno_content}}
    </div>
    <div class="new_files"> <strong> <i class="fa fa-caret-right"></i> 相關檔案 : </strong>
        <ul>
            @if(isset($arrAttachmentData) && $arrAttachmentData != '')
                @foreach($arrAttachmentData as $key => $val)
                    <li style="display:inline-block; text-align: center; margin-left: 10px;">
                        <a download href="{!! $val['fileWebPath'] !!}">{!! $val['file_original_name'] !!}</a>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>
</div>