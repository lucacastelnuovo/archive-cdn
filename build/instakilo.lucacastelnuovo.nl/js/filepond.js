"use strict";FilePond.registerPlugin(FilePondPluginFileValidateSize,FilePondPluginFileValidateType,FilePondPluginImageExifOrientation,FilePondPluginImageCrop,FilePondPluginImageTransform),FilePond.setOptions({maxFileSize:"5MB",acceptedFileTypes:["image/png","image/jpg","image/jpeg"],imageCropAspectRatio:"1:1",server:"/includes/upload.php"});var pond=FilePond.create(document.querySelector('input[type="file"]')),upload_btn=document.querySelector("button.btn-large"),caption_input=document.querySelector("#caption");upload_btn.disabled=!0;var pond_instance=document.querySelector(".filepond--root");pond_instance.addEventListener("FilePond:processfile",function(e){null!==e.detail.error&&M.toast({html:"Please reupload image"}),upload_btn.disabled=!1});