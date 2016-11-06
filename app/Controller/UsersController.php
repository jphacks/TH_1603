<?php

class UsersController extends AppController {
	public $users = array('User', 'Keyword', 'Word');

	public function add() {
		$this->autoLayout = false;
		if ($this->request->is('post')) {
			$data = array('User' => array(
						'name' => 'Yano Sota'
					));
			$this->User->save($data);
		} else {
			echo 'Error';
		}
		exit();
	}

	public function read() {
		App::uses('Xml', 'Utility');
		App::import('Vendor', 'simple_html_dom');
		$this->autoLayout = false;

		$this->loadModel('Keyword');
		$data = $this->Keyword->find('all',
			        array(
			            'fields' => array('topic'),
			        )
				);
		$result = array();
		for($i = 0; $i < count($data); $i++) {
			$xml = Xml::build('http://news.yahoo.co.jp/pickup/'.$data[$i]['Keyword']['topic'].'/rss.xml');
			$html = file_get_html($xml->channel->item[0]->link);
			$img = $html->find('span[class=image] img', 0);
			if($img) {
				$image_url = strval($img->{'data-src'});
			} else {
				$image_url = '';
			}
			$result[] = array('title' => strval($xml->channel->item[0]->title),
								'url' => strval($xml->channel->item[0]->link),
								'image' => $image_url);
		}
		$this->response->type('json');
        echo json_encode($result);
	}

	public function news() {
		App::import('Vendor', 'simple_html_dom');
		$this->autoLayout = false;
		//URLからデータを取得する場合
		if($this->request->is('post')) {
			//print_r($this->request->data('url'));
			$html = file_get_html($this->request->data('url'));
			$element = $html->find('div[class=headlineTxt]', 0);
			$title = $element->find("a[id=link]", 0)->plaintext;
			$text = $element->find("p[class=hbody]", 0)->plaintext;
			//$arr = array($title, $text);
			//print_r($arr);

			$url = 'https://labs.goo.ne.jp/api/keyword';
			$data = array(
			    'app_id' => "4327f16bb8ce615d8fabfa012eca7278d291c6078935ec7509abc7ee61f2f7b4",
			    'title' => $title,
			    'body' => $text,
			    'max_num' => 1
			);

			$headers = array(
	    		'Content-Type: application/x-www-form-urlencoded',
			);

			$options = array("http" => array(
				"method" => "POST",
				"content" => http_build_query($data),
				'header' => implode("\r\n", $headers),
			));

			$text = file_get_contents($url, false, stream_context_create($options));
			$text = json_decode($text, true);
			//print_r($text);
			//echo key($text['keywords'][0]);

			if (key($text['keywords'][0])) {
				$this->loadModel('Word');
				$data = array('Word' => array(
								'user_id' => 1,
								'name' => key($text['keywords'][0])
							));
				$this->Word->save($data);
				$this->response->header('X-Content-Type-Options', 'nosniff');
				$this->response->header('Access-Control-Allow-Origin', '*');
				$this->response->send();
			}
		}
	}

	public function key() {
		$this->loadModel('Word');
		$this->autoLayout = false;
		$data = $this->Word->find('all',
			        array(
			            'fields' => array('name'),
			            'conditions' => array('user_id' => 1),
			            'order' => array('id DESC'),
			            'limit' => 1
			        )
				);
		$json =  array('keyword' => $data[0]['Word']['name']);
		$this->response->type('json');
        echo json_encode($json);
	}
}
?>