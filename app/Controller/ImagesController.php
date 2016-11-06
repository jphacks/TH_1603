<?php 

class ImagesController extends AppController {
	public $images = array('Image');

	public function add() {
		$this->autoLayout = false;
		if ($_FILES['file']) {
			//echo $this->request->data;
			$data = array('Image' => array(
						'user_id' => 1,
						'name' => $_FILES['file']['name']));
    	//画像の保存
   			if($this->Image->save($data)){
      			//画像保存先のパス
 				print_r($_FILES['file']);
 				move_uploaded_file($_FILES['file']['tmp_name'], IMAGES . DS . $_FILES['file']['name']);
    		}else{
      			echo 'Faild';
    		}
  		}
  		exit();
	}

	public function view() {
		$this->autoLayout = false;
		//ini_set('upload_max_filesize', '32M');
		if($_FILES['file']){
 			print_r($_FILES['file']);
 			move_uploaded_file($_FILES['file']['tmp_name'], IMAGES . DS . $_FILES['file']['name']);
		}
		exit();
	}

}