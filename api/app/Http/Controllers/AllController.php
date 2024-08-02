<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Brand;
use App\Models\motherboard;
use App\Models\machinehasstoragedevice;
use App\Models\processor;
use App\Models\rammemory;
use App\Models\storagedevice;
use App\Models\graphiccard;
use App\Models\powersupply;
use App\Models\machine;
use PhpParser\Node\Stmt\Return_;

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
        return  data($encode, 200);
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
    public function CreateMachine(Request $machine)
    {

        $name  = $machine->name ?? null;
        $description  = $machine->description ?? null;
        $motherboardId  = $machine->motherboardId ?? null;
        $powerSupplyId  = $machine->powerSupplyId ?? null;
        $processorId  = $machine->processorId ?? null;
        $ramMemoryId  = $machine->ramMemoryId ?? null;
        $ramMemoryAmount  = $machine->ramMemoryAmount ?? null;
        $storageDeviceId  = $machine->storageDevices[0]['storageDeviceId'] ?? null;
        $amount  = $machine->storageDevices[0]['amount'] ?? null;
        $graphicCardId  = $machine->graphicCardId ?? null;
        $graphicCardAmount  = $machine->graphicCardAmount ?? null;
        $image  = $machine->imageBase64 ?? null;

        if (!$motherboardId) {
            return data([
                'motherboardId' => 'É necessario ao menos uma motherboardId',
            ]);
        }
        if (!$powerSupplyId) {
            return data([
                'powerSupplyId' => 'É necessario ao menos uma powerSupplyId'
            ]);
        }
        if (
            !$motherboardId || !$powerSupplyId || !$processorId ||
            !$ramMemoryAmount || !$storageDeviceId || !$amount || !$graphicCardId || !$graphicCardAmount || !$image
        ) {
            return data([
                'all' => 'todos os items são obrigatorios'
            ], 422);
        }

        $motherboard = motherboard::find($motherboardId);
        $power = powersupply::find($powerSupplyId);
        $processor = processor::find($processorId);
        $memory = rammemory::find($ramMemoryId);
        $store = storagedevice::find($storageDeviceId);
        $grafic = graphiccard::find($graphicCardId);

        if (!$processor & !$memory && !$store && !$grafic) {
            return data([
                'message' => 'Máquina válida'
            ], 200);
        }
        if ($motherboard->socketTypeId !== $processor->socketTypeId) {
            return data([
                'motherboard' => 'Tipo de soquete da placa-mãe é diferente do tipo de soquete do processador'
            ], 422);
        }
        if ($processor->tdp > $motherboard->maxTdp) {
            return data([
                'processor' => 'TDP do processador é maior do que o TDP máximo suportado pela placa-mãe'
            ], 422);
        }
        if ($memory->ramMemoryTypeId !== $motherboard->ramMemoryTypeId) {
            return data([
                'ramMemory' => 'Tipo de memória RAM da placa-mãe é diferente do tipo da memória RAM'
            ], 422);
        }
        if ($ramMemoryAmount > $motherboard->ramMemorySlots || $ramMemoryAmount < 1) {
            return data([
                'ramMemory' => 'Quantidade de memórias RAM é maior do que a quantidade de slots presentes na placa-mãe e deve ter nom minimo uma'
            ], 422);
        }
        if ($graphicCardAmount > $motherboard->pciSlots || $graphicCardAmount < 1) {
            return data([
                'graphicCard' => 'Quantidade de placas de vídeo é maior do que a quantidade de slots PCI Express na placamãe e deve ter nom minimo uma'
            ], 422);
        }


        switch ($store->storageDeviceInterface) {
            case 'sata':
                if ($amount > $motherboard->sataSlots) {
                    return data([
                        'storageDevices' => 'Quantidade de dispositivos de armazenamento do tipo SATA é maior do que a quantidade de
slots SATA na placa mãe'
                    ], 422);
                }
                break;
            default:
                if ($amount > $motherboard->m2Slots) {
                    return data([
                        'storageDevices' => 'Quantidade de dispositivos de armazenamento do tipo M2 é maior do que a quantidade de
slots M2 na placa mãe'
                    ], 422);
                }
                break;
        }

        if ($amount < 1 || $storageDeviceId < 1) {
            return data([
                'storageDevices' => 'Soma total de dispositivos de armazenamento é igual a zero deve ter ao menos um '
            ], 422);
        }
        if ($graphicCardAmount > 1 && $grafic->supportMultiGpu < 1) {
            return data([
                'graphicCard' => 'Quantidade de placas de vídeo é maior que 1 o modelo de placa de vídeo não suporta
SLI/Crossfire'
            ], 422);
        }
        if ($power->potency <  $grafic->minimumPowerSupply * $graphicCardAmount) {
            return data([
                'powerSupply' => 'Potência da fonte de alimentação é menor do que a potência mínima da placa de vídeo multiplicada pela quantidade de placas de vídeo'
            ], 422);
        }
        $clera = $image;

        if (strpos($image, ',') !== false) {
            $position = strpos($image, ',');
            $clera = substr($image, $position + 1);
        }

        // return  $clera;
        $clearimage =  base64_decode($clera);

        $nameimage = time();

        $publicpath = public_path('images');
        $imagepath = $publicpath . "/" . $nameimage . ".png";
        file_put_contents($imagepath, $clearimage);


        $machine = [
            "name" => $name,
            "description" => $description,
            "motherboardId" => $motherboardId,
            "powerSupplyId" => $powerSupplyId,
            "processorId" => $processorId,
            "ramMemoryId" => $ramMemoryId,
            "ramMemoryAmount" => $ramMemoryAmount,
            "graphicCardId" => $graphicCardId,
            "imageUrl" => $nameimage,
            "graphicCardAmount" => $graphicCardAmount

        ];
        $Newmachine = machine::create($machine);

        $storegeUpdate = [
            "machineId" => $Newmachine->id,
            "storageDeviceId" => $storageDeviceId,
            "amount" => $amount,
        ];
        machinehasstoragedevice::create($storegeUpdate);

        return data(['id' => $Newmachine->id], 200);
    }
    public function UpdateMachine($id)
    {
        $machine = request();

        $description  = $machine->description ?? null;
        $name  = $machine->name ?? null;
        $idMachine = $id ?? null;
        $motherboardId  = $machine->motherboardId ?? null;
        $powerSupplyId  = $machine->powerSupplyId ?? null;
        $processorId  = $machine->processorId ?? null;
        $ramMemoryId  = $machine->ramMemoryId ?? null;
        $ramMemoryAmount  = $machine->ramMemoryAmount ?? null;
        $storageDeviceId  = $machine->storageDevices[0]['storageDeviceId'] ?? null;
        $amount  = $machine->storageDevices[0]['amount'] ?? null;
        $graphicCardId  = $machine->graphicCardId ?? null;
        $graphicCardAmount  = $machine->graphicCardAmount ?? null;
        $image  = $machine->imageBase64 ?? null;

        if (!$idMachine) {
            return data([
                'machine' => 'É necessario o id da maquina',
            ], 422);
        }
        if (!$motherboardId) {
            return data([
                'motherboardId' => 'É necessario ao menos uma motherboardId',
            ], 422);
        }
        if (!$powerSupplyId) {
            return data([
                'powerSupplyId' => 'É necessario ao menos uma powerSupplyId'
            ], 422);
        }
        if (
            !$motherboardId || !$powerSupplyId || !$processorId ||
            !$ramMemoryAmount || !$storageDeviceId || !$amount || !$graphicCardId || !$graphicCardAmount
        ) {
            return data([
                'all' => 'todos os items são obrigatorios'
            ], 422);
        }

        $motherboard = motherboard::find($motherboardId);
        $power = powersupply::find($powerSupplyId);
        $processor = processor::find($processorId);
        $memory = rammemory::find($ramMemoryId);
        $store = storagedevice::find($storageDeviceId);
        $grafic = graphiccard::find($graphicCardId);

        if (!$processor & !$memory && !$store && !$grafic) {
            return data([
                'message' => 'Máquina válida'
            ], 200);
        }
        if ($motherboard->socketTypeId !== $processor->socketTypeId) {
            return data([
                'motherboard' => 'Tipo de soquete da placa-mãe é diferente do tipo de soquete do processador'
            ], 422);
        }
        if ($processor->tdp > $motherboard->maxTdp) {
            return data([
                'processor' => 'TDP do processador é maior do que o TDP máximo suportado pela placa-mãe'
            ], 422);
        }
        if ($memory->ramMemoryTypeId !== $motherboard->ramMemoryTypeId) {
            return data([
                'ramMemory' => 'Tipo de memória RAM da placa-mãe é diferente do tipo da memória RAM'
            ], 422);
        }
        if ($ramMemoryAmount > $motherboard->ramMemorySlots || $ramMemoryAmount < 1) {
            return data([
                'ramMemory' => 'Quantidade de memórias RAM é maior do que a quantidade de slots presentes na placa-mãe e deve ter nom minimo uma'
            ], 422);
        }
        if ($graphicCardAmount > $motherboard->pciSlots || $graphicCardAmount < 1) {
            return data([
                'graphicCard' => 'Quantidade de placas de vídeo é maior do que a quantidade de slots PCI Express na placamãe e deve ter nom minimo uma'
            ], 422);
        }


        switch ($store->storageDeviceInterface) {
            case 'sata':
                if ($amount > $motherboard->sataSlots) {
                    return data([
                        'storageDevices' => 'Quantidade de dispositivos de armazenamento do tipo SATA é maior do que a quantidade de
slots SATA na placa mãe'
                    ], 422);
                }
                break;
            default:
                if ($amount > $motherboard->m2Slots) {
                    return data([
                        'storageDevices' => 'Quantidade de dispositivos de armazenamento do tipo M2 é maior do que a quantidade de
slots M2 na placa mãe'
                    ], 422);
                }
                break;
        }

        if ($amount < 1 || $storageDeviceId < 1) {
            return data([
                'storageDevices' => 'Soma total de dispositivos de armazenamento é igual a zero deve ter ao menos um '
            ], 422);
        }
        if ($graphicCardAmount > 1 && $grafic->supportMultiGpu < 1) {
            return data([
                'graphicCard' => 'Quantidade de placas de vídeo é maior que 1 o modelo de placa de vídeo não suporta
SLI/Crossfire'
            ], 422);
        }
        if ($power->potency <  $grafic->minimumPowerSupply * $graphicCardAmount) {
            return data([
                'powerSupply' => 'Potência da fonte de alimentação é menor do que a potência mínima da placa de vídeo multiplicada pela quantidade de placas de vídeo'
            ], 422);
        }

        $nameimage = '';
        if ($image) {

            $clera = $image;

            if (strpos($image, ',') !== false) {
                $position = strpos($image, ',');
                $clera = substr($image, $position + 1);
            }

            // return  $clera;
            $clearimage =  base64_decode($clera);

            $nameimage = time();

            $publicpath = public_path('images');
            $imagepath = $publicpath . "/" . $nameimage . ".png";
            file_put_contents($imagepath, $clearimage);
        }

        $oldDevice = machinehasstoragedevice::where('machineId', $idMachine)->first();
        $machineNew = [
            "name" => $name,
            "description" => $description,
            "motherboardId" => $motherboardId,
            "powerSupplyId" => $powerSupplyId,
            "processorId" => $processorId,
            "ramMemoryId" => $ramMemoryId,
            "ramMemoryAmount" => $ramMemoryAmount,
            "graphicCardId" => $graphicCardId,
            "graphicCardAmount" => $graphicCardAmount

        ];
        if ($image) {
            $machineNew['imageUrl'] = $nameimage;
            machine::where('id', $idMachine)->update($machineNew);
        } else {
            machine::where('id', $idMachine)->update($machineNew);
        }
        $storege = [
            "machineId" => $idMachine,
            "storageDeviceId" => $storageDeviceId,
            "amount" => $amount,
        ];
        machinehasstoragedevice::where('machineId', $idMachine)->where('storageDeviceId', $oldDevice->storageDeviceId)->where('amount', $oldDevice->amount)->update($storege);

        return data([
            array_merge($machineNew, $storege)
        ], 200);
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
            $exist = DB::table($search)->where('name', 'LIKE', '%' . $q . '%')->get();
        }
        $all = [];

        foreach ($exist as $category) {

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
        return response()->json([
            $results
        ], 200);
    }
    public function VerifyComp(Request $machine)
    {

        $motherboardId  = $machine->motherboardId ?? null;
        $powerSupplyId  = $machine->powerSupplyId ?? null;
        $processorId  = $machine->processorId ?? null;
        $ramMemoryId  = $machine->ramMemoryId ?? null;
        $ramMemoryAmount  = $machine->ramMemoryAmount ?? null;
        $storageDeviceId  = $machine->storageDevices[0]['storageDeviceId'] ?? null;
        $amount  = $machine->storageDevices[0]['amount'] ?? null;
        $graphicCardId  = $machine->graphicCardId ?? null;
        $graphicCardAmount  = $machine->graphicCardAmount ?? null;
        $errors = [];


        //soquete placa mae porcessador
        $motherboard = motherboard::find($motherboardId);
        $power = powersupply::find($powerSupplyId);
        $processor = processor::find($processorId);
        $memory = rammemory::find($ramMemoryId);
        $store = storagedevice::find($storageDeviceId);
        $grafic = graphiccard::find($graphicCardId);

        if ($motherboard === null) {
            $errors['motherboardId']  = 'É necessario ao menos uma motherboardId';
        }
        if ($power === null) {
            $errors['powerSupplyId']  = 'É necessario ao menos uma powerSupplyId';
        }
        // return $errors;
        if (!$processor & !$memory && !$store && !$grafic && $power && $motherboard) {
            return data([
                'message' => 'Máquina válida'
            ], 200);
        }
        // if ($errors) {
        //     return data($errors, 422);
        // }
        if (isset($motherboard) && isset($processor)) {

            if ($motherboard->socketTypeId !== $processor->socketTypeId) {
                $errors['motherboard']  = 'Tipo de soquete da placa-mãe é diferente do tipo de soquete do processador';
            }
            if ($processor->tdp > $motherboard->maxTdp) {
                $errors['processor'] = 'TDP do processador é maior do que o TDP máximo suportado pela placa-mãe';
            }
        }
        if (isset($memory) && isset($motherboard)) {

            if ($memory->ramMemoryTypeId !== $motherboard->ramMemoryTypeId) {
                $errors['ramMemory'] = 'Tipo de memória RAM da placa-mãe é diferente do tipo da memória RAM';
            }
        }
        if (isset($ramMemoryAmount) && isset($motherboard)) {

            if ($ramMemoryAmount > $motherboard->ramMemorySlots || $ramMemoryAmount < 1) {
                $errors['ramMemory'] = 'Quantidade de memórias RAM é maior do que a quantidade de slots presentes na placa-mãe e deve ter nom minimo uma';
            }
        }
        if (isset($graphicCardAmount) && isset($motherboard)) {
            if ($graphicCardAmount > $motherboard->pciSlots || $graphicCardAmount < 1) {
                $errors['graphicCard'] = 'Quantidade de placas de vídeo é maior do que a quantidade de slots PCI Express na placamãe e deve ter nom minimo uma';
            }
        }


        if (isset($store) && isset($motherboard)) {
            switch ($store->storageDeviceInterface) {
                case 'sata':
                    if ($amount > $motherboard->sataSlots) {
                        $errors['storageDevices'] = 'Quantidade de dispositivos de armazenamento do tipo SATA é maior do que a quantidade de
slots SATA na placa mãe';
                    }
                    break;
                default:
                    if ($amount > $motherboard->m2Slots) {
                        $errors['storageDevices'] = 'Quantidade de dispositivos de armazenamento do tipo M2 é maior do que a quantidade de
slots M2 na placa mãe';
                    }
                    break;
            }
        }
        if (isset($amount) && isset($storageDeviceId)) {

            if ($amount < 1 || $storageDeviceId < 1) {
                $errors['storageDevices'] = 'Soma total de dispositivos de armazenamento é igual a zero deve ter ao menos um ';
            }
        }

        if (isset($graphicCardAmount) && isset($grafic)) {
            if ($graphicCardAmount > 1 && $grafic->supportMultiGpu < 1) {
                $errors['graphicCard'] = 'Quantidade de placas de vídeo é maior que 1 o modelo de placa de vídeo não suporta
    SLI/Crossfire';
            }
        }

        if (isset($power) && isset($grafic)) {

            if ($power->potency <  $grafic->minimumPowerSupply * $graphicCardAmount) {
                $errors['powerSupply'] = 'Potência da fonte de alimentação é menor do que a potência mínima da placa de vídeo multiplicada  pela quantidade de placas de vídeo';
            }
        }

        if ($errors) {
            return data($errors, 422);
        }

        return data([
            'message' => 'Máquina válida'
        ], 200);
    }
    public function Listmotherboards(Request $params)
    {
        $url = "http://127.0.0.1:8000/XX/AlatechMachines/api/images/";

        $pagesize =  $params->pageSize ??  20;
        $pages  =   $params->page ?? 1;
        $offset = ($pages - 1)  * $pagesize;

        $data = motherboard::all();
        $all = [];
        foreach ($data as $item) {
            $all[] = [
                'id' => $item->id,
                'name' => $item->name,
                'imageUrl' => $url . $item->imageUrl,
                'brandId' => $item->brandId,
                'socketTypeId' => $item->socketTypeId,
                'ramMemoryTypeId' => $item->ramMemoryTypeId,
                'ramMemorySlots' => $item->ramMemorySlots,
                'maxTdp' => $item->maxTdp,
                'sataSlots' => $item->sataSlots,
                'm2Slots' => $item->m2Slots,
                'pciSlots' => $item->pciSlots,
            ];
        }

        $results = array_slice($all, $offset, $pagesize);
        return response()->json([$results], 200);
    }
    public function Listprocessor(Request $params)
    {
        $url = "http://127.0.0.1:8000/XX/AlatechMachines/api/images/";

        $pagesize =  $params->pageSize ??  20;
        $pages  =   $params->page ?? 1;
        $offset = ($pages - 1)  * $pagesize;

        $data = processor::all();
        $all = [];
        foreach ($data as $item) {
            $all[] = [
                'id' => $item->id,
                'name' => $item->name,
                'imageUrl' => $url . $item->imageUrl,
                'brandId' => $item->brandId,
                'socketTypeId' => $item->socketTypeId,
                'cores' => $item->cores,
                'baseFrequency' => $item->baseFrequency,
                'maxFrequency' => $item->maxFrequency,
                'cacheMemory' => $item->cacheMemory,
                'tdp' => $item->tdp,
            ];
        }

        $results = array_slice($all, $offset, $pagesize);
        return response()->json(($results), 200);
    }
    public function Listrammemory(Request $params)
    {
        $url = "http://127.0.0.1:8000/XX/AlatechMachines/api/images/";

        $pagesize =  $params->pageSize ??  20;
        $pages  =   $params->page ?? 1;
        $offset = ($pages - 1)  * $pagesize;

        $data = rammemory::all();
        $all = [];
        foreach ($data as $item) {
            $all[] = [
                'id' => $item->id,
                'name' => $item->name,
                'imageUrl' => $url . $item->imageUrl,
                'brandId' => $item->brandId,
                'size' => $item->size,
                'ramMemoryTypeId ' => $item->ramMemoryTypeId,
                'frequency' => $item->frequency,
            ];
        }

        $results = array_slice($all, $offset, $pagesize);
        return response()->json(($results), 200);
    }
    public function Liststoragedevice(Request $params)
    {
        $url = "http://127.0.0.1:8000/XX/AlatechMachines/api/images/";

        $pagesize =  $params->pageSize ??  20;
        $pages  =   $params->page ?? 1;
        $offset = ($pages - 1)  * $pagesize;

        $data = storagedevice::all();
        $all = [];
        foreach ($data as $item) {
            $all[] = [
                'id' => $item->id,
                'name' => $item->name,
                'imageUrl' => $url . $item->imageUrl,
                'brandId' => $item->brandId,
                'size' => $item->size,
                'storageDeviceType ' => $item->storageDeviceType,
                'storageDeviceInterface' => $item->storageDeviceInterface,
            ];
        }

        $results = array_slice($all, $offset, $pagesize);
        return response()->json(($results), 200);
    }
    public function Listgraphiccard(Request $params)
    {
        $url = "http://127.0.0.1:8000/XX/AlatechMachines/api/images/";

        $pagesize =  $params->pageSize ??  20;
        $pages  =   $params->page ?? 1;
        $offset = ($pages - 1)  * $pagesize;

        $data = graphiccard::all();
        $all = [];
        foreach ($data as $item) {
            $all[] = [
                'id' => $item->id,
                'name' => $item->name,
                'imageUrl' => $url . $item->imageUrl,
                'brandId' => $item->brandId,
                'memorySize' => $item->memorySize,
                'memoryType ' => $item->memoryType,
                'minimumPowerSupply' => $item->minimumPowerSupply,
                'supportMultiGpu' => $item->supportMultiGpu,
            ];
        }

        $results = array_slice($all, $offset, $pagesize);
        return response()->json(($results), 200);
    }
    public function Listpowersupply(Request $params)
    {
        $url = "http://127.0.0.1:8000/XX/AlatechMachines/api/images/";

        $pagesize =  $params->pageSize ??  20;
        $pages  =   $params->page ?? 1;
        $offset = ($pages - 1)  * $pagesize;

        $data = powersupply::all();
        $all = [];
        foreach ($data as $item) {
            $all[] = [
                'id' => $item->id,
                'name' => $item->name,
                'imageUrl' => $url . $item->imageUrl,
                'brandId' => $item->brandId,
                'potency' => $item->potency,
                'badge80Plus ' => $item->badge80Plus,
            ];
        }

        $results = array_slice($all, $offset, $pagesize);
        return response()->json(($results), 200);
    }
    public function Listbrand(Request $params)
    {

        $pagesize =  $params->pageSize ??  20;
        $pages  =   $params->page ?? 1;
        $offset = ($pages - 1)  * $pagesize;

        $data = Brand::all();
        $all = [];
        foreach ($data as $item) {
            $all[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }

        $results = array_slice($all, $offset, $pagesize);
        return response()->json(($results), 200);
    }
    public function Listmachine(Request $params)
    {
        $url = "http://127.0.0.1:8000/XX/AlatechMachines/api/images/";

        $pagesize =  $params->pageSize ??  20;
        $pages  =   $params->page ?? 1;
        $offset = ($pages - 1)  * $pagesize;

        $data = machine::all();
        $all = [];
        foreach ($data as $item) {
            $all[] = [
                'id' => $item->id,
                'name' => $item->name,
                'imageUrl' => $url . $item->imageUrl,
                'description' => $item->description,
                'motherboardId' => $item->motherboardId,
                'processorId' => $item->processorId,
                'ramMemoryId' => $item->ramMemoryId,
                'ramMemoryAmount' => $item->ramMemoryAmount,
                'graphicCardId' => $item->graphicCardId,
                'graphicCardAmount' => $item->graphicCardAmount,
                'powerSupplyId' => $item->powerSupplyId,
            ];
        }

        $results = array_slice($all, $offset, $pagesize);
        return response()->json(($results), 200);
    }
   
}
