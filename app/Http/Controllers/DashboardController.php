<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Display the SPV-Track dashboard with KPI metrics and multi-period analytics.
     */
    public function index(Request $request): View
    {
        $currentUser = auth()->user() ?? User::first() ?? (object) [
            'nama_warehouse' => 'Supervisor Staging',
            'role' => 'admin',
            'email' => 'admin@spvtrack.com',
        ];

        // Overall stats
        $totalShipments = Shipment::count();
        $totalSubmitted = Shipment::where('status', 'submitted')->count();
        $totalDraft = Shipment::where('status', 'draft')->count();
        $totalPetugas = Karyawan::where('status', 'aktif')->count();
        $successRate = $totalShipments > 0 ? round(($totalSubmitted / $totalShipments) * 100, 1) : 0;

        // Today stats
        $todayDate = Carbon::today('Asia/Jakarta');
        $todayShipments = Shipment::whereDate('tanggal_staging', $todayDate)
            ->orWhere(function ($q) use ($todayDate) {
                $q->whereNull('tanggal_staging')->whereDate('created_at', $todayDate);
            })->count();

        $todaySubmitted = Shipment::where('status', 'submitted')
            ->where(function ($q) use ($todayDate) {
                $q->whereDate('tanggal_staging', $todayDate)
                    ->orWhere(function ($sq) use ($todayDate) {
                        $sq->whereNull('tanggal_staging')->whereDate('created_at', $todayDate);
                    });
            })->count();

        // Load all shipment records with minimal attributes for memory-efficient date & category processing
        $allShipments = Shipment::select([
            'id', 'status', 'jenis_produk', 'jenis_pengiriman',
            'waktu', 'cuaca', 'warehouse_lokasi', 'tanggal_staging',
            'created_at', 'submitted_at', 'karyawan_id',
        ])->get();

        // 1. Multi-Period KPI Data
        $kpiData = $this->buildMultiPeriodKpiData($allShipments, $todayDate);

        // 2. Status distribution
        $statusDistribution = [
            'submitted' => $totalSubmitted,
            'draft' => $totalDraft,
            'success_rate' => $successRate,
        ];

        // 3. Shipment Type & Product distribution
        $typeDistribution = [
            'export' => $allShipments->where('jenis_pengiriman', 'export')->count(),
            'lokal' => $allShipments->where('jenis_pengiriman', 'lokal')->count(),
            'fiber' => $allShipments->where('jenis_produk', 'fiber')->count(),
            'sodium' => $allShipments->where('jenis_produk', 'sodium')->count(),
        ];

        // 4. Shift & Location operational distribution
        $operationalDistribution = [
            'waktu' => [
                'siang' => $allShipments->where('waktu', 'siang')->count(),
                'sore' => $allShipments->where('waktu', 'sore')->count(),
                'malam' => $allShipments->where('waktu', 'malam')->count(),
            ],
            'lokasi' => [
                'atas' => $allShipments->where('warehouse_lokasi', 'atas')->count(),
                'tengah' => $allShipments->where('warehouse_lokasi', 'tengah')->count(),
                'bawah' => $allShipments->where('warehouse_lokasi', 'bawah')->count(),
            ],
            'cuaca' => [
                'kering' => $allShipments->where('cuaca', 'kering')->count(),
                'mendung' => $allShipments->where('cuaca', 'mendung')->count(),
                'hujan' => $allShipments->where('cuaca', 'hujan')->count(),
                'gerimis' => $allShipments->where('cuaca', 'gerimis')->count(),
            ],
        ];

        // 5. Top Petugas performance
        $topPetugas = Karyawan::where('status', 'aktif')
            ->withCount([
                'shipments as total_shipments',
                'shipments as submitted_shipments' => function ($query) {
                    $query->where('status', 'submitted');
                },
            ])
            ->orderByDesc('submitted_shipments')
            ->orderByDesc('total_shipments')
            ->take(5)
            ->get();

        // 6. Recent Shipments for table
        $recentShipments = Shipment::with(['karyawan', 'karyawans'])
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', [
            'currentUser' => $currentUser,
            'totalShipments' => $totalShipments,
            'totalSubmitted' => $totalSubmitted,
            'totalDraft' => $totalDraft,
            'totalPetugas' => $totalPetugas,
            'successRate' => $successRate,
            'todayShipments' => $todayShipments,
            'todaySubmitted' => $todaySubmitted,
            'kpiData' => $kpiData,
            'statusDistribution' => $statusDistribution,
            'typeDistribution' => $typeDistribution,
            'operationalDistribution' => $operationalDistribution,
            'topPetugas' => $topPetugas,
            'shipments' => $recentShipments,
        ]);
    }

    /**
     * Build multi-period KPI datasets (Harian, Bulanan, Kuartalan, Tahunan).
     *
     * @param  Collection  $shipments
     * @return array<string, array>
     */
    protected function buildMultiPeriodKpiData($shipments, Carbon $now): array
    {
        $currentYear = $now->year;

        // Map shipments with Carbon date for quick access
        $datedShipments = $shipments->map(function ($s) {
            $date = $s->tanggal_staging
                ? Carbon::parse($s->tanggal_staging)
                : ($s->created_at ? Carbon::parse($s->created_at) : null);

            return [
                'id' => $s->id,
                'status' => $s->status,
                'date' => $date,
                'year' => $date?->year,
                'month' => $date?->month,
                'date_str' => $date?->format('Y-m-d'),
            ];
        })->filter(fn ($item) => ! is_null($item['date']));

        // --- 1. HARIAN (Daily - 14 Days) ---
        $dailyLabels = [];
        $dailyTotals = [];
        $dailySubmitted = [];
        $dailyDrafts = [];
        $dailyRates = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $dayStr = $day->format('Y-m-d');
            $dailyLabels[] = $day->translatedFormat('d M');

            $matched = $datedShipments->where('date_str', $dayStr);
            $tot = $matched->count();
            $sub = $matched->where('status', 'submitted')->count();
            $drf = $tot - $sub;
            $rate = $tot > 0 ? round(($sub / $tot) * 100, 1) : 0;

            $dailyTotals[] = $tot;
            $dailySubmitted[] = $sub;
            $dailyDrafts[] = $drf;
            $dailyRates[] = $rate;
        }

        $dailySumTotal = array_sum($dailyTotals);
        $dailySumSubmitted = array_sum($dailySubmitted);
        $dailySumDraft = array_sum($dailyDrafts);
        $dailyAvgRate = $dailySumTotal > 0 ? round(($dailySumSubmitted / $dailySumTotal) * 100, 1) : 0;

        // --- 2. BULANAN (Monthly - 12 Months of Current Year) ---
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyTotals = [];
        $monthlySubmitted = [];
        $monthlyDrafts = [];
        $monthlyRates = [];

        for ($m = 1; $m <= 12; $m++) {
            $matched = $datedShipments->where('year', $currentYear)->where('month', $m);
            $tot = $matched->count();
            $sub = $matched->where('status', 'submitted')->count();
            $drf = $tot - $sub;
            $rate = $tot > 0 ? round(($sub / $tot) * 100, 1) : 0;

            $monthlyTotals[] = $tot;
            $monthlySubmitted[] = $sub;
            $monthlyDrafts[] = $drf;
            $monthlyRates[] = $rate;
        }

        $monthlySumTotal = array_sum($monthlyTotals);
        $monthlySumSubmitted = array_sum($monthlySubmitted);
        $monthlySumDraft = array_sum($monthlyDrafts);
        $monthlyAvgRate = $monthlySumTotal > 0 ? round(($monthlySumSubmitted / $monthlySumTotal) * 100, 1) : 0;

        // --- 3. KUARTALAN (Quarterly - Q1..Q4 of Current Year) ---
        $quarterLabels = ['Q1 (Jan - Mar)', 'Q2 (Apr - Jun)', 'Q3 (Jul - Sep)', 'Q4 (Okt - Des)'];
        $quarterlyTotals = [];
        $quarterlySubmitted = [];
        $quarterlyDrafts = [];
        $quarterlyRates = [];

        for ($q = 1; $q <= 4; $q++) {
            $startM = ($q - 1) * 3 + 1;
            $endM = $q * 3;

            $matched = $datedShipments->where('year', $currentYear)
                ->filter(fn ($item) => $item['month'] >= $startM && $item['month'] <= $endM);

            $tot = $matched->count();
            $sub = $matched->where('status', 'submitted')->count();
            $drf = $tot - $sub;
            $rate = $tot > 0 ? round(($sub / $tot) * 100, 1) : 0;

            $quarterlyTotals[] = $tot;
            $quarterlySubmitted[] = $sub;
            $quarterlyDrafts[] = $drf;
            $quarterlyRates[] = $rate;
        }

        $quarterlySumTotal = array_sum($quarterlyTotals);
        $quarterlySumSubmitted = array_sum($quarterlySubmitted);
        $quarterlySumDraft = array_sum($quarterlyDrafts);
        $quarterlyAvgRate = $quarterlySumTotal > 0 ? round(($quarterlySumSubmitted / $quarterlySumTotal) * 100, 1) : 0;

        // --- 4. TAHUNAN (Yearly - 5 Years) ---
        $yearLabels = [];
        $yearlyTotals = [];
        $yearlySubmitted = [];
        $yearlyDrafts = [];
        $yearlyRates = [];

        $startYear = $currentYear - 4;
        for ($y = $startYear; $y <= $currentYear; $y++) {
            $yearLabels[] = (string) $y;
            $matched = $datedShipments->where('year', $y);
            $tot = $matched->count();
            $sub = $matched->where('status', 'submitted')->count();
            $drf = $tot - $sub;
            $rate = $tot > 0 ? round(($sub / $tot) * 100, 1) : 0;

            $yearlyTotals[] = $tot;
            $yearlySubmitted[] = $sub;
            $yearlyDrafts[] = $drf;
            $yearlyRates[] = $rate;
        }

        $yearlySumTotal = array_sum($yearlyTotals);
        $yearlySumSubmitted = array_sum($yearlySubmitted);
        $yearlySumDraft = array_sum($yearlyDrafts);
        $yearlyAvgRate = $yearlySumTotal > 0 ? round(($yearlySumSubmitted / $yearlySumTotal) * 100, 1) : 0;

        return [
            'harian' => [
                'title' => 'KPI Harian (14 Hari Terakhir)',
                'badge' => '14 Hari Terakhir',
                'labels' => $dailyLabels,
                'totals' => $dailyTotals,
                'submitted' => $dailySubmitted,
                'drafts' => $dailyDrafts,
                'rates' => $dailyRates,
                'summary' => [
                    'total' => $dailySumTotal,
                    'submitted' => $dailySumSubmitted,
                    'draft' => $dailySumDraft,
                    'rate' => $dailyAvgRate,
                ],
            ],
            'bulanan' => [
                'title' => 'KPI Bulanan (Tahun '.$currentYear.')',
                'badge' => '12 Bulan Tahun '.$currentYear,
                'labels' => $monthNames,
                'totals' => $monthlyTotals,
                'submitted' => $monthlySubmitted,
                'drafts' => $monthlyDrafts,
                'rates' => $monthlyRates,
                'summary' => [
                    'total' => $monthlySumTotal,
                    'submitted' => $monthlySumSubmitted,
                    'draft' => $monthlySumDraft,
                    'rate' => $monthlyAvgRate,
                ],
            ],
            'kuartalan' => [
                'title' => 'KPI Kuartalan (Tahun '.$currentYear.')',
                'badge' => 'Q1 - Q4 Tahun '.$currentYear,
                'labels' => $quarterLabels,
                'totals' => $quarterlyTotals,
                'submitted' => $quarterlySubmitted,
                'drafts' => $quarterlyDrafts,
                'rates' => $quarterlyRates,
                'summary' => [
                    'total' => $quarterlySumTotal,
                    'submitted' => $quarterlySumSubmitted,
                    'draft' => $quarterlySumDraft,
                    'rate' => $quarterlyAvgRate,
                ],
            ],
            'tahunan' => [
                'title' => 'KPI Tahunan (5 Tahun Terakhir)',
                'badge' => $startYear.' - '.$currentYear,
                'labels' => $yearLabels,
                'totals' => $yearlyTotals,
                'submitted' => $yearlySubmitted,
                'drafts' => $yearlyDrafts,
                'rates' => $yearlyRates,
                'summary' => [
                    'total' => $yearlySumTotal,
                    'submitted' => $yearlySumSubmitted,
                    'draft' => $yearlySumDraft,
                    'rate' => $yearlyAvgRate,
                ],
            ],
        ];
    }
}
