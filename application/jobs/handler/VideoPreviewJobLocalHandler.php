<?php
namespace app\jobs\handler;

use think\queue\Job;
use think\Db;

use app\common\FFMpegUtil;

class VideoPreviewJobLocalHandler {    
    
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
    
    /**
     * 检查是否已经生成预览
     * @param unknown $data
     * @return boolean
     */
    private function checkDatabaseToSeeIfJobNeedToBeDone($data){
        $exist = Db::name('video')->where(['id'=>$data["v"],'preview'=>NULL])->count('id');       
        if($exist){
            return true;
        }
        return false;
    }    
    
    /**
     * 生成预览并更新数据库
     * @param unknown $data
     * @return boolean
     */
    private function doVideoPreviewJob($data) {        
        
        $p = config('custom.ftp_parent_folder').DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $data["p"]));
        $v = $data["v"];
        $s = $data["s"];
        
        $preview = FFMpegUtil::gen_video_preview($p, $s);
        
        if($preview){
            
            $user = Db::name('video')->field('user_id')->where(['id'=>$v])->find();            
            if($user&&$user["user_id"]){
                $upload_path = config('custom.ftp_parent_folder').DIRECTORY_SEPARATOR.$user["user_id"].DIRECTORY_SEPARATOR;
            }else{
                $upload_path = config('custom.ftp_parent_folder').DIRECTORY_SEPARATOR;
            }
            
            $s_preview = str_replace($upload_path, '', $preview);
            
            $ret = Db::name('video')->where(['id'=>$v])->update(['preview'=>$s_preview]);
            
            if($ret){
                return true;
            }
            return false;
        }else{
            return false;
        }
    }
}