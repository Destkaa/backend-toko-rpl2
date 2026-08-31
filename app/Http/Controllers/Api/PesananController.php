<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Exception;
use Illuminate\Http\Request;

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

            $request->validate([
                'id_pelanggan'      => 'required|exists:pelanggans,id',
                'tanggal'           => 'required|date',
                'items'             => 'required|array',
                'items.*.id_produk' => 'required|exists:produks,id',
                'items.*.jumlah'    => 'required|integer|min:1',
            ]);

            $pesanan = new Pesanan;

            $pesanan->id_pelanggan = $request->id_pelanggan;
            $pesanan->tanggal      = $request->tanggal;

            $pesanan->save();

            // Siapkan data produk dan jumlah
            $produk = [];

            foreach ($request->items as $item) {

                $produk[$item['id_produk']] = [
                    'jumlah' => $item['jumlah'],
                ];

            }

            // ATTACH
            $pesanan->produk()->attach($produk);

            return response()->json([
                'status'  => true,
                'message' => 'Pesanan berhasil ditambahkan.',
                'data'    => $pesanan->load(
                    'pelanggan',
                    'produk'
                ),
            ], 201);

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

            $pesanan = Pesanan::with([
                'pelanggan',
                'produk',
            ])->find($id);

            if (! $pesanan) {

                return response()->json([
                    'status'  => false,
                    'message' => 'Pesanan tidak ditemukan.',
                ], 404);

            }

            return response()->json([
                'status' => true,
                'data'   => $pesanan,
            ]);

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

            $request->validate([
                'id_pelanggan'      => 'required|exists:pelanggans,id',
                'tanggal'           => 'required|date',

                'items'             => 'required|array',
                'items.*.id_produk' => 'required|exists:produks,id',
                'items.*.jumlah'    => 'required|integer|min:1',
            ]);

            $pesanan->id_pelanggan = $request->id_pelanggan;
            $pesanan->tanggal      = $request->tanggal;

            $pesanan->save();

            // Siapkan data produk dan jumlah
            $produk = [];

            foreach ($request->items as $item) {

                $produk[$item['id_produk']] = [
                    'jumlah' => $item['jumlah'],
                ];

            }

            // SYNC
            $pesanan->produk()->sync($produk);

            return response()->json([
                'status'  => true,
                'message' => 'Pesanan berhasil diperbarui.',
                'data'    => $pesanan->load(
                    'pelanggan',
                    'produk'
                ),
            ]);

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

            // DETACH
            $pesanan->produk()->detach();

            $pesanan->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Pesanan berhasil dihapus.',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);

        }
    }
}
