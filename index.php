<?php
	//Youtube Uploader by JWhy
	//© 2011-2012 Jasper Wiegratz
	
	//Settings
		//Developer Key
		//You can obtain a developer key at https://code.google.com/apis/youtube/dashboard/gwt/index.html#product/
		//Click on "New Product", fill in your data and paste the developer key shown there between the ''
		$user_developer_key = 'abcdefghijklmnopqrstuvxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz10987654321anexample';
	//End of Settings
	
	//Youtube is a registered trademark of YouTube, LLC
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<link href='http://fonts.googleapis.com/css?family=Nova+Square' rel='stylesheet' type='text/css'>
	<title>JWhy Youtube Uploader</title>
</head>
<body style="text-align: center; font-family: 'Nova Square', cursive; background-image:url(http://www.deviantart.com/download/122345313/Deadmau5_Wallpaper_by_kampollo.jpg); background-position: center;">
<?php
	if(@$_POST['sent'] != '1'){	//Upload data transmitted
	if(!file_exists('Zend/loader.php')){
		//die('<h3>Zend is not installed.</h3>');
	}
	
	//Get directory Content
    $dir = "./video/";
    $file_array = Array();
    if(is_dir($dir))    {
        $handle = opendir($dir);
        if(is_resource($handle))    {
            while($file = readdir($handle))    {
                if($file != "." && $file != "..")
                    array_push($file_array, $file);
            }
        }else{
            echo "Could not open the directory.";
        }
    }else{
        echo "The directory '" . $dir . "' does not exist.";
    }
	
	//Create <option> list
	$filenames_dropdown = '';
	foreach($file_array as $key=>$value) {
		$filenames_dropdown .= "<option>$value</option>";
	} 
	
	//output video upload form
		echo '
		<div>
			<form method="POST" action="' . $_SERVER['REQUEST_URI'] . '">
				<input style="opacity: 0.85;" name="sent" type="hidden" value="1">
				<div align="center" style="margin: auto;">
				<table border="0">
					<tr>
						<td>
							<p>Username</p>
						</td><td></td>
						<td>
							<input style="opacity: 0.85;" name="username" type="text" value="">
						</td>
					</tr>	<tr>
						<td>
							<p>Password</p>
						</td><td></td>
						<td>
							<input style="opacity: 0.85;" name="password" type="password">
						</td>
					</tr>
					<tr>
						<td>
							<p>Filename</p>
						</td><td></td>
						<td>video/
							<select name="filename" size="1">' . $filenames_dropdown . '
								
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<p>MIME-type</p>
						</td><td></td>
						<td>
							<input style="opacity: 0.85;" name="mime-type" type="text" value="video/avi">
						</td>
					</tr>
					<tr>
						<td>
							<p>Video title</p>
						</td><td></td>
						<td>
							<input style="opacity: 0.85;" name="title" type="text">
						</td>
					</tr>
					<tr>
						<td>
							<p>Description</p>
						</td><td></td>
						<td>
							<input style="opacity: 0.85;" name="description" type="text">
						</td>
					</tr>
					<tr>
						<td>
							<p>Category (engl.)</p>
						</td><td></td>
						<td>
							<input style="opacity: 0.85;" name="category" type="text" value="Games">
						</td>
					</tr>
					<tr>
						<td>
							<p>Tags</p>
						</td><td></td>
						<td>
							<input style="opacity: 0.85;" name="tags" type="text" value="Minecraft, Bukkit">
						</td>
					</tr>
				</table>
				<input style="opacity: 0.85;" type="submit">
				<input style="opacity: 0.85;" type="reset">
				</div>
			</form>
			<h3 style="font-family: ' . "'Verdana'" . '; font-size: 0.75em;">&copy; 2011 <a href="http://jwhy.de/">JWhy</a></h3>
		';
		} elseif(@$_POST['sent'] == '1'){//upload video to yt with given form data
		
		//init zend uploader
		require_once 'Zend/Loader.php';
		Zend_Loader::loadClass('Zend_Gdata_YouTube');
		$yt = new Zend_Gdata_YouTube();
		Zend_Loader::loadClass('Zend_Gdata_AuthSub');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');

		session_start();

		function getAuthSubRequestUrl(){
			$next = 'http://en.wiktionary.org/wiki/success';
			$scope = 'http://gdata.youtube.com';
			$secure = false;
			$session = true;
			return Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
		}
		
		function getAuthSubHttpClient() {
			if (!isset($_SESSION['sessionToken']) && !isset($_GET['token']) ){
				echo '<a href="' . getAuthSubRequestUrl() . '">Login!</a>';
				return;
			} else if (!isset($_SESSION['sessionToken']) && isset($_GET['token'])) {
			  $_SESSION['sessionToken'] = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
			}

			$httpClient = Zend_Gdata_AuthSub::getHttpClient($_SESSION['sessionToken']);
			return $httpClient;
		}
		$authenticationURL= 'https://www.google.com/accounts/ClientLogin';
		$httpClient = Zend_Gdata_ClientLogin::getHttpClient(
			$username = $_POST['username'],
			$password = $_POST['password'],
			$service = 'youtube',
			$client = null,
			$source = 'Youtube Uploader by JWhy',
			$loginToken = null,
			$loginCaptcha = null,
			$authenticationURL
		);
		$developerKey = $user_developer_key;
		$applicationId = 'Youtube Uploader by JWhy';
		$clientId = $_POST['username'] . ' using Youtube Uploader by JWhy';
		$yt = new Zend_Gdata_YouTube($httpClient, $applicationId, $clientId, $developerKey);
		$myVideoEntry = new Zend_Gdata_YouTube_VideoEntry();
		$filesource = $yt->newMediaFileSource('video/' . $_POST['filename']);
		$filesource->setContentType($_POST['mime-type']);
		$filesource->setSlug($_POST['filename']);
		$myVideoEntry->setMediaSource($filesource);
		$myVideoEntry->setVideoTitle($_POST['title']);
		$myVideoEntry->setVideoDescription($_POST['description']);
		$myVideoEntry->setVideoCategory($_POST['category']);
		$myVideoEntry->SetVideoTags($_POST['tags']);
		
		/*
				$myVideoEntry->setVideoDeveloperTags(array('mydevtag', 'anotherdevtag'));
				$yt->registerPackage('Zend_Gdata_Geo');
				$yt->registerPackage('Zend_Gdata_Geo_Extension');
				$where = $yt->newGeoRssWhere();
				$position = $yt->newGmlPos('37.0 -122.0');
				$where->point = $yt->newGmlPoint($position);
				$myVideoEntry->setWhere($where);
		*/
		
		//Do the upload
		$uploadUrl = 'http://uploads.gdata.youtube.com/feeds/api/users/default/uploads';
		try {
		  $newEntry = $yt->insertEntry($myVideoEntry, $uploadUrl, 'Zend_Gdata_YouTube_VideoEntry');
		} catch (Zend_Gdata_App_HttpException $httpException) {
		  echo $httpException->getRawResponseBody();
		} catch (Zend_Gdata_App_Exception $e) {
			echo $e->getMessage();
		}
	}
?>
</body>
</html>