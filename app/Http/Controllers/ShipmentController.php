<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::with(['karyawan', 'evidenceItems'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('packing_list_no', 'like', "%{$search}%")
                    ->orWhere('nomor_container_atau_plat', 'like', "%{$search}%")
                    ->orWhere('plat_nomor', 'like', "%{$search}%")
                    ->orWhere('shipment_no', 'like', "%{$search}%")
                    ->orWhere('shipment_group', 'like', "%{$search}%")
                    ->orWhere('nama_sopir', 'like', "%{$search}%")
                    ->orWhere('agen_forwarding', 'like', "%{$search}%")
                    ->orWhere('tujuan_pengiriman', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            if ($type !== 'semua') {
                $query->where('jenis_pengiriman', $type);
            }
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $shipments = $query->paginate(10)->withQueryString();

        return view('shipments.index', compact('shipments'));
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['karyawan', 'user', 'evidenceItems.sopPhotoPoint', 'photos']);
        $points = SopPhotoPoint::orderBy('urutan')->get();

        return view('shipments.show', compact('shipment', 'points'));
    }
}
