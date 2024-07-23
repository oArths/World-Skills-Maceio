<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Brand;
use App\Models\motherboard;
use App\Models\processor;
use App\Models\rammemory;
use App\Models\storagedevice;
use App\Models\graphiccard;
use App\Models\powersupply;
use App\Models\machine;


class AllController extends Controller
{
    public function getImages($id = null)
    {

        if (!$id) {
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

        if (!isset($exist)) {
            return error('message:', 'Imagem não encontrada', 404);
        }

        $filePath = public_path("images//{$id}.png");
        $mimeType = mime_content_type($filePath);
        $file = file_get_contents($filePath);
        $encode = base64_encode($file);
        header("Content-type : $mimeType");
        return  error('a', $encode);
    }
    public function removeMachine($id = null)
    {

        if (!$id) {
            return error('message:', 'Modelo de máquina não encontrado', 404);
        }

        $machine =  machine::find($id);
        if (!$machine) {
            return error('message:', 'Modelo de máquina não encontrado', 404);
        }

        return error('', '', 204);
    }
    public function Searchitem(Request $params)
    {

        $a = $_SERVER['REQUEST_URI'];
        $clear = explode('?', $a);
        $clear = explode('/', $clear[0]);
        $search = end($clear);
        $url = "http://127.0.0.1:8000/XX/AlatechMachines/api/images/";


        $pagesize = $params->pagesize ?? 20;
        $page = $params->page ?? 1;

        $offset = ($page - 1) * $pagesize;
        $q = $params->q ?? null;


        if (!$q) {
            $exist = DB::table($search)->get();
        } else {
            $exist = DB::table($search)->where('name', "$q")->get();
        }
        $all = [];

        foreach ($exist as $category ) {

            foreach ($exist as $item) {
                $categoryDetaisl = [];

                switch ($search) {
                    case 'motherboard':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'socketTypeId' => $item->socketTypeId,
                            'ramMemoryTypeId' => $item->ramMemoryTypeId,
                            'ramMemorySlots' => $item->ramMemorySlots,
                            'maxTdp' => $item->maxTdp,
                            'sataSlots' => $item->sataSlots,
                            'm2Slots' => $item->m2Slots,
                            'pciSlots' => $item->pciSlots,
                        ];
                        break;
                    case 'processor':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'socketTypeId' => $item->socketTypeId,
                            'cores' => $item->cores,
                            'baseFrequency' => $item->baseFrequency,
                            'maxFrequency' => $item->maxFrequency,
                            'cacheMemory' => $item->cacheMemory,
                            'tdp' => $item->tdp,
                        ];
                        break;
                    case 'rammemory':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'size' => $item->size,
                            'ramMemoryTypeId ' => $item->ramMemoryTypeId,
                            'frequency' => $item->frequency,
                        ];
                        break;
                    case 'storagedevice':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'size' => $item->size,
                            'storageDeviceType ' => $item->storageDeviceType,
                            'storageDeviceInterface' => $item->storageDeviceInterface,
                        ];
                        break;
                    case 'graphiccard':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'memorySize' => $item->memorySize,
                            'memoryType ' => $item->memoryType,
                            'minimumPowerSupply' => $item->minimumPowerSupply,
                            'supportMultiGpu' => $item->supportMultiGpu,
                        ];
                        break;
                    case 'powersupply':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'potency' => $item->potency,
                            'badge80Plus ' => $item->badge80Plus,
                        ];
                        break;
                    case 'brand':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'potency' => $item->potency,
                            'badge80Plus ' => $item->badge80Plus,
                        ];
                        break;
                    case 'machine':
                        $categoryDetaisl = [
                            'motherboardId' => $item->motherboardId,
                            'processorId' => $item->processorId,
                            'ramMemoryId' => $item->ramMemoryId,
                            'ramMemoryAmount' => $item->ramMemoryAmount,
                            'graphicCardId' => $item->graphicCardId,
                            'graphicCardAmount' => $item->graphicCardAmount,
                            'powerSupplyId' => $item->powerSupplyId,
                        ];
                        break;
                    default:
                        break;
                }

                switch ($category) {
                    case 'machine':
                        $all[] = [
                            'name' => $item->name,
                            'description' => $item->description,
                            'Detalhes de Entidades' => $categoryDetaisl
                        ];
                        break;

                    case 'brand':
                        $all[] = [
                            'name' => $item->name,
                        ];
                        break;

                    default:
                        $all[] = [
                            'name' => $item->name,
                            'imageUrl' => $url . $item->imageUrl,
                            'Detalhes de Entidades' => $categoryDetaisl
                        ];
                        break;
                }
            }
        }
        $results =  array_slice($all, $offset, $pagesize);
        return $results;
    }
    public function ListItems(Request $params)
    {

        $url = "http://127.0.0.1:8000/XX/AlatechMachines/api/images/";

        $pagesize =  $params->pageSize ??  20;
        $pages  =   $params->page ?? 1;
        $offset = ($pages - 1)  * $pagesize;

        $all = [];
        $models = [
            'motherboards' => motherboard::class,
            'processor' => processor::class,
            'rammemory' => rammemory::class,
            'storagedevice' => storagedevice::class,
            'graphiccard' => graphiccard::class,
            'powersupply' => powersupply::class,
            'brand' => Brand::class,
            'machine' => machine::class,
        ];

        foreach ($models as $category => $modelsClass) {
            $data = $modelsClass::get();

            foreach ($data as $item) {
                $categoryDetaisl = [];

                switch ($category) {
                    case 'motherboards':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'socketTypeId' => $item->socketTypeId,
                            'ramMemoryTypeId' => $item->ramMemoryTypeId,
                            'ramMemorySlots' => $item->ramMemorySlots,
                            'maxTdp' => $item->maxTdp,
                            'sataSlots' => $item->sataSlots,
                            'm2Slots' => $item->m2Slots,
                            'pciSlots' => $item->pciSlots,
                        ];
                        break;
                    case 'processor':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'socketTypeId' => $item->socketTypeId,
                            'cores' => $item->cores,
                            'baseFrequency' => $item->baseFrequency,
                            'maxFrequency' => $item->maxFrequency,
                            'cacheMemory' => $item->cacheMemory,
                            'tdp' => $item->tdp,
                        ];
                        break;
                    case 'rammemory':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'size' => $item->size,
                            'ramMemoryTypeId ' => $item->ramMemoryTypeId,
                            'frequency' => $item->frequency,
                        ];
                        break;
                    case 'storagedevice':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'size' => $item->size,
                            'storageDeviceType ' => $item->storageDeviceType,
                            'storageDeviceInterface' => $item->storageDeviceInterface,
                        ];
                        break;
                    case 'graphiccard':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'memorySize' => $item->memorySize,
                            'memoryType ' => $item->memoryType,
                            'minimumPowerSupply' => $item->minimumPowerSupply,
                            'supportMultiGpu' => $item->supportMultiGpu,
                        ];
                        break;
                    case 'powersupply':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'potency' => $item->potency,
                            'badge80Plus ' => $item->badge80Plus,
                        ];
                        break;
                    case 'brand':
                        $categoryDetaisl = [
                            'brandId' => $item->brandId,
                            'potency' => $item->potency,
                            'badge80Plus ' => $item->badge80Plus,
                        ];
                        break;
                    case 'machine':
                        $categoryDetaisl = [
                            'motherboardId' => $item->motherboardId,
                            'processorId' => $item->processorId,
                            'ramMemoryId' => $item->ramMemoryId,
                            'ramMemoryAmount' => $item->ramMemoryAmount,
                            'graphicCardId' => $item->graphicCardId,
                            'graphicCardAmount' => $item->graphicCardAmount,
                            'powerSupplyId' => $item->powerSupplyId,
                        ];
                        break;
                    default:
                        break;
                }

                switch ($category) {
                    case 'machine':
                        $all[] = [
                            'category' => $category,
                            'name' => $item->name,
                            'description' => $item->description,
                            'Detalhes de Entidades' => $categoryDetaisl
                        ];
                        break;

                    case 'brand':
                        $all[] = [
                            'category' => $category,
                            'name' => $item->name,
                        ];
                        break;

                    default:
                        $all[] = [
                            'category' => $category,
                            'name' => $item->name,
                            'imageUrl' => $url . $item->imageUrl,
                            'Detalhes de Entidades' => $categoryDetaisl
                        ];
                        break;
                }
            }
        }
        $data = array_slice($all, $offset, $pagesize);

        return $data;
    }
}
