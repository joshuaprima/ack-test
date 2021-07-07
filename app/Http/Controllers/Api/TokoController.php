<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use Illuminate\Http\Request;
use \Validator;
use Illuminate\Support\Facades\Auth;
use \File;

class TokoController extends Controller
{
    public function __construct() {
        $this->middleware('auth:users');
    }

    public function createToko(Request $request){
        $count = Toko::where('userId', auth()->id())->count();
        if ($count<3){
            $rules = [
                'nama' => 'required|string',
                'alamat' => 'required',
                'kota' => 'required',
                'provinsi' => 'required',
                'foto.*' => 'image'
            ];

            $messages = [
                'nama.required' => 'Nama must be filled',
                'alamat.required' => 'Alamat must be filled',
                'kota.required' => 'Kota must be filled',
                'provinsi.required' => 'Provinsi must be filled',
                'foto.image' => 'File must be an image'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'message' => 'Something is wrong!',
                    'data' => $validator->errors()
                ],400);
            }else{
                $files = $request->file('foto');
                if (count($files)>3){
                    return response()->json([
                        'success' => false,
                        'message' => 'Can only upload 3 images!',
                    ],400);
                }else{
                    foreach ($files as $file){
                        $filename = date('YmdHis').'-'.$request->nama.'-'.$file->getClientOriginalName();
                        $folder = 'images/toko';
                        $file->move($folder, $filename);
                        $foto[] = [
                            $folder.'/'.$filename
                        ];
                    }
                    $data = Toko::create([
                        'nama' => $request->nama,
                        'alamat' => $request->alamat,
                        'kota' => $request->kota,
                        'provinsi' => $request->provinsi,
                        'userId' => auth()->id(),
                        'foto' => json_encode($foto)
                    ]);

                    if ($data){
                        return response()->json([
                            'success' => true,
                            'message' => 'Toko created!',
                            'data' => $data
                        ],200);
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to create toko!',
                        ],401);
                    }
                }
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'You can only have 3 toko!',
            ],400);
        }
    }

    public function getDetailToko($id){
        $data = Toko::find($id);
        if (!empty($data)){
            return response()->json([
                'success' => true,
                'message' => 'Detail toko obtained!',
                'data' => $data,
            ],200);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Failed to get detail toko!',
            ],401);
        }
    }

    public function getListToko(){
        $data = Toko::all();

        if (!empty($data)){
            return response()->json([
                'success' => true,
                'message' => 'List toko obtained!',
                'data' => $data,
            ],200);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Failed to get list toko!',
            ],401);
        }
    }

    public function editToko(Request $request, $id){
        $data = Toko::find($id);

        $rules = [
            'nama' => 'required|string',
            'alamat' => 'required',
            'kota' => 'required',
            'provinsi' => 'required',
            'foto.*' => 'image'
        ];

        $messages = [
            'nama.required' => 'Nama must be filled',
            'alamat.required' => 'Alamat must be filled',
            'kota.required' => 'Kota must be filled',
            'provinsi.required' => 'Provinsi must be filled',
            'foto.image' => 'File must be an image'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Something is wrong!',
                'data' => $validator->errors()
            ],400);
        }else{
            $files = $request->file('foto');
            if (count($files)>3){
                return response()->json([
                    'success' => false,
                    'message' => 'Can only upload 3 images!',
                ],400);
            }else{
                foreach ($files as $file){
                    $filename = date('YmdHis').'-'.$request->nama.'-'.$file->getClientOriginalName();
                    $folder = 'images/toko';
                    if($file->move($folder, $filename)){
                        $foto[] = [
                            $folder.'/'.$filename
                        ];
                    }
                }
                foreach (json_decode($data->foto) as $old){
                    File::delete($old);
                }

                $data->nama = $request->nama;
                $data->alamat = $request->alamat;
                $data->kota = $request->kota;
                $data->provinsi = $request->provinsi;
                $data->foto = json_encode($foto);

                if ($data->save()){
                    return response()->json([
                        'success' => true,
                        'message' => 'Toko updated!',
                        'data' => $data
                    ],200);
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to update toko!',
                    ],401);
                }
            }
        }
    }
}
