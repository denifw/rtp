<?php

use Illuminate\Database\Seeder;

class DashboardForNewUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $query = 'SELECT  us.us_id, us.us_name, ump.ump_ss_id, srv.srv_id, srv.srv_name
                  FROM    users AS us INNER JOIN
                          user_mapping AS ump ON ump.ump_us_id = us.us_id INNER JOIN
                          system_service AS ssr ON ssr.ssr_ss_id = ump.ump_ss_id INNER JOIN
                          service AS srv ON srv.srv_id = ssr.ssr_srv_id 
                  WHERE   (us.us_active = \'Y\') 
                          AND (ump.ump_ss_id NOT IN (SELECT dsh_ss_id
                                                     FROM dashboard) 
                          OR us.us_id NOT IN (SELECT dsh_us_id
											  FROM dashboard))
				  GROUP BY us.us_id, us.us_name, ump.ump_ss_id, srv.srv_id, srv.srv_name';
        $resultUsers = \Illuminate\Support\Facades\DB::select($query);
        $orderDsh = 1;
        # Inser Dashboard
        foreach ($resultUsers AS $user) {
            $dshId = \Illuminate\Support\Facades\DB::table('dashboard')->insertGetId(
                [
                    'dsh_ss_id' => $user->ump_ss_id,
                    'dsh_us_id' => $user->us_id,
                    'dsh_name' => $user->srv_name,
                    'dsh_description' => $user->srv_name,
                    'dsh_order' => $orderDsh,
                    'dsh_created_by' => 1,
                    'dsh_created_on' => date('Y-m-d H:i:s'),
                ], 'dsh_id'
            );
            $orderDsh++;
            # Insert Dashboard Detail
            $dsiQuery = 'SELECT dsi.dsi_id, dsi.dsi_title, dsi.dsi_code, dsi.dsi_route,
                                dsi.dsi_description, dsi.dsi_grid_large, dsi.dsi_grid_medium,
                                dsi.dsi_grid_small, dsi.dsi_grid_xsmall, dsi.dsi_height,
                                dsi.dsi_color, dsi.dsi_order
                          FROM dashboard_item AS dsi
                          WHERE dsi.dsi_deleted_on IS NULL AND dsi.dsi_code <> \'autoReloadPlanningJob\'
                                 AND dsi.dsi_code <> \'autoReloadProgressJob\' AND dsi.dsi_code <> \'warehouseArriveSoon\'
                          ORDER BY dsi.dsi_order';
            $dsiData = \Illuminate\Support\Facades\DB::select($dsiQuery);
            $dsiGeneral = ['totalPlanningJob', 'totalPublishedJob', 'totalInProgressJob', 'totalCompleteJob', 'planningJobTable', 'inProgressJobTable'];
            foreach ($dsiData AS $dsi) {
                if (in_array($dsi->dsi_code, $dsiGeneral, true) === true) {
                    $parameters = [
                        'jo_srv_id' => $user->srv_id,
                        'jo_srv_name' => $user->srv_name,
                    ];
                    \Illuminate\Support\Facades\DB::table('dashboard_detail')->insert(
                        [
                            'dsd_dsh_id' => $dshId,
                            'dsd_dsi_id' => $dsi->dsi_id,
                            'dsd_title' => $dsi->dsi_title,
                            'dsd_grid_large' => $dsi->dsi_grid_large,
                            'dsd_grid_medium' => $dsi->dsi_grid_medium,
                            'dsd_grid_small' => $dsi->dsi_grid_small,
                            'dsd_grid_xsmall' => $dsi->dsi_grid_xsmall,
                            'dsd_height' => $dsi->dsi_height,
                            'dsd_color' => $dsi->dsi_color,
                            'dsd_order' => $dsi->dsi_order,
                            'dsd_parameter' => json_encode($parameters),
                            'dsd_created_by' => 1,
                            'dsd_created_on' => date('Y-m-d H:i:s'),
                        ]
                    );
                } else {
                    $dsiWh = ['totalInboundItem', 'totalOutboundItem', 'totalGoodItem', 'totalDamageItem'];
                    $dsiInk = ['totalImport', 'totalImportContainer', 'totalExport', 'totalExportContainer'];
                    $dsiTrk = ['inProgressJobTrucking', 'planningJobTrucking'];
                    # Warehouse & Trucking
                    if (($user->srv_name === 'Warehouse' && in_array($dsi->dsi_code, $dsiWh, true) === true) ||
                        ($user->srv_name === 'Trucking' && in_array($dsi->dsi_code, $dsiTrk, true) === true)) {
                        $parameters = [
                            'jo_srv_id' => $user->srv_id,
                            'jo_srv_name' => $user->srv_name,
                        ];
                        \Illuminate\Support\Facades\DB::table('dashboard_detail')->insert(
                            [
                                'dsd_dsh_id' => $dshId,
                                'dsd_dsi_id' => $dsi->dsi_id,
                                'dsd_title' => $dsi->dsi_title,
                                'dsd_grid_large' => $dsi->dsi_grid_large,
                                'dsd_grid_medium' => $dsi->dsi_grid_medium,
                                'dsd_grid_small' => $dsi->dsi_grid_small,
                                'dsd_grid_xsmall' => $dsi->dsi_grid_xsmall,
                                'dsd_height' => $dsi->dsi_height,
                                'dsd_color' => $dsi->dsi_color,
                                'dsd_order' => $dsi->dsi_order,
                                'dsd_parameter' => json_encode($parameters),
                                'dsd_created_by' => 1,
                                'dsd_created_on' => date('Y-m-d H:i:s'),
                            ]
                        );
                    }
                    # Inklaring
                    if ($user->srv_name === 'Inklaring' && in_array($dsi->dsi_code, $dsiInk, true) === true) {
                        $parameters = [
                            'jo_srv_id' => $user->srv_id,
                            'jo_srv_name' => $user->srv_name,
                        ];
                        if ($dsi->dsi_code === 'totalImport') {
                            $parameters['jo_srt_id'] = 8;
                            $parameters['jo_srt_name'] = 'Import';
                        } elseif ($dsi->dsi_code === 'totalExport') {
                            $parameters['jo_srt_id'] = 9;
                            $parameters['jo_srt_name'] = 'Export';
                        } elseif ($dsi->dsi_code === 'totalImportContainer') {
                            $parameters['jo_srt_id'] = 6;
                            $parameters['jo_srt_name'] = 'Import Container';
                        } elseif ($dsi->dsi_code === 'totalExportContainer') {
                            $parameters['jo_srt_id'] = 7;
                            $parameters['jo_srt_name'] = 'Export Container';
                        }
                        \Illuminate\Support\Facades\DB::table('dashboard_detail')->insert(
                            [
                                'dsd_dsh_id' => $dshId,
                                'dsd_dsi_id' => $dsi->dsi_id,
                                'dsd_title' => $dsi->dsi_title,
                                'dsd_grid_large' => $dsi->dsi_grid_large,
                                'dsd_grid_medium' => $dsi->dsi_grid_medium,
                                'dsd_grid_small' => $dsi->dsi_grid_small,
                                'dsd_grid_xsmall' => $dsi->dsi_grid_xsmall,
                                'dsd_height' => $dsi->dsi_height,
                                'dsd_color' => $dsi->dsi_color,
                                'dsd_order' => $dsi->dsi_order,
                                'dsd_parameter' => json_encode($parameters),
                                'dsd_created_by' => 1,
                                'dsd_created_on' => date('Y-m-d H:i:s'),
                            ]
                        );
                    }
                }
            }
        }
    }
}
