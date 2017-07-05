<?php if (!defined('THINK_PATH')) exit();?><!--添加广告-->
<div class="dialog_content">
<form id="info_form" action="<?php echo u('ad/add');?>" method="post">
<table width="100%" cellpadding="2" cellspacing="1" class="table_form">
    <tr>
      <th width="80"><?php echo L('ad_name');?> :</th>
      <td><input type="text" name="name" id="name" class="input-text" size="40"></td>
    </tr>
    <tr>
      <th><?php echo L('ad_url');?> :</th>
      <td><input type="text" name="url" class="input-text" size="40"></td>
    </tr>
    <tr>
      <th><?php echo L('adboard');?> :</th>
      <td>
        <select name="board_id" id="board_id">
            <?php if(is_array($adboards)): $i = 0; $__LIST__ = $adboards;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["id"]); ?>" allowtype="<?php echo ($val["allow_type"]); ?>"><?php echo ($val["name"]); ?>（<?php echo ($val["width"]); ?>*<?php echo ($val["height"]); ?>）</option><?php endforeach; endif; else: echo "" ;endif; ?>
        </select>
      </td>
    </tr>
    <tr>
      <th><?php echo L('ad_type');?> :</th>
      <td>
        <select name="type" id="type">
            <?php if(is_array($ad_type_arr)): $i = 0; $__LIST__ = $ad_type_arr;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($val); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
        </select>
      </td>
    </tr>
    <tr id="ad_image" class="bill_media">
      <th><?php echo L('ad_image');?> :</th>
      <td>
          <input type="text" name="img" id="J_img" class="input-text fl mr10" size="30">
          <div id="J_upload_img" class="upload_btn"><span><?php echo L('upload');?></span></div>
      </td>
    </tr>
    <tr id="ad_code" class="bill_media" style="display:none;">
      <th><?php echo L('ad_code');?> :</th>
      <td><textarea rows="3" cols="50" name="code" id="code"></textarea></td>
    </tr>
    <tr id="ad_flash" class="bill_media" style="display:none;">
      <th><?php echo L('ad_flash');?> :</th>
      <td>
          <input type="text" name="flash" id="J_flash" class="input-text fl mr10" size="30">
          <div id="J_upload_flash" class="upload_btn"><span><?php echo L('upload');?></span></div>
      </td>
    </tr>
    <tr id="ad_text" class="bill_media" style="display:none;">
      <th><?php echo L('ad_text');?> :</th>
      <td><textarea rows="3" cols="50" name="text" id="text"></textarea></td>
    </tr>
    <tr>
      <th><?php echo L('ad_ext_image');?> :</th>
      <td>
          <input type="text" name="extimg" id="J_extimg" class="input-text fl mr10" size="30">
          <div id="J_upload_extimg" class="upload_btn"><span><?php echo L('upload');?></span></div>
      </td>
    </tr>
    <tr>
      <th><?php echo L('ad_ext_val');?> :</th>
      <td>
          <input type="text" name="extval" class="input-text fl mr10">
      </td>
    </tr>
    <tr>
      <th><?php echo L('ad_desc');?> :</th>
      <td>
          <input type="text" name="desc" class="input-text fl mr10" size="30">
      </td>
    </tr>
    <tr>
      <th><?php echo L('ad_time');?> :</th>
      <td>
        <input type="text" name="start_time" id="start_time" class="date" size="12"> - 
        <input type="text" name="end_time" id="end_time" class="date" size="12">
      </td>
    </tr>
    <tr>
        <th><?php echo L('enabled');?> :</th>
        <td>
            <label><input type="radio" name="status" value="1" checked> <?php echo L('yes');?></label>&nbsp;&nbsp;
            <label><input type="radio" name="status" value="0"> <?php echo L('no');?></label>
        </td>
    </tr>
</table>
</form>
</div>
<script src="__STATIC__/js/fileuploader.js"></script>
<script>
Calendar.setup({
    inputField : "start_time",
    ifFormat   : "%Y-%m-%d",
    showsTime  : false,
    timeFormat : "24"
});
Calendar.setup({
    inputField : "end_time",
    ifFormat   : "%Y-%m-%d",
    showsTime  : false,
    timeFormat : "24"
});
$(function(){
    $("#type").change(function(){
        $(".bill_media").hide();
        $("#ad_"+$(this).val()).show();
    });
    $("#type").change();
    //获取版块允许的广告类型
    $("#board_id").change(function(){
        var allowtype = $("#board_id option:selected").attr('allowtype');
        var allowtype_arr = allowtype.split("|");
        $("#type option").attr('selected', false).hide();
        $("#type option").each(function(){
            for(i=0; i<allowtype_arr.length; i++) {
                if($(this).val() == allowtype_arr[i]){
                    $(this).show();
                }
            }
        });
        $('#type option:visible:first').attr('selected', true);
        $("#type").change();
    });
    $("#board_id").change();

    $.formValidator.initConfig({formid:"info_form",autotip:true});
    $("#name").formValidator({onshow:"请填写广告名称",onfocus:"请填写广告名称"}).inputValidator({min:1,onerror:"请填写广告名称"});
    $('#info_form').ajaxForm({success:complate,dataType:'json'});
    function complate(result){
        if(result.status == 1){
            $.dialog.get(result.dialog).close();
            $.pinphp.tip({content:result.msg});
            window.location.reload();
        } else {
            $.pinphp.tip({content:result.msg, icon:'alert'});
        }
    }
    
    //上传图片
    var img_uploader = new qq.FileUploaderBasic({
        allowedExtensions: ['jpg','gif','jpeg','png','bmp','pdg'],
        button: document.getElementById('J_upload_img'),
        multiple: false,
        action: "<?php echo U('ad/ajax_upload_img');?>",
        inputName: 'img',
        forceMultipart: true, //用$_FILES
        messages: {
            typeError: lang.upload_type_error,
            sizeError: lang.upload_size_error,
            minSizeError: lang.upload_minsize_error,
            emptyError: lang.upload_empty_error,
            noFilesError: lang.upload_nofile_error,
            onLeave: lang.upload_onLeave
        },
        showMessage: function(message){
            $.pinphp.tip({content:message, icon:'error'});
        },
        onSubmit: function(id, fileName){
            $('#J_upload_img').addClass('btn_disabled').find('span').text(lang.uploading);
        },
        onComplete: function(id, fileName, result){
            $('#J_upload_img').removeClass('btn_disabled').find('span').text(lang.upload);
            if(result.status == '1'){
                $('#J_img').val(result.data);
            } else {
                $.pinphp.tip({content:result.msg, icon:'error'});
            }
        }
    });

    var extimg_uploader = new qq.FileUploaderBasic({
        allowedExtensions: ['jpg','gif','jpeg','png','bmp','pdg'],
        button: document.getElementById('J_upload_extimg'),
        multiple: false,
        action: "<?php echo U('ad/ajax_upload_img', array('type'=>'extimg'));?>",
        inputName: 'extimg',
        forceMultipart: true, //用$_FILES
        messages: {
            typeError: lang.upload_type_error,
            sizeError: lang.upload_size_error,
            minSizeError: lang.upload_minsize_error,
            emptyError: lang.upload_empty_error,
            noFilesError: lang.upload_nofile_error,
            onLeave: lang.upload_onLeave
        },
        showMessage: function(message){
            $.pinphp.tip({content:message, icon:'error'});
        },
        onSubmit: function(id, fileName){
            $('#J_upload_extimg').addClass('btn_disabled').find('span').text(lang.uploading);
        },
        onComplete: function(id, fileName, result){
            $('#J_upload_extimg').removeClass('btn_disabled').find('span').text(lang.upload);
            if(result.status == '1'){
                $('#J_extimg').val(result.data);
            } else {
                $.pinphp.tip({content:result.msg, icon:'error'});
            }
        }
    });
    var flash_uploader = new qq.FileUploaderBasic({
        allowedExtensions: ['swf'],
        button: document.getElementById('J_upload_flash'),
        multiple: false,
        action: "<?php echo U('ad/ajax_upload_img', array('type'=>'flash'));?>",
        inputName: 'flash',
        forceMultipart: true, //用$_FILES
        messages: {
            typeError: lang.upload_type_error,
            sizeError: lang.upload_size_error,
            minSizeError: lang.upload_minsize_error,
            emptyError: lang.upload_empty_error,
            noFilesError: lang.upload_nofile_error,
            onLeave: lang.upload_onLeave
        },
        showMessage: function(message){
            $.pinphp.tip({content:message, icon:'error'});
        },
        onSubmit: function(id, fileName){
            $('#J_upload_flash').addClass('btn_disabled').find('span').text(lang.uploading);
        },
        onComplete: function(id, fileName, result){
            $('#J_upload_flash').removeClass('btn_disabled').find('span').text(lang.upload);
            if(result.status == '1'){
                $('#J_flash').val(result.data);
            } else {
                $.pinphp.tip({content:result.msg, icon:'error'});
            }
        }
    });
})
</script>