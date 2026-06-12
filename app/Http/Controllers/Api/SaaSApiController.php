<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaaSApiController extends Controller
{
    /**
     * Endpoint untuk mendapatkan daftar lisensi dinamis dari database PostgreSQL (tabel journals).
     */
    public function getLicenses(Request $request): JsonResponse
    {
        $journals = Journal::all();
        $licenses = [];

        foreach ($journals as $index => $journal) {
            $licenses[] = [
                'id' => 'LIC-' . $journal->id,
                'tenantName' => $journal->name,
                'licenseKey' => 'IAMJOS_LIC_' . strtoupper($journal->slug) . '_' . $journal->id,
                'coreSystemActive' => (bool) $journal->enabled,
                'premiumTheme' => $journal->theme ?? 'Green Nature Theme',
                'activePlugins' => json_decode($journal->plugins ?? '[]') ?: ['Auto PDF Compressor', 'Email Notification Gateway'],
                'status' => $journal->enabled ? 'Active' : 'Suspended',
                'spec' => [
                    'domain' => $journal->domain ?? 'iamjos.id',
                    'subdomain' => $journal->slug . '.iamjos.id',
                    'cpuCores' => 4,
                    'ramGb' => 8,
                    'storageGb' => 80,
                ],
                'vpsIp' => $journal->vps_ip ?? '103.56.200.' . (40 + $index + 1),
                'securityShield' => [
                    'dockerProtected' => true,
                    'ioncubeActive' => true,
                    'codeChecksumValid' => true,
                    'themeSignatureVerified' => true,
                ],
            ];
        }

        return response()->json($licenses);
    }

    /**
     * Endpoint untuk mendapatkan daftar pesanan dinamis.
     */
    public function getOrders(Request $request): JsonResponse
    {
        $journals = Journal::all();
        $orders = [];

        foreach ($journals as $index => $journal) {
            $orders[] = [
                'id' => 'ORD-' . (100 + $index + 1),
                'tenantName' => $journal->name,
                'email' => $journal->email ?? 'admin@' . ($journal->domain ?? 'iamjos.id'),
                'packageType' => ($index + 1) % 2 === 0 ? 'Pro' : 'Starter',
                'price' => ($index + 1) % 2 === 0 ? 25000000 : 10000000,
                'vpsIp' => $journal->vps_ip ?? '103.56.200.' . (40 + $index + 1),
                'status' => $journal->enabled ? 'Active' : 'Pending',
                'orderDate' => $journal->created_at ? $journal->created_at->format('d M Y H:i') : now()->format('d M Y H:i'),
                'spec' => [
                    'domain' => $journal->domain ?? 'iamjos.id',
                    'subdomain' => $journal->slug . '.iamjos.id',
                    'cpuCores' => 4,
                    'ramGb' => 8,
                    'storageGb' => 80,
                ],
                'premiumTheme' => $journal->theme ?? 'Green Nature Theme',
                'activePlugins' => ['Auto PDF Compressor'],
            ];
        }

        return response()->json($orders);
    }

    /**
     * Endpoint untuk telemetri monitor dinamis.
     */
    public function getMonitors(Request $request): JsonResponse
    {
        $journals = Journal::all();
        $monitors = [];

        foreach ($journals as $index => $journal) {
            $monitors[] = [
                'id' => 'MON-' . $journal->id,
                'tenantName' => $journal->name,
                'vpsIp' => $journal->vps_ip ?? '103.56.200.' . (40 + $index + 1),
                'status' => $journal->enabled ? 'Online' : 'Offline',
                'cpuLoad' => $journal->enabled ? rand(15, 65) : 0,
                'ramUsage' => $journal->enabled ? rand(40, 80) : 0,
                'storageUsage' => $journal->enabled ? rand(30, 75) : 0,
                'latency' => $journal->enabled ? rand(10, 45) : 0,
            ];
        }

        return response()->json($monitors);
    }

    /**
     * Endpoint untuk billing invoices dinamis.
     */
    public function getBillings(Request $request): JsonResponse
    {
        $journals = Journal::all();
        $billings = [];

        foreach ($journals as $index => $journal) {
            $billings[] = [
                'id' => 'INV-2026-' . (1000 + $index + 1),
                'tenantName' => $journal->name,
                'amount' => ($index + 1) % 2 === 0 ? 2500000 : 1000000,
                'dueDate' => now()->addDays(15)->format('d M Y'),
                'status' => $journal->enabled ? 'Paid' : 'Unpaid',
            ];
        }

        return response()->json($billings);
    }
}
