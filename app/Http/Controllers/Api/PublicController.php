<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    // Daftar semua produk, dengan pagination
    public function produk()
    {
        try {
            $produk = DB::table('produks')
                ->join('kategoris', 'produks.id_kategori', '=', 'kategoris.id')
                ->select(
                    'produks.id', 'produks.nama_barang', 'produks.harga_barang',
                    'produks.stok', 'produks.deskripsi', 'kategoris.nama_kategori'
                )
                ->paginate(10);

            return response()->json([
                'status'  => true,
                'message' => 'Data produk berhasil diambil.',
                'data'    => $produk,
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Detail satu produk
    public function detailProduk($id)
    {
        try {
            $produk = DB::table('produks')
                ->join('kategoris', 'produks.id_kategori', '=', 'kategoris.id')
                ->select('produks.*', 'kategoris.nama_kategori')
                ->where('produks.id', $id)
                ->first();

            if (! $produk) {
                return response()->json(['status' => false, 'message' => 'Produk tidak ditemukan.'], 404);
            }

            return response()->json(['status' => true, 'data' => $produk]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Semua kategori
    public function kategori()
    {
        try {
            $kategori = DB::table('kategoris')->get();
            return response()->json(['status' => true, 'data' => $kategori]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Produk berdasarkan kategori tertentu
    public function produkByKategori($id)
    {
        try {
            $produk = DB::table('produks')
                ->join('kategoris', 'produks.id_kategori', '=', 'kategoris.id')
                ->where('kategoris.id', $id)
                ->select('produks.*', 'kategoris.nama_kategori')
                ->paginate(10);

            return response()->json(['status' => true, 'data' => $produk]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Pencarian produk berdasarkan nama
    public function search(Request $request)
    {
        try {
            $produk = DB::table('produks')
                ->join('kategoris', 'produks.id_kategori', '=', 'kategoris.id')
                ->where('produks.nama_barang', 'like', '%' . $request->keyword . '%')
                ->select('produks.*', 'kategoris.nama_kategori')
                ->paginate(10);

            return response()->json(['status' => true, 'data' => $produk]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
