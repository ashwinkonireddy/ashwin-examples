<?php
// If you aren't using composer, register Pheanstalk class loader
require_once('library/pheanstalk-master/pheanstalk_init.php');


$pheanstalk = new Pheanstalk_Pheanstalk('10.0.5.10:11300');


while(1) {
$job = $pheanstalk
->watch('encoder_tube')
->ignore('default')
->reserve();

$videoProfile = (array)json_decode($job->getData());

var_dump($videoProfile);
$input		= $videoProfile["input"];
$output     = basename($videoProfile["input"]);
$videoRate  = $videoProfile["videoRate"]."k";
$resolution = $videoProfile["resolution"];
$fileType   = $videoProfile["fileType"];
$outputPath = $videoProfile["output"];
$outputfile = $outputPath.$output.$fileType.".mp4";

encode($input, $videoRate, $resolution, $outputfile);
echo "-- Deleted --";
$pheanstalk->delete($job);
}

function encode($input, $videoRate, $resolution, $outputfile)
{
	exec("ffmpeg -i '$input' -vcodec libfacc -vcodec libx264 -b:a 192 -b:v $videoRate -s $resolution -flags +aic+mv4 '$outputfile'");
	return true;
}
