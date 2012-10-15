<?php

header( 'Content-Type:   text/html;   charset=utf-8 ');
require_once 'RenrenRestApiService.class.php';
$rrObj = new RenrenRestApiService;

/*
 *新用户类
 */
 
class Newuser {

	
	public $rrObj;
	public $_uid;
	public $_sessionkey;
	public $my_connect;
	
		
	
	public function __construct()
	{
		$this->_sessionkey = $_GET['sessionkey'];
			
		$this->_uid = $_GET['uid']; 
		
		//链接数据库，本代码基于新浪SAE云平台，大家可自行修改数据库地址
		@$this->my_connect = mysql_connect(SAE_MYSQL_HOST_M .':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);
		@$dbc = mysql_select_db(SAE_MYSQL_DB,$this->my_connect);
		$this->_uid = mysql_real_escape_string($this->_uid);
		$this->rrObj = new RenrenRestApiService;
		
	}
	/*
	 *获取用户基本信息，存入数据库
	 */
	public function getinfo() 
	{
		
		$params = array(
			'session_key' => $this->_sessionkey ,
			'uids'        => $this->_uid ,
			'fields'	  => 'name,sex,birthday,mainurl,hometown_location,work_history,university_history,hs_history'
			//'
			);
		$res = $this->rrObj->rr_post_curl('users.getInfo', $params);
		
		
		
		$uid = $this->_uid;
		$name = $res[0]['name'] ;
		$sex  = $res[0]['sex'];
		$birthday = $res[0]['birthday'];
		$mainurl = $res[0]['mainurl'];
		$hometown =$res[0]['hometown_location']['province'].$res[0]['hometown_location']['city'] ;
		
		if($res[0]['work_history'][0]['company_name'] != NULL)
		{
			$work = $res[0]['work_history'][0]['company_name'] ;
		}
		else
		{
			$work = 'none';
		}
		if($res[0]['university_history'][0]['name'] != NULL)
		{
			$university = $res[0]['university_history'][0]['name'].$res[0]['university_history'][0]['department'];
		}
		else
		{
			$university = 'none';
		}
		if($res[0]['university_history'][0]['year'] != NULL)
		{
			$university_year = $res[0]['university_history'][0]['year'];
		}
		else
		{
			$university_year = '0000';
		}
		if($res[0]['hs_history'][0]['name'] != NULL)
		{
			$highschool = $res[0]['hs_history'][0]['name'];
		}
		else
		{
			$highschool = 'none';
		}
		if($res[0]['hs_history'][0]['grad_year'] != NULL)
		{
			$highschool_year = $res[0]['hs_history'][0]['grad_year'];
		}
		else
		{
			$highschool_year = '0000';
		}
		
		
		
		
		
		$sql = "INSERT INTO `info` (`id`, `uid`, `name`, `sex`, `birthday`, `mainurl`, `hometown`, `work`, `university`, `universityyear`, `highschool`, `highschoolyear`) VALUES (NULL, '".$uid."', '".$name."', '".$sex."', '".$birthday."', '".$mainurl."', '".$hometown."', '".$work."', '".$university."', '".$university_year."', '".$highschool."', '".$highschool_year."')";
		mysql_query($sql,$this->my_connect);
	}
	
	/*
	 *获取用户所有照片，并将图片地址保存在数据库中
	 */
	public function getphoto()
	{
		$params = array(
			'session_key' => $this->_sessionkey ,
			'uid'        => $this->_uid ,
			'page'	  	  => '1' ,
			'count'		  => '200',
			);
		$res = $this->rrObj->rr_post_curl('photos.getAlbums', $params);
		
		$albcount = count($res);
		for($i=0;$i<$albcount;$i++)
		{
			$params = array(
				'session_key' => $this->_sessionkey ,
				'uid'        => $this->_uid ,
				'page'	  	  => '1' ,
				'count'		  => '200',
				'aid'		  => $res[$i]['aid'] ,
				);
			$res2 = $this->rrObj->rr_post_curl('photos.get', $params);
			
			$uid = $this->_uid;
			$albumname = $res[$i]['name'];
			$visible = $res[$i]['visible'];
			
			$piccount = count($res2);
			for($j=0;$j<$piccount;$j++)
			{
				$url_large = $res2[$j]['url_large'] ;
				$time = $res2[$j]['time'] ;
				$comment_count = $res2[$j]['comment_count'] ;
				$view_count = $res2[$j]['view_count'] ;
			
				if($res2[$j]['caption'] != NULL)
				{
					$photoinfo = $res2[$j]['caption'] ;
				}
				else
				{
					$photoinfo = 'none';
				}
				
				
				$sql = "INSERT INTO `photo` (`id`, `uid`, `albumname`, `url_large`, `photoinfo`, `time`, `visible`, `comment_count`, `view_count`) VALUES (NULL, '".$uid."', '".$albumname."', '".$url_large."', '".$photoinfo."', '".$time."', '".$visible."', '".$comment_count."', '".$view_count."');";
				mysql_query($sql,$this->my_connect);
			}
			
			
			
		}
		
		
		
		
		
	}
	
	/**
	 *
	 *获取用户所有状态及位置存入数据库中
	 */
	public function getstatus()
	{
		
		$params = array(
			'session_key' => $this->_sessionkey ,
			'page'	  	  => '1' ,
			'count'		  => '999',
			);
		$res = $this->rrObj->rr_post_curl('status.gets', $params);
		
		
		$uid = $this->_uid;
		$count = count($res);
		
		for($i=0;$i<$count;$i++)
		{
			if($res[$i]['forward_message'] == NULL)
			{
				$message = $res[$i]['message'];
				$sourcename = $res[$i]['source_name'];
				$time = $res[$i]['time'];
				$comment_count = $res[$i]['comment_count'];
				$status_id = $res[$i]['status_id'];
				
				if($res[$i]['place'] != NULL)
				{
					$location = $res[$i]['place']['name'];
					$longitude = $res[$i]['place']['longitude'];
					$latitude = $res[$i]['place']['latitude'];
				}
				else
				{
					$location = 'none';
					$longitude = 'none';
					$latitude = 'none';
				}
				
				$sql = "INSERT INTO `status` (`id`, `uid`, `message`, `sourcename`, `location`, `longitude`, `latitude`, `time`, `comment_count`, `status_id`) VALUES (NULL, '".$uid."', '".$message."', '".$sourcename."', '".$location."', '".$longitude."', '".$latitude."', '".$time."', '".$comment_count."', '".$status_id."');";
				mysql_query($sql,$this->my_connect);

			}
		}
	}
	
	
	
}

//老用户执行此类，没来得及写，有需要的自己写吧
class Olduser {
	
}
//对于长时间没访问的老用户，当再次访问时，收集这段时间新加的信息状态照片等，没来得及写，有需要的自己写吧
class Refreshuser {
	
}

class Retabe {
	
	public $_instance;
	public $_userstatus ;
	public $my_connect;
	
	/**
	 *判断用户类别
	 */
	public function check()
	{
		$sql = "SELECT `stamp` FROM `timecheck` WHERE `uid` = ".mysql_real_escape_string($_GET['uid'])." LIMIT 0, 30 ";
		@$result = mysql_query($sql,$this->my_connect);
		@$num = mysql_num_rows($result);
		
		
		if($num == 0)
		{
			
			$this->_userstatus = 'new' ;
			
			$time = time();
			$date = date("Y-m-d" ,time()); 
			$sql = "INSERT INTO `timecheck` (`id`, `uid`, `date`, `stamp`) VALUES (NULL, '".mysql_real_escape_string($_GET['uid'])."', '".$date."', '".$time."');";
			$result = mysql_query($sql,$this->my_connect);
		}
		else
		{
			while(@$detail = mysql_fetch_row($result))
			{
				if(time()-$detail[0] <12096000)
				$this->_userstatus = 'old' ;
				else
				{
					$this->_userstatus = 'refresh' ;
					$sql = "UPDATE `timecheck` SET `stamp` = '".time()."' WHERE `uid` = ".mysql_real_escape_string($_GET['uid'])." LIMIT 1;";
					mysql_query($sql,$this->my_connect);
				}
			}
		}
		mysql_close($this->my_connect);
	}
	
	public function __construct()
	{
		@$this->my_connect = mysql_connect(SAE_MYSQL_HOST_M .':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS);
		@$dbc = mysql_select_db(SAE_MYSQL_DB,$this->my_connect);
		
		$this->check();
		if($this->_userstatus == 'new')
		{
			$this->_instance = new Newuser();
		}
		else
		{
			if($this->_userstatus == 'old')
			{
				$this->_instance = new Olduser();
			}
			else
			{
				$this->_instance = new Refreshuser();
			}
		}
	
	}
	/*
	 *执行收集操作
	 */
	public function run()
	{
		
		$this->_instance->getinfo();
		$this->_instance->getphoto();
		$this->_instance->getstatus();
		
		
		
		
	}
	
}

$a = new Retabe();
$a->run();




	