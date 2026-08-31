<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $request->validate([
                'id_pelanggan'      => 'required|exists:pelanggans,id',
                'tanggal'           => 'required|date',
                'items'             => 'required|array|min:1',
                'items.*.id_produk' => 'required|exists:produks,id',
                'items.*.jumlah'    => 'required|integer|min:1',
            ]);

            // Cek kecukupan stok seluruh produk sebelum diproses
            foreach ($request->items as $item) {
                $produkModel = Produk::find($item['id_produk']);
                if ($produkModel->stok < $item['jumlah']) {
                    return response()->json([
                        'status'  => false,
                        'message' => "Stok produk '{$produkModel->nama_barang}' tidak mencukupi. Tersisa: {$produkModel->stok}",
                    ], 422);
                }
            }

            $pesanan = DB::transaction(function () use ($request) {
                $pesanan = new Pesanan;
                $pesanan->id_pelanggan = $request->id_pelanggan;
                $pesanan->tanggal      = $request->tanggal;
                $pesanan->save();

                $produkAttach = [];
                foreach ($request->items as $item) {
                    $produkAttach[$item['id_produk']] = [
                        'jumlah' => $item['jumlah'],
                    ];

                    // Kurangi stok produk
                    Produk::where('id', $item['id_produk'])->decrement('stok', $item['jumlah']);
                }

                $pesanan->produk()->attach($produkAttach);

                return $pesanan;
            });

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
            $pesanan = Pesanan::with('produk')->find($id);

            if (! $pesanan) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Pesanan tidak ditemukan.',
                ], 404);
            }

            $request->validate([
                'id_pelanggan'      => 'required|exists:pelanggans,id',
                'tanggal'           => 'required|date',
                'items'             => 'required|array|min:1',
                'items.*.id_produk' => 'required|exists:produks,id',
                'items.*.jumlah'    => 'required|integer|min:1',
            ]);

            DB::transaction(function () use ($pesanan, $request) {
                // 1. Kembalikan stok lama sebelum dihitung ulang
                foreach ($pesanan->produk as $pLama) {
                    Produk::where('id', $pLama->id)->increment('stok', $pLama->pivot->jumlah);
                }

                // 2. Cek apakah stok cukup untuk data baru
                foreach ($request->items as $item) {
                    $produkModel = Produk::find($item['id_produk']);
                    if ($produkModel->stok < $item['jumlah']) {
                        throw new Exception("Stok produk '{$produkModel->nama_barang}' tidak mencukupi. Tersisa: {$produkModel->stok}");
                    }
                }

                // 3. Update data pesanan
                $pesanan->id_pelanggan = $request->id_pelanggan;
                $pesanan->tanggal      = $request->tanggal;
                $pesanan->save();

                // 4. Potong stok baru & persiapkan sync
                $produkSync = [];
                foreach ($request->items as $item) {
                    $produkSync[$item['id_produk']] = [
                        'jumlah' => $item['jumlah'],
                    ];

                    Produk::where('id', $item['id_produk'])->decrement('stok', $item['jumlah']);
                }

                $pesanan->produk()->sync($produkSync);
            });

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
            $pesanan = Pesanan::with('produk')->find($id);

            if (! $pesanan) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Pesanan tidak ditemukan.',
                ], 404);
            }

            DB::transaction(function () use ($pesanan) {
                // Kembalikan stok produk saat pesanan dihapus
                foreach ($pesanan->produk as $p) {
                    Produk::where('id', $p->id)->increment('stok', $p->pivot->jumlah);
                }

                $pesanan->produk()->detach();
                $pesanan->delete();
            });

            return response()->json([
                'status'  => true,
                'message' => 'Pesanan berhasil dihapus dan stok produk telah dikembalikan.',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}