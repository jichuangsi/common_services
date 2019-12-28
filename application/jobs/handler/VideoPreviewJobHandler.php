<?php
namespace app\jobs\handler;

use think\queue\Job;

use app\common\FFMpegUtil;

class VideoPreviewJobHandler {    
    
    public function fire(Job $job, $param){
        
        $data = $param['data'];
        $isJobStillNeedToBeDone = $this->checkDatabaseToSeeIfJobNeedToBeDone($data);
        if(!$isJobStillNeedToBeDone){
            $job->delete();
            return;
        }
        
        $isJobDone = $this->doVideoPreviewJob($data);
        
        if ($isJobDone) {            
            $job->delete();
            print("<info>Video Preview Local Job(".$data["v"].") has been done and deleted at ".date('Y-m-d H:i:s')."</info>\n");
        }else{
            if ($job->attempts() > 3) {                
                print("<warn>Video Preview Local Job(".$data["v"].") has been retried more than 3 times!"."</warn>\n");
                $job->delete();                
                //print("<info>Hello Job will be availabe again after 2s."."</info>\n");
                //$job->release(2); 
            }
        }
    }    
    
    private function checkDatabaseToSeeIfJobNeedToBeDone($data){
        return true;
    }    
    
    private function doVideoPreviewJob($data) {        
        
        $p = config('custom.ftp_parent_folder').DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $data["p"]));
        $v = $data["v"];
        $s = $data["s"];
        $cbu = $data["cbu"];
        $cbm = $data["cbm"];
        
        $preview = FFMpegUtil::gen_video_preview($p, $s);
        
        if($preview){            
            if($cbu){
                $s_preview = str_replace(config('custom.ftp_parent_folder').DIRECTORY_SEPARATOR, '', $preview);
                $param['id'] = $v;
                $param['path'] = $s_preview;
                $result = json_decode(curl($cbu, $param, $cbm));
                
                if($result&&$result->resultCode===0){
                    return true;
                }else{
                    return false;
                }
            }
            return true;
        }else{
            return false;
        }
    }
}