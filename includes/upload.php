<?php
$upload = new clqupload("upload");
$result = $upload->upload_process();

if(array_key_exists("name", $result)) {
    echo $result['name'];
} else {
	echo "!Failed: ".$result;
}

/*
Instructions : 
    $upload = new file_upload("upl");
        ##>Used to define the $_FILES name parameter (if form upload box named "upl" so uploading process var. will be $_FILES[upl] and here in class will named "upl" only).
    $upload->file_prefix = "pict";
        ##>Put prefix before file name.
    $upload->safe_name = TRUE;
        ##>change any spaces to "_" ,initially true.
    $upload->max_file_size
        ##>Specify maximum file size.
    $upload->dirname
        ##>dirname of being uploaded to
    $upload->filename
        ##>Specify file name without random generation or using prefix
    $upload->rename_rand
        ##>Specify it to true to make class to generate random file name
    $upload->allowed_mime_types
        ##>Specify allowed mime types to be uploaded
    $upload->blacklist_ext
        ##>Black list extensions that forbidden to be uploaded    
    echo $upload->upload_process();
        ##>perform final uploading process.
        
After uploading process it return array contining these information 
    [0]File Name
    [1]Dir Name
    [2]File Size
    [3]File Type
    [4]Extension
    [5]Original Name
    [6]File Path
************************************************************************/
    
class clqupload {    
    var $uploadinput ;
    var $dirname = "../data/";
    var $chmod = "0777";
    var $safe_name = true ;
    var $max_file_size ;
    var $file_prefix ;
    var $filename ;
    var $filesize ;
    var $filetype ;
    var $file_tmp ;
    var $file_err ;
    var $file_ext ;
    var $upload_error ;
    var $rename_rand ;
    var $rename_to ;
    var $allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif', 'image/tiff');
    var $blacklist_ext = array("php","php3","php4","php5","php6","phtml");
    
    function __construct($uploadinput){

        $this->original_name = $_FILES[$uploadinput]['name'];
        $this->filesize = $_FILES[$uploadinput]['size'];
        $this->filetype = $_FILES[$uploadinput]['type'];
        $this->file_tmp = $_FILES[$uploadinput]['tmp_name'];
        $this->file_err = $_FILES[$uploadinput]['error'];
        $this->file_ext = substr($this->original_name, strrpos($this->original_name, '.') + 1);
        
        
        foreach ($this->blacklist_ext as $item) {
            if(preg_match("/$item\$/i",$this->original_name)) {
                 @unlink($this->file_tmp);
                 $this->upload_error = "Unsupported file types to upload";
            }
        }
        
        if (!file_exists($this->dirname)){
             if (!mkdir($this->dirname, $this->chmod)){
                 @unlink($this->file_tmp);
                 $this->upload_error = "Folder not exist and cannot make it";
             }
        }
        
        if($this->max_file_size){
            if($this->filesize > $this->max_file_size){
                 @unlink($this->file_tmp);
                 $this->upload_error = "Exceeded the maxumun file size";                
            }
        }
        
        if($this->filesize == 0){
            @unlink($this->file_tmp);
             $this->upload_error = "Error in File: 0 byte";            
        }  
          
        if($this->file_err){
            switch ($this->file_err){
                case '0':
                    @unlink($this->file_tmp);
                     $this->upload_error = "Error in File: 0 byte";            
                    break;
    
                case '1':
                    @unlink($this->file_tmp);
                     $this->upload_error = "Exceeded server maximum upload size in $this->filesize";
                    break;
    
                case '2':
                    @unlink($this->file_tmp);
                     $this->upload_error = "Exceeded the maxumun file size";
                    break;
    
                case '3':
                    @unlink($this->file_tmp);
                     $this->upload_error = "Error in uploading process maybe of connection or error occured during uploading";            
                    break;
    
                case '4':
                    @unlink($this->file_tmp);
                     $this->upload_error = "Nothing selected to upload - Go back";
                    break;
              }
            
        } 
          
        if (!$this->file_ext)
        {
            @unlink($this->file_tmp);
             $this->upload_error = "file must have an extension";
           }
        
        if($this->mime_check()==false){
            @unlink($this->file_tmp);
            $this->upload_error = "mime type error";
        }

        if($this->ext_check()==false){
            @unlink($this->file_tmp);
            $this->upload_error = "mime type error";
        }        
    }
    
    function filename_without_ext($filename){
        $pos = strripos($filename, '.');
        if($pos === false){
            return $filename;
        }else{
            return substr($filename, 0, $pos);
        }
    }

    function up_file_name($counter=0){
        
        $TEMP = $this->file_prefix;
        if($this->rename_rand){
            $TEMP .= rand(56461654648976,56461654648976546);
        }else{
            $name_without_ext = $this->filename_without_ext($this->original_name);
            $TEMP .= $name_without_ext;
        }
        if($this->rename_to){
            $TEMP = $this->file_prefix;
            $TEMP .= $this->rename_to;
        }
        if($this->safe_name){
            $TEMP = str_replace(" ","_",$TEMP);
        }
        
        do{
            $file_TEMP = $TEMP;
            $file_TEMP2 = $TEMP;
            $file_TEMP .= ".";
            $file_TEMP .= $this->file_ext;
            if(file_exists($this->dirname.$file_TEMP)){
                $counter ++;
                $file_TEMP2 .= "$counter";
            }
            $file_TEMP2 .= ".";
            $file_TEMP2 .= $this->file_ext;
        }while(file_exists($this->dirname.$file_TEMP2));
        
        $TEMP = $file_TEMP2;
        return $TEMP;
    }
    
    function upload_process(){
        if($this->upload_error <> ""){
            return $this->upload_error;
        }else{
            $this->filename = $this->up_file_name();
            if (is_uploaded_file($this->file_tmp))
            {
                if (@move_uploaded_file($this->file_tmp,$this->dirname.$this->filename)){
                    $fila_path = $this->dirname.$this->filename;
                    $array_success = array("name"=>$this->filename,
                                           "dirname"=>$this->dirname,
                                           "size"=>$this->filesize,
                                           "type"=>$this->filetype,
                                           "ext"=>$this->file_ext,
                                           "original_name"=>$this->original_name,
                                           "file_path"=>$fila_path
                                           );
                    return $array_success;                    
                }else{
                    @unlink($this->file_tmp);
                    echo '<b><font face=tahoma size=2><center>ÎØÃ : ÛíÑ ÞÇÏÑ Úáì äÞá ÇáãáÝ Çáì ÇáãÌáÏ ÇáãÚíä';
                }
            }else{
                @unlink($this->file_tmp);
                 $this->upload_error = "File isn't Authorized to upload";            
            }
        }
    }
    
    function mime_check(){
        return $this->mime_error = in_array($this->filetype,$this->allowed_mime_types);
    }
    
    function ext_check(){
        return $this->mime_error = in_array($this->filetype,$this->allowed_mime_types);
    }    
} 
