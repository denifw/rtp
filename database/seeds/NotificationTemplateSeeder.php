<?php

use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('notification_template')->truncate();

        DB::table('notification_template')->insert(['nt_code' => 'jobpublish', 'nt_description' => 'Publish Job', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer", "jo_service_term"]', 'nt_mail_path' => 'Job/JobPublish', 'nt_uid' => '2adb7664-5f0c-4d07-ac10-d59b86d727cc', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'jobtruckarrive', 'nt_description' => 'Truck Arrive', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer", "jo_service_term"]', 'nt_mail_path' => 'Job/Warehouse/JobTruckArrive', 'nt_uid' => '40eb5c5d-4161-41a8-ae45-2c1daeb9697e', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'inboundstartunload', 'nt_description' => 'Unload Process', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Warehouse/InboundStartUnload', 'nt_uid' => '282cadb1-55dc-4adf-965e-d9277aad2c93', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'inboundcompleteunload', 'nt_description' => 'Unload Completed', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Warehouse/InboundCompleteUnload', 'nt_uid' => '59f515b7-a4db-4edb-a090-015610727708', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'inboundstartputaway', 'nt_description' => 'Put Away Process', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Warehouse/InboundStartPutAway', 'nt_uid' => 'f2eda051-de6f-4386-b75d-6ca9029d9d53', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'inboundcompleteputaway', 'nt_description' => 'Put Away Completed', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Warehouse/InboundCompletePutAway', 'nt_uid' => '2bd00547-eb28-4e72-ab5f-b094b5434cf6', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'jobfinish', 'nt_description' => 'Finish', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer", "jo_service_term"]', 'nt_mail_path' => 'Job/JobFinish', 'nt_uid' => '755c0053-3f72-457f-a4f8-4f9613ad1ad4', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'outboundstartpick', 'nt_description' => 'Picking Process', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Warehouse/OutboundStartPick', 'nt_uid' => '05a0d2b0-463e-43b4-ab68-4716c3b7ea75', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'outboundcompletepick', 'nt_description' => 'Picking Completed', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Warehouse/OutboundCompletePick', 'nt_uid' => '12b1d33d-95ba-4a58-97f8-428827c9d311', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'outboundstartload', 'nt_description' => 'Load Process', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Warehouse/OutboundStartLoad', 'nt_uid' => 'f0ec267e-c81b-4079-8e5d-dfd849d67e23', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'outboundcompleteload', 'nt_description' => 'Load Completed', 'nt_module' => 'warehouse', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Warehouse/OutboundCompleteLoad', 'nt_uid' => '87318cd6-100f-4cda-9235-ab47c81d2417', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'drafting', 'nt_description' => 'Drafting Process', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Inklaring/Drafting', 'nt_uid' => '3eb11261-e5ab-41dc-bd25-08ef046dd95b', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'completedrafting', 'nt_description' => 'Drafting Completed', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Inklaring/CompleteDrafting', 'nt_uid' => '2eb91d27-65dc-4c06-b632-5aa4b4c1071a', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'register', 'nt_description' => 'Register Process', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Inklaring/Register', 'nt_uid' => 'c7d27ea2-f3ea-4d2a-9110-a109a112e763', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'completeregister', 'nt_description' => 'register Completed', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Inklaring/CompleteRegister', 'nt_uid' => '9ce778cd-6192-4fab-98f6-c3860df81435', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'portrelease', 'nt_description' => 'Port Release Process', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Inklaring/PortRelease', 'nt_uid' => 'ed6c49a8-518b-487f-82c8-a1981cb819e9', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'completeportrelease', 'nt_description' => 'Port Release Completed', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Inklaring/CompletePortRelease', 'nt_uid' => '48a00e05-6943-415a-9ac2-98610139f19f', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'goodsrelease', 'nt_description' => 'Goods Release Process', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Inklaring/GoodsRelease', 'nt_uid' => '1587c9ab-6a43-445b-95dc-f7413496b27c', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'completegoodsrelease', 'nt_description' => 'Goods Release Completed', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Inklaring/CompleteGoodsRelease', 'nt_uid' => 'aee54758-8ec8-4b0c-8d5d-32b5d814b261', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'transportarrive', 'nt_description' => 'Transport Arrive', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Inklaring/TransportArrive', 'nt_uid' => 'bda3c4e5-79be-4852-b543-aca8fd1348f8', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'transportdeparture', 'nt_description' => 'Transport Departure', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Inklaring/TransportDeparture', 'nt_uid' => 'b6cf01f8-6caa-41ea-803e-6164416c2932', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'pickup', 'nt_description' => 'Pick Up Delivery', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/PickUp', 'nt_uid' => 'db74cc6c-729d-4dde-9d02-e7ea65171e74', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'arrivedepo', 'nt_description' => 'Arrive Depo', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/ArriveDepo', 'nt_uid' => '2e03c895-1790-44b5-be90-4d27e1f21cf3', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'arrivelud', 'nt_description' => 'Arrive Loading/Unloading', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer", "loc_type"]', 'nt_mail_path' => 'Job/Delivery/ArriveLud', 'nt_uid' => '995ef668-e039-4010-8f25-928a6634fe32', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'loadcontainer', 'nt_description' => 'Load Container Process', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/LoadContainer', 'nt_uid' => '049ef120-3f83-4a02-90d9-b69642ff0b8a', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'completeloadcontainer', 'nt_description' => 'Load Container Complete', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/CompleteLoadContainer', 'nt_uid' => 'f427eae4-9c96-44e6-b9fb-3f8a1f631087', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'delivery', 'nt_description' => 'Start Delivery Goods', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/Delivery', 'nt_uid' => 'd380c98e-a259-448a-a1de-c18b4582ce68', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'loadunload', 'nt_description' => 'Loading/Unloading Process', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer", "loc_type"]', 'nt_mail_path' => 'Job/Delivery/LoadUnload', 'nt_uid' => 'a6fe7b1a-be61-44fd-b8c6-474486fc1802', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'completeloadunload', 'nt_description' => 'Complete Loading/Unloading', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer", "loc_type"]', 'nt_mail_path' => 'Job/Delivery/CompleteLoadUnload', 'nt_uid' => '460f2c3a-ae63-4aab-a824-b816d03423d7', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'completedelivery', 'nt_description' => 'Complete Delivery', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/CompleteDelivery', 'nt_uid' => 'a265ad83-f43a-47d2-bd8a-adabc47c8a53', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'returncontainer', 'nt_description' => 'Return Container Process', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/ReturnContainer', 'nt_uid' => '70974158-2de7-4903-a3a9-6acae13ed5a3', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'arrivedeporeturn', 'nt_description' => 'Arrive Return', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/ArriveDepoReturn', 'nt_uid' => '7b2c3c81-347c-473a-b765-6407c1c5cff1', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'unloadcontainer', 'nt_description' => 'Unload Container Process', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/UnloadContainer', 'nt_uid' => '16bd0401-9927-4e88-bf89-ea4173f16fdf', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'completeunloadcontainer', 'nt_description' => 'Complete Unload Container Process', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/CompleteUnloadContainer', 'nt_uid' => 'ffbff44c-ce49-4d5e-8646-10ad75e5fa70', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'pool', 'nt_description' => 'Back to Pool', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/BackPool', 'nt_uid' => 'fb345b73-7d1d-4161-85dc-9600bc3ef912', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'endpool', 'nt_description' => 'Arrive at Pool', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/ArrivePool', 'nt_uid' => 'b24eee12-b8e1-49eb-9e5a-949c462983e0', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'endpickup', 'nt_description' => 'Complete Pick Up', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer"]', 'nt_mail_path' => 'Job/Delivery/CompletePickup', 'nt_uid' => '6d0399f5-0cd2-4609-b7e9-ada086bfce32', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'jobpublish', 'nt_description' => 'Publish Job', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer", "jo_service_term"]', 'nt_mail_path' => 'Job/JobPublish', 'nt_uid' => '9dacee13-bd0f-41d5-857f-592de76a5a4c', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'jobpublish', 'nt_description' => 'Publish Job', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer", "jo_service_term"]', 'nt_mail_path' => 'Job/JobPublish', 'nt_uid' => '5032fadb-395e-4731-bd23-5a3b22c9966c', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'jobfinish', 'nt_description' => 'Finish', 'nt_module' => 'inklaring', 'nt_message_fields' => '["jo_number", "jo_customer", "jo_service_term"]', 'nt_mail_path' => 'Job/JobFinish', 'nt_uid' => '70dbb5bb-582e-4958-aff3-5b31e883fbcf', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
        DB::table('notification_template')->insert(['nt_code' => 'jobfinish', 'nt_description' => 'Finish', 'nt_module' => 'delivery', 'nt_message_fields' => '["jo_number", "jo_customer", "jo_service_term"]', 'nt_mail_path' => 'Job/JobFinish', 'nt_uid' => 'bfcafb88-8f92-4997-9c51-e8d04e258946', 'nt_created_on' => date('Y-m-d H:i:s'), 'nt_created_by' => 1]);
    }
}
