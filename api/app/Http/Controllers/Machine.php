<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Machine extends Controller
{
public function getImages($id = null){

    if(!$id){
        return error('message:', 'Imagem não encontrada', 404);
    }

    $exist = 
    DB::table('storagedevice')->select('imageUrl')->where('imageUrl', $id)->union(
        DB::table('rammemory')->select('imageUrl')->where('imageUrl', $id)
    )->union(
        DB::table('processor')->select('imageUrl')->where('imageUrl', $id)
    )->union(
        DB::table('powersupply')->select('imageUrl')->where('imageUrl', $id)
    )->union(
        DB::table('motherboard')->select('imageUrl')->where('imageUrl', $id)
    )->union(
        DB::table('machine')->select('imageUrl')->where('imageUrl', $id)
    )->union(
        DB::table('graphiccard')->select('imageUrl')->where('imageUrl', $id)
    )->first();

    if(!isset($exist)){
        return error('message:', 'Imagem não encontrada', 404);
    }

    $filePath = public_path("images//{$id}.png");
    $mimeType = mime_content_type($filePath);
    $file = file_get_contents($filePath);
    $encode = base64_encode($file);
    header("Content-type : $mimeType");
    return  error('a', $encode);


}

}
