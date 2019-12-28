<?php
namespace app\api\controller;

use think\Controller;
use think\Exception;
use think\Response;
use think\Request;

use app\common\FFMpegUtil;

class Ffmpeg extends Controller
{
    private $err = [
        '1001' => 'parameter is missing',
        '1002' => 'preview is fail to generate',
    ];
    
    private $msg = [
        'preview' => 'preview is succeed to generate',
    ];
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function preview(Request $request){
        $p = $request->param("p/s",'');
        $s = $request->param("s/d",config('custom.preview_second'));
        
        if(!$p){
            die(json_encode(['resultCode' => 1001,'error' => $this->err['1001']]));
        }
        
        $preview = FFMpegUtil::gen_video_preview($p, $s);
        
        if($preview){
            die(json_encode(['resultCode' => 0,'message' => $this->msg['preview'],'data' => $preview]));
        }else{
            die(json_encode(['resultCode' => 1002,'error' => $this->err['1002']]));
        }
        
    }
}