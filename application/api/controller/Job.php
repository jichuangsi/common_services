<?php
namespace app\api\controller;

use think\Exception;
use think\Queue;
use think\Controller;
use think\Request;

class Job extends Controller {
    
    private $err = [
        '2001' => 'parameter is missing',
        '2002' => 'preview job is fail to publish',
    ];
    
    private $msg = [
        'preview_job' => 'preview job is succeed to publish',
    ];
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function actionWithVideoPreviewJob(Request $request){ 
        
        $p = $request->param('p/s', '');        
        if(!$p){
            die(json_encode(['resultCode' => 2001,'error' => $this->err['2001']]));
        }
        
        $post =  json_decode($p);  
        $path = $post->p;//资源路径        
        $v = $post->v;//资源唯一标识
        $s = isset($post->s)?$post->s:config('custom.preview_second');//预览秒数
        $cbu = isset($post->callbackurl)?$post->callbackurl:'';//回调url
        $cbm = isset($post->callbackmethod)?$post->callbackmethod:'';//回调方法
        if(!$path||!$v){
            die(json_encode(['resultCode' => 2001,'error' => $this->err['2001']]));
        }
        
        $param['handler']   = config('custom.remote_service')?'app\jobs\handler\VideoPreviewJobHandler':'app\jobs\handler\VideoPreviewJobLocalHandler';
        
        $param['queue']     = "videoPreviewJobQueue";
        
        $param['data']      = [ 'ts' => time(), 'bizId' => uniqid() , 'data' => ['p'=>$path,'v'=>intval($v),'s'=>intval($s),'cbu'=>$cbu,'cbm'=>$cbm] ] ;        
        
        $ret = self::actionWithQueuePush($param);
        
        if($ret){
            die(json_encode(['resultCode' => 0,'message' => $this->msg['preview_job'],'data' => $ret]));
        }else{
            die(json_encode(['resultCode' => 2002,'error' => $this->err['2002']]));
        }
    }
    
    static private function actionWithQueuePush($param){
        $isPushed = Queue::push( $param['handler'] , $param['data'] , $param['queue'] );
        //$isPushed = Queue::later( 10, $jobHandlerClassName , $jobData , $jobQueueName );
        
        if( $isPushed !== false ){
            return true;
        }else{
            return false;
        }
    }
}