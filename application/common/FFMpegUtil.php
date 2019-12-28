<?php
namespace app\common;

use think\Exception;
use think\facade\Env;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\X264;
use FFMpeg\Format\Video\WMV;
use FFMpeg\Format\Video\WebM;
use FFMpeg\FFMpeg;

class FFMpegUtil
{
    //
    static public function gen_video_preview($p='', $s=0)
    {   
        if(!$p||!$s) return null;
        
        $np =  self::gen_new_name($p);
        
        if(!$np) return null;
        
        //$ffmpeg = FFMpeg::create();
		$ffmpeg = FFMpeg::create(array(
		    'ffmpeg.binaries' => Env::get('extend_path').'ffmpeg\win\bin\ffmpeg.exe',
		    'ffprobe.binaries' => Env::get('extend_path').'ffmpeg\win\bin\ffprobe.exe',
            'timeout' => 0,
            'ffmpeg.threads' => 12
        ));
		
		$video = $ffmpeg->open($p);
		$video
			->filters()
			->resize(new Dimension(320, 240))
			->synchronize();
		//截图
		/* $video
			->frame(TimeCode::fromSeconds(10))
			->save('http://192.168.31.108:92/Uploads/orange.jpg'); */
		//截视频
		$clip = $video->clip(TimeCode::fromSeconds(0), TimeCode::fromSeconds($s));
		$clip->save(new X264('aac'), $np);
		/*$video
			->save(new X264(), 'export-x264.mp4')
			->save(new WMV(), 'export-wmv.wmv')
			->save(new WebM(), 'export-webm.webm');*/
		
		if(file_exists($np)){
		    return $np;
		}else{
		    return null;
		}
    }
    
    static private function gen_new_name($s){
        if(!$s) return null;
        
        $info = pathinfo($s);        
        
        return $info["dirname"].DIRECTORY_SEPARATOR.$info["filename"].'_preview.'.$info["extension"];
    }
}