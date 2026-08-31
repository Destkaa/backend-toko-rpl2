<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KategoriController extends Controller
{
    public function index()
    {
        try {
            $kategori = Kategori::latest('id_kategori')->get();

            return response()->json([
                'status'  => true,
                'message' => 'Data Kategori berhasil diambil',
                'data'    => $kategori,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_kategori' => 'required|unique:kategoris,nama_kategori',
            ]);

            $kategori = Kategori::create([
                'nama_kategori' => $request->nama_kategori,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Data kategori berhasil dibuat',
                'data'    => $kategori,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $kategori = Kategori::find($id);

            if (! $kategori) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data kategori tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Detail data kategori berhasil diambil',
                'data'    => $kategori,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $kategori = Kategori::find($id);

            if (! $kategori) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data kategori tidak ada',
                ], 404);
            }

            $request->validate([
                'nama_kategori' => 'required|unique:kategoris,nama_kategori,' . $id . ',id_kategori',
            ]);

            $kategori->nama_kategori = $request->nama_kategori;
            $kategori->save();

            return response()->json([
                'status'  => true,
                'message' => 'Data kategori berhasil diedit',
                'data'    => $kategori,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $kategori = Kategori::find($id);

            if (! $kategori) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data kategori tidak ditemukan',
                ], 404);
            }

            $kategori->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Data kategori berhasil dihapus',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}