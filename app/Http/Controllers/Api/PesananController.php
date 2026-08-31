<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    public function index()
    {
        try {
            $pesanan = Pesanan::with([
                'pelanggan',
                'produk',
            ])->get();

            return response()->json([
                'status'  => true,
                'message' => 'Data pesanan berhasil diambil.',
                'data'    => $pesanan,
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
            // PERBAIKAN: Gunakan 'exists:pelanggans,id' jika nama kolom di DB adalah 'id'
            // Jika kolom di DB memang 'id_pelanggan', biarkan 'exists:pelanggans,id_pelanggan'
            $request->validate([
                'id_pelanggan'      => 'required|exists:pelanggans,id',
                'tanggal'           => 'required|date',
                'items'             => 'required|array|min:1',
                'items.*.id_produk' => 'required|exists:produks,id',
                'items.*.jumlah'    => 'required|integer|min:1',
            ]);

            $pesanan = new Pesanan;
            $pesanan->id_pelanggan = $request->id_pelanggan;
            $pesanan->tanggal      = $request->tanggal;
            $pesanan->save();

            $produk = [];
            foreach ($request->items as $item) {
                $produk[$item['id_produk']] = [
                    'jumlah' => $item['jumlah'],
                ];
            }

            $pesanan->produk()->attach($produk);

            return response()->json([
                'status'  => true,
                'message' => 'Pesanan berhasil ditambahkan.',
                'data'    => $pesanan->load('pelanggan', 'produk'),
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
            $pesanan = Pesanan::with(['pelanggan', 'produk'])->find($id);

            if (! $pesanan) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Pesanan tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data'   => $pesanan,
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
            $pesanan = Pesanan::find($id);

            if (! $pesanan) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Pesanan tidak ditemukan.',
                ], 404);
            }

            // PERBAIKAN: Samakan validasi exists dengan struktur primary key DB
            $request->validate([
                'id_pelanggan'      => 'required|exists:pelanggans,id',
                'tanggal'           => 'required|date',
                'items'             => 'required|array|min:1',
                'items.*.id_produk' => 'required|exists:produks,id',
                'items.*.jumlah'    => 'required|integer|min:1',
            ]);

            $pesanan->id_pelanggan = $request->id_pelanggan;
            $pesanan->tanggal      = $request->tanggal;
            $pesanan->save();

            $produk = [];
            foreach ($request->items as $item) {
                $produk[$item['id_produk']] = [
                    'jumlah' => $item['jumlah'],
                ];
            }

            $pesanan->produk()->sync($produk);

            return response()->json([
                'status'  => true,
                'message' => 'Pesanan berhasil diperbarui.',
                'data'    => $pesanan->load('pelanggan', 'produk'),
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
            $pesanan = Pesanan::find($id);

            if (! $pesanan) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Pesanan tidak ditemukan.',
                ], 404);
            }

            // Lepas relasi pivot detail_pesanan terlebih dahulu
            $pesanan->produk()->detach();

            // Hapus data utama pesanan
            $pesanan->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Pesanan berhasil dihapus.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}