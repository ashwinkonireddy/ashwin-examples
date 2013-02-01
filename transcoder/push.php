<?php
// If you aren't using composer, register Pheanstalk class loader
require_once('library/pheanstalk-master/pheanstalk_init.php');


$pheanstalk = new Pheanstalk_Pheanstalk('10.0.5.10:11300');
//input files path
$ripsDir = "/projects/tryout/encoding/Rips/";

//Output files directory
$rippedDir = "/projects/tryout/encoding/Ripped/";

$profiles = array();
$profiles['flash_phone_high'] =  array("videoRate"=>1000, "resolution"=>"320x240", "fileType"=>"_flash_phone_high", "extension"=>".mp4");
$profiles['flash_phone_low']  =  array("videoRate"=>300, "resolution"=>"320x240", "fileType"=>"_flash_phone_low", "extension"=>".mp4");
$profiles['flash_phone_mid']  =  array("videoRate"=>500, "resolution"=>"320x240", "fileType"=>"_flash_phone_mid", "extension"=>".mp4");
$profiles['hd']       		  =  array("videoRate"=>1200, "resolution"=>"1280x720", "fileType"=>"_hd", "extension"=>".mp4");
$profiles['high']             =  array("videoRate"=>1000, "resolution"=>"720x480", "fileType"=>"_high", "extension"=>".mp4");
$profiles['ipad_high']        =  array("videoRate"=>1000, "resolution"=>"720x480", "fileType"=>"_ipad_high", "extension"=>".mp4");
$profiles['ipad_low']         =  array("videoRate"=>300, "resolution"=>"720x480", "fileType"=>"_ipad_low", "extension"=>".mp4");
$profiles['ipad_mid']         =  array("videoRate"=>500, "resolution"=>"720x480", "fileType"=>"_ipad_mid", "extension"=>".mp4");
$profiles['iphone_high']      =  array("videoRate"=>750, "resolution"=>"320x240", "fileType"=>"_iphone_high", "extension"=>".mp4");
$profiles['iphone_low']       =  array("videoRate"=>300, "resolution"=>"320x240", "fileType"=>"_iphone_low", "extension"=>".mp4");
$profiles['iphone_mid']       =  array("videoRate"=>500, "resolution"=>"320x240", "fileType"=>"_iphone_mid", "extension"=>".mp4");
$profiles['mobile_high']      =  array("videoRate"=>800, "resolution"=>"320x240", "fileType"=>"_mobile_high", "extension"=>".mp4");
$profiles['mobile_low']       =  array("videoRate"=>300, "resolution"=>"320x240", "fileType"=>"_mobile_low", "extension"=>".mp4");
$profiles['mobile_mid']       =  array("videoRate"=>500, "resolution"=>"320x240", "fileType"=>"_mobile_mid", "extension"=>".mp4");
$profiles['low']              =  array("videoRate"=>300, "resolution"=>"720x480", "fileType"=>"_low", "extension"=>".mp4");
$profiles['mid']              =  array("videoRate"=>500, "resolution"=>"720x480", "fileType"=>"_mid", "extension"=>".mp4");



//Bring all the files like .mp4
$files = glob($ripsDir . "*.mp4");

$jobProfiles = array();
//itterate through each file and assign the video profile to transcode the file.
foreach($files as $file)
{
	$initilizeVideoProfile = array();
	
	
	
	foreach($profiles as $profile)
	{
		//check if file already exist
		$profile['input']  = $file;
		$profile['output'] = $rippedDir;
		
		$pheanstalk
		->useTube('encoder_tube')
		->put(json_encode($profile));
	}
	
}
// $job = $pheanstalk
// ->watch('encoder_tube')
// ->ignore('default')
// ->reserve();

// echo $job->getData().'\n';
//  $pheanstalk->delete($job);

// var_dump($pheanstalk->stats());
// var_dump($pheanstalk->statsTube('encoder_tube'));

