<?php
function jsonResponse($messsage, $status = 201 ,$dataName, $data = [] ){
    return response()->json([
        'message' => $messsage,
        $dataName => $data,
    ], $status);
}
function error($messsageName, $messsage, $status = 400 ){
    return response()->json([
        $messsageName => $messsage,
    ], $status);
}
function data($data, $status = 400 ){
    return response()->json([$data], $status);
}