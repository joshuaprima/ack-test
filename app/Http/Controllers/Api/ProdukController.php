<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use \Validator;
use Illuminate\Support\Facades\Auth;
use \File;

class ProdukController extends Controller
{
    public function __construct() {
        $this->middleware('auth:users');
    }

    public function createProduk(Request $request, $id){
        $count = Produk::where('tokoId', $id)->count();
        if ($count<100){
            $rules = [
                'nama' => 'required|string',
                'harga' => 'required',
                'stok' => 'required',
                'rak' => 'required',
                'foto.*' => 'image'
            ];

            $messages = [
                'nama.required' => 'Nama must be filled',
                'harga.required' => 'Harga must be filled',
                'stok.required' => 'Stok must be filled',
                'rak.required' => 'rak must be filled',
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
                if (count($files)>3) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Can only upload 3 images!',
                    ], 400);
                }else{
                    foreach ($files as $file){
                        $filename = date('YmdHis').'-'.$request->nama.'-'.$file->getClientOriginalName();
                        $folder = 'images/produk';
                        $file->move($folder, $filename);
                        $foto[] = [
                            $folder.'/'.$filename
                        ];
                    }
                    $data = Produk::create([
                        'nama' => $request->nama,
                        'harga' => $request->harga,
                        'stok' => $request->stok,
                        'rak' => $request->rak,
                        'tokoId' => $id,
                        'foto' => json_encode($foto)
                    ]);

                    if ($data){
                        return response()->json([
                            'success' => true,
                            'message' => 'Produk created!',
                            'data' => $data
                        ],200);
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to create produk!',
                        ],401);
                    }
                }
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'You can only have 100 produk!',
            ],400);
        }
    }

    public function getDetailProduk($id){
        $data = Produk::find($id);
        if (!empty($data)){
            return response()->json([
                'success' => true,
                'message' => 'Detail produk obtained!',
                'data' => $data,
            ],200);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Failed to get detail produk!',
            ],401);
        }
    }

    public function getListProduk(){
        $data = Produk::all();

        if (!empty($data)){
            return response()->json([
                'success' => true,
                'message' => 'List produk obtained!',
                'data' => $data
            ],200);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Failed to get list produk!',
            ],401);
        }
    }

    public function editProduk(Request $request, $id){
        $data = Produk::find($id);

        $rules = [
            'nama' => 'required|string',
            'harga' => 'required',
            'stok' => 'required',
            'rak' => 'required',
            'foto.*' => 'image'
        ];

        $messages = [
            'nama.required' => 'Nama must be filled',
            'harga.required' => 'Harga must be filled',
            'stok.required' => 'Stok must be filled',
            'rak.required' => 'rak must be filled',
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
                    $folder = 'images/produk';
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
                $data->harga = $request->harga;
                $data->stok = $request->stok;
                $data->rak = $request->rak;
                $data->foto = json_encode($foto);

                if ($data->save()){
                    return response()->json([
                        'success' => true,
                        'message' => 'Produk updated!',
                        'data' => $data
                    ],200);
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to update produk!',
                    ],401);
                }
            }
        }
    }
}
