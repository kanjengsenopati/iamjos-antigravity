<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Services\PhriEventService;
use Carbon\Carbon;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Event::orderBy('start_date', 'desc');
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    $actionDelete = route('event.destroy', $data->id);
                    $actionToggleStatus = route('event.toggle-status', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.toggle-status', [
                            'action' => $actionToggleStatus,
                            'id' => $data->id,
                            'isActive' => $data->is_active
                        ]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->addColumn('status', function ($data) {
                    return $data->is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
                })
                ->addColumn('event_date', function ($data) {
                    $startDate = $data->start_date ? Carbon::parse($data->start_date)->format('d M Y') : '';
                    $endDate = $data->end_date ? Carbon::parse($data->end_date)->format('d M Y') : '';

                    if ($startDate && $endDate) {
                        return $startDate === $endDate ? $startDate : $startDate . ' - ' . $endDate;
                    }
                    return $startDate ?: $endDate ?: 'Not Set';
                })
                ->addColumn('approval_status', function ($data) {
                    return $data->is_approved ? '<span class="badge badge-success">Approved</span>' : '<span class="badge badge-warning">Pending</span>';
                })
                ->rawColumns(['action', 'status', 'event_date', 'approval_status'])
                ->make(true);
        }
        return view('admins.event.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('event.index')->with('success', 'Event Berhasil Dihapus.');
    }

    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleStatus(Event $event)
    {
        $event->update(['is_active' => !$event->is_active]);

        $status = $event->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Event telah {$status}.",
                'status' => $event->is_active
            ]);
        }

        return redirect()->route('event.index')->with('success', "Status event telah diubah menjadi {$status}.");
    }

    /**
     * Sync events from PHRI API
     */
    public function sync(PhriEventService $service)
    {
        try {
            $result = $service->sync();

            if (!empty($result['not_modified'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada perubahan data event (304 Not Modified).'
                ]);
            }

            $message = sprintf(
                'Sinkronisasi berhasil. Inserted: %d, Updated: %d',
                $result['inserted'] ?? 0,
                $result['updated'] ?? 0
            );

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $result
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan sinkronisasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
