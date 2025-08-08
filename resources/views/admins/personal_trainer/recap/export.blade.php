<html>
    @if ($type == "internal")
        <table class="table table-bordered" border="1">
            <thead>
                <tr>
                    <th width=30 valign="middle" align="center"><b>Nama Coach</b></th>
                    <th width=15 valign="middle" align="center"><b>Level</b></th>
                    @foreach ($session_bundling['session'] as $session)
                        <th width=30 valign="middle" align="center">Session<br><b>{{ $session->name }}</b></th>
                    @endforeach
                    @foreach ($session_bundling['bundling'] as $bundling)
                        <th width=30 valign="middle" align="center">Bundling<br><b>{{ $bundling->name }}</b></th>
                    @endforeach
                    <th width="40" class="text-center" valign="middle" align="center"><b>Kelas</b></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pt_internal as $internal)
                    @php
                        // gym class
                        // get gym class cancel
                        $gym_class_cancel = \App\Models\GymClassCancelHistory::pluck('gym_class_id')->toArray();
                        $classes_id = \App\Models\GymClassHistory::when($fromDate, function ($q_class) use ( $internal, $fromDate, $toDate) {
                                $q_class->whereRelation('gym_class', 'personal_trainer_id', $internal->id);
                            })->where('date', '>=', $fromDate)
                            ->where('date', '<=', $toDate)
                            ->whereNot('status', "CANCELED")
                            ->when(count($gym_class_cancel) > 0, fn ($q) => $q->whereNotIn('gym_class_id', $gym_class_cancel));
                        $totalClasses = \App\Models\GymClass::whereIn('id', $classes_id->pluck('gym_class_id')->toArray())->withTrashed()->count();
                        $timeClass = $totalClasses . " Kelas";

                        // get data nama kelas dan list usernya
                        $classes_count = array_count_values($classes_id->pluck('gym_class_id')->toArray());
                        $classes_history = \App\Models\GymClassHistory::with('gym_class')
                            ->when($internal, function ($q_class) use ($fromDate, $toDate, $internal) {
                                $q_class->whereRelation('gym_class', 'personal_trainer_id', $internal->id);
                            })->where('date', '>=', $fromDate)
                            ->where('date', '<=', $toDate)
                            ->whereNot('status', "CANCELED")
                            ->whereIn('gym_class_id', array_keys($classes_count))
                            ->get();
                        $group_class = [];
                        $user_class = [];
                        $last_class_name = "";
                        foreach ($classes_history as $classes) {
                            $date_formatter = date('d/m/Y', strtotime($classes->date));
                            $class_name = $classes->gym_class?->name . " ({$date_formatter})";
                            $classes['total_session'] = $classes_count[$classes->gym_class_id];
                            $user_class[$class_name][] = $classes;
                            $class_total_session = $last_class_name == $class_name ? $group_class[$class_name]["session"] + $classes_count[$classes->gym_class_id] : $classes_count[$classes->gym_class_id];
                            $group_class[$class_name] = [
                                "session" => $class_total_session,
                                "class" => $user_class
                            ];
                            $last_class_name = $class_name;
                        }
                        ksort($group_class);
                        // ini data yang akan kelas yang akan yang akan ditampilan
                        $class_sesi = 0;
                        $data_classes = "";
                        foreach ($group_class as $key_class_name => $value_class) {
                            $grouped_classes = collect($value_class['class'][$key_class_name])->groupBy('user_id')->map(function ($user_classes) {
                                $check_in_total = $user_classes->sum(function ($user_class) {
                                    return $user_class->check_in ? 1 : 0;
                                });
                                $not_check_in_total = $user_classes->count() - $check_in_total;
                                return [
                                    'user_id' => $user_classes[0]->user_id,
                                    'username' => $user_classes[0]->user->name,
                                    'check_in' => $check_in_total,
                                    'not_check_in' => $not_check_in_total,
                                ];
                            })->values();
                            $packet_class_count = 0;
                            $user_class_detail = [];
                            foreach ($grouped_classes as $class_result) {
                                $status_checkIn = $class_result['check_in'] > 0 ? "CheckIn" : "Not CheckIn";
                                $user_class_detail[] = "<b>- " . $class_result['username'] . '</b> (' . $status_checkIn . ')';
                                $packet_class_count += 1;
                            }

                            $class_sesi += $packet_class_count;
                            $data_classes .= "<br><li>{$key_class_name} (" . $packet_class_count . " Peserta)</li>
                                <ul>" . implode('<br>', $user_class_detail) . "</ul>";
                        }
                    @endphp
                    <tr>
                        <td valign="middle"><b>{{ $internal->name }}</b></td>
                        <td valign="middle" align="center">{{ $internal->personal_trainer_level?->name }}</td>
                        @foreach ($session_bundling['session'] as $session)
                            @php
                                $package_history = \App\Models\PersonalTrainerPacketSessionHistory::with('gym_class_bundling')
                                    // ->wherePersonalTrainerId($internal->id)
                                    ->whereHas('personal_trainer_schedule_members', function ($q) use ($fromDate, $toDate, $internal) {
                                        $q->where('date', '>=', $fromDate)
                                            ->where('date', '<=', $toDate)
                                            ->wherePersonalTrainerId($internal->id)
                                            ->whereNull('canceled_at');
                                    })
                                    ->wherePersonalTrainerPacketSessionId($session->id)
                                    ->orderBy('personal_trainer_packet_session_id')
                                    ->get();
                                $data_session = [];
                                foreach ($package_history as $session_value_user) {
                                    // data user dan total sesi nya 
                                    $get_session_by_user = \App\Models\PersonalTrainerScheduleMember::where('personal_trainer_packet_session_history_id', $session_value_user->id)->whereUserId($session_value_user->user_id)->whereNull('canceled_at')->where('date', '>=', $fromDate)->where('date', '<=', $toDate)->get();
                                    $session_used = $get_session_by_user->where('is_used',true)->count();
                                    $session_not_used = $get_session_by_user->where('is_used',false)->count();
                                    $data_session[$session->name][] = "- " . $session_value_user->user->name . ' (<b>' . $session_used . ' Sesi CheckIn, '. $session_not_used . ' Sesi Not CheckIn</b>)';

                                }
                            @endphp 
                            <td valign="middle"><?= @$data_session[$session->name] ? implode('<br>', $data_session[$session->name]) : "-"; ?></td>
                        @endforeach
                        @foreach ($session_bundling['bundling'] as $bundling)
                            @php
                                $bundling_history = \App\Models\PersonalTrainerPacketSessionHistory::with('gym_class_bundling')
                                    // ->wherePersonalTrainerId($internal->id)
                                    ->whereHas('personal_trainer_schedule_members', function ($q) use ($fromDate, $toDate, $internal) {
                                        $q->where('date', '>=', $fromDate)
                                            ->where('date', '<=', $toDate)
                                            ->wherePersonalTrainerId($internal->id)
                                            ->whereNull('canceled_at');
                                    })
                                    ->whereGymClassBundlingId($bundling->id)
                                    ->orderBy('gym_class_bundling_id')
                                    ->get();
                                $data_bundling = [];
                                foreach ($bundling_history as $bundling_value_user) {
                                    // data user dan total sesi nya 
                                    $get_bundling_by_user = \App\Models\PersonalTrainerScheduleMember::where('personal_trainer_packet_session_history_id', $bundling_value_user->id)->whereUserId($bundling_value_user->user_id)->whereNull('canceled_at')->where('date', '>=', $fromDate)->where('date', '<=', $toDate)->get();
                                    $bundling_used = $get_bundling_by_user->where('is_used',true)->count();
                                    $bundling_not_used = $get_bundling_by_user->where('is_used',false)->count();
                                    $data_bundling[$bundling->name][] = "- " . $bundling_value_user->user->name . ' (<b>' . $bundling_used . ' Sesi CheckIn, '. $bundling_not_used . ' Sesi Not CheckIn</b>)';

                                }
                            @endphp
                            <td valign="middle"><?= @$data_bundling[$bundling->name] ? implode('<br>', $data_bundling[$bundling->name]) : "-"; ?></td>
                        @endforeach
                        <td valign="middle"><?= $timeClass; ?><br><?= $data_classes ? $data_classes : "-" ?></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table border=1>
            <tr>
                <th width="4" class="text-center" valign="middle" align="center">No. </th>
                <th width="30" class="text-center" valign="middle" align="center">Nama Coach</th>
                <th width="30" class="text-center" valign="middle" align="center">Total Sesi</th>
                <th width="40" class="text-center" valign="middle" align="center">Total Kelas</th>
            </tr>
            @foreach ($pt_external as $external)
            @php
                // get gym class cancel
                $gym_class_cancel = \App\Models\GymClassCancelHistory::pluck('gym_class_id')->toArray();
                $classes_id = \App\Models\GymClassHistory::when($fromDate, function ($q_class) use ($external, $fromDate, $toDate) {
                        $q_class->whereRelation('gym_class', 'personal_trainer_external_id', $external->id);
                    })->where('date', '>=', $fromDate)
                    ->where('date', '<=', $toDate)
                    ->whereNot('status', "CANCELED")
                    ->when(count($gym_class_cancel) > 0, fn ($q) => $q->whereNotIn('gym_class_id', $gym_class_cancel));
                $totalClasses = \App\Models\GymClass::whereIn('id', $classes_id->pluck('gym_class_id')->toArray())->withTrashed()->count();
                $timeClass = $totalClasses . " Kelas";

                // get data nama kelas dan list usernya
                $classes_count = array_count_values($classes_id->pluck('gym_class_id')->toArray());
                $classes_history = \App\Models\GymClassHistory::with('gym_class')
                    ->when($fromDate, function ($q_class) use ($external, $fromDate, $toDate) {
                        $q_class->whereRelation('gym_class', 'personal_trainer_external_id', $external->id);
                    })->where('date', '>=', $fromDate)
                    ->where('date', '<=', $toDate)
                    ->whereNot('status', "CANCELED")
                    ->whereIn('gym_class_id', array_keys($classes_count))
                    ->get();
                $group_class = [];
                $user_class = [];
                $last_class_name = "";
                foreach ($classes_history as $classes) {
                    $date_formatter = date('d/m/Y', strtotime($classes->date));
                    $class_name = $classes->gym_class?->name . " ({$date_formatter})";
                    $classes['total_session'] = $classes_count[$classes->gym_class_id];
                    $user_class[$class_name][] = $classes;
                    $class_total_session = $last_class_name == $class_name ? $group_class[$class_name]["session"] + $classes_count[$classes->gym_class_id] : $classes_count[$classes->gym_class_id];
                    $group_class[$class_name] = [
                        "session" => $class_total_session,
                        "class" => $user_class
                    ];
                    $last_class_name = $class_name;
                }
                ksort($group_class);
                // ini data yang akan kelas yang akan yang akan ditampilan
                $class_sesi = 0;
                $data_classes = "";
                foreach ($group_class as $key_class_name => $value_class) {
                    $grouped_classes = collect($value_class['class'][$key_class_name])->groupBy('user_id')->map(function ($user_classes) {
                        $check_in_total = $user_classes->sum(function ($user_class) {
                            return $user_class->check_in ? 1 : 0;
                        });
                        $not_check_in_total = $user_classes->count() - $check_in_total;
                        return [
                            'user_id' => $user_classes[0]->user_id,
                            'username' => $user_classes[0]->user->name,
                            'check_in' => $check_in_total,
                            'not_check_in' => $not_check_in_total,
                        ];
                    })->values();
                    $packet_class_count = 0;
                    $user_class_detail = [];
                    foreach ($grouped_classes as $class_result) {
                        $status_checkIn = $class_result['check_in'] > 0 ? "CheckIn" : "Not CheckIn";
                        $packet_class_count += 1;
                        $user_class_detail[] = "<b>- " . $class_result['username'] . '</b> (' . $status_checkIn . ')';
                    }

                    $class_sesi += $packet_class_count;
                    $data_classes .= "<br><li>{$key_class_name} (" . $packet_class_count . " Peserta)</li>
                        <ul>" . implode('<br>', $user_class_detail) . "</ul>";
                }
            @endphp
                <tr>
                    <td valign="middle">{{ $loop->iteration }}</td>
                    <td valign="middle">{{ $external->name }}</td>
                    <td valign="middle">-</td>
                    <td style="vertical-align: top; width:300px"><?= $timeClass; ?><br><?= $data_classes ? $data_classes : "-" ?></td>
                </tr>
            @endforeach
        </table>
    @endif

</html>