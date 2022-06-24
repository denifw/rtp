<?php

return [
    'expired_access' => 'Access login anda sudah expired, silahkan melakukan login kembali.',
    'invalidUsernamePassword' => 'Nama penguna dan kata sandi tidak valid.',
    'invalidCheckFound' => 'Ditemukan data yang tidak valid.',
    'exportXlsNotConfigure' => "Fasilias mengunduh excel belum di atur.",
    'successUpdate' => 'Pembaharuan data berhasil diselesaikan.',
    'successInsert' => 'Penambahan data berhasil diselesaikan.',
    'failedUpdate' => 'Perubahan data gagal. Silahkan check data yang tidak valid.',
    'failedInsert' => 'Penambahan data gagal. Silahkan check data yang tidak valid.',
    'doNotHavePermission' => 'Anda tidak memiliki izin untuk mengakses halaman ini.',
    'pageNotFound' => 'Halaman tidak ditemukan.',
    'canNotFoundPage' => 'Kami tidak menemukan halaman yang anda cari.',
    'noMenuFound' => 'Tidak ada data menu yang ditemukan.',
    'noDataFound' => 'Tidak ada data yang ditemukan.',
    'noActivePageFound' => 'Tidak ada halaman aktif yang ditemukan.',
    'pagingInfo' => 'Menampilkan :dataPerRow dari :totalData data.',
//    # Done
    'invalidDetailData' => 'Data untuk halaman detail tidak ditemukan.',
    'invalidTokenForEmailConfirmation' => 'Token untuk konfirmasil akun tidak valid.',
    'successEmailConfirmation' => 'Selamat, Akun anda sudah terverifikasi.',
    'successAddUserAndWaitingConfirmation' => 'Pengguna berhasil ditambahkan dan sedang menunggu konfirmasi melalui e-mail.',
    'successAddUserAndFailSendingEmail' => 'Pengguna berhasil ditambahkan, namun sistem gagal untuk mengirimkan link konfirmasi melalui e-mail. <br>Mohon dipastikan alamat email yang digunakan masih aktif.',
    'userWithoutEmailConfirmation' => "Pengguna ini belum melakukan konfirmasi akin. <br>Tekan tombol Kirim Pengingat jika anda ingin mengirimkan ulang link konfirmasi kepada pengguna.",
    'failed' => 'Akun anda tidak cocok dengan data sistem.',
    'notMatchCredential' => 'Akun anda tidak cocok dengan data sistem.',
    'destroy_access' => 'Akun anda telah digunakan pada perangkat lain.',
    'throttle' => 'Terlalu banyak percobaan login. Silahkan coba lagi dalam :seconds detik.',
    'invalidCredential' => 'Akun anda tidak cocok dengan data sistem.',
    'successEmailReset' => 'Link untuk mereset kata sandi anda telah dikirimkan melalui e-mail ke :email .',
    'unableToSentEmail' => 'Gagal mengirimkan email ke :email .',
    'successResetPassword' => 'Kata sandi anda telah di reset.',
    'paidConfirmation' => 'Apakah anda yakin melakukan pembayaran ini?',
    'verifiedConfirmation' => 'Apakah anda yakin melakukan verifikasi data ini?',
    'unablePaidInvoice' => 'Detail data tidak ditemukan. Silahkan menginputkan detail data terlebih dahulu sebelum melakukan pembayaran.',
    'invoiceSubmitConfirmation' => 'Apakah anda yakin mengkonfirmasi pengiriman faktur ini?',
    'unableSubmitInvoice' => 'Detail data tidak ditemukan. Silahkan menginputkan detail data terlebih dahulu sebelum melakukan pengiriman faktur.',
    'deleteConfirmation' => 'Apakah anda yakin menghapus data ini?',
    'freezeAccountConfirmation' => 'Apakah anda yakin membekukan akun ini?',
    'unFreezeAccountConfirmation' => 'Apakah anda yakin membuka pembekuan akun ini?',
    'frozenAccount' => 'Akun ini telah dibekukan oleh <strong>:user</strong>, pada tanggal <strong>:time</strong>,<br />karena : <strong>:reason</strong>',
//    'pleaseConfirmToCargoEvent' => 'Please confirm to start adding cargo event.',
//    'deleteEventConfirmation' => 'Are you sure to delete this event data?',
//    'deleteCargoConfirmation' => 'Are you sure to delete this cargo data?',
//    'deleteBoardingOfficerConfirmation' => 'Are you sure to delete this boarding officer data?',
//    'deleteUserGroupConfirmation' => 'Are you sure to delete this user group data?',
//    'deleteStowageConfirmation' => 'Are you sure to delete this stowage data?',
//    'deleteImageConfirmation' => 'Are you sure to delete this image?',
//    'missingTokenRequest' => 'Missing Token Request',
//    'missingSystemIdRequest' => 'Missing System ID Request',
//    'invalidTokenRequest' => 'Invalid Token Request',
//    'invalidSystemId' => 'Invalid System id for token request.',
//    'requireParameter' => 'Require Parameter',
//    'missingParameter' => 'Missing Parameter :param.',
//    'invalidValueParameter' => 'Invalid value parameter :param.',
//    'exceptionMessage' => 'Exception found, please make sure you give the correct type for all parameters.',
//    'deletePortConfirmation' => 'Are you sure to delete this port office?',
//    'questionToRequest' => 'Are sure to request ?',
//    'questionToApprove' => 'Are sure to approve this Quotation ?',
//    'quotationExpired' => 'This Quotation is Expired, please update !',
//    'quotationDraft' => 'This is Draft Quotation, Please update complete and Request !',
//    'quotationWaitingApproval' => 'Quotation is waiting Approval from manager !',
//    'quotationApproved' => 'This Quotation is Approved !',
//    'jobCanceledReason' => 'This Job has been canceled by <strong>:user</strong>, on <strong>:time</strong>,<br />because : <strong>:reason</strong>',
    'publishJobConfirmation' => 'Apakah anda yakin mempublikasikan pekerjaan ini?',
    'archiveConfirmation' => 'Apakah anda yakin mengarsipkan pekerjaan ini?',
    'reOpenArchiveConfirmation' => 'Apakah anda yakin membuka kembali arsip pekerjaan ini?',
//    'finishJobConfirmation' => 'Are you sure to finish this job?',
    'unablePublishJobOrder' => 'Pekerjaan ini tidak bisa di publikasikan, Silahkan input Manager dan Detail Pekerjaan sebelum mempublikasikan pekerjaan ini.',
    'unableArchiveJobOrder' => 'Pekerjaan ini tidak bisa di arsip, Silahkan pastikan semua faktur sudah di buat dan sudah di bayar.',
//    'unablePublishJobOrderOffice' => 'Unable to publish this job order, please complete officer information.',
//    'unablePublishJobOrderUncompleteInformation' => 'Unable to publish this job order, please provide information below.',
//    'pleaseUpdateQuantityActual' => 'Please update quantity actual for goods : :goods.',
//    'pleaseRegisterAllGoodsDamage' => 'Please register all the broken goods.',
//    'pleaseUpdateAllGoodsDamageStatus' => 'Please update all the status of broken goods, is it returned or stored.',
//    'quantityDamageNotMatch' => 'Quantity damage not match, registered :registered items, but found :found items.',
//    'inboundStorageEmpty' => 'Can not complete storage, please register the storage area for all goods.',
//    'inboundStorageNotMatch' => 'Can not complete storage, total quantity actual inbound does not match with total quantity stored. Inbound :inbound Stored :stored.',
//    'outboundPickingEmpty' => 'Can not complete picking, please register the storage area of items taken.',
//    'outboundStorageNotMatch' => 'Can not complete picking, total quantity outbound does not match with total quantity taken. Outbound :outbound taken :taken.',
//    'outboundLoadedNotMatch' => 'Can not complete loading, total quantity planning does not match with total quantity loaded. Planning :planning,  Loaded :loaded.',
//    'canNotCompleteActionForEmptyGoods' => 'Can not complete this action, please fill in the planning goods data.',
//    'cancelOrderConfirmation' => 'Are you sure to cancel this job order?',
//    'pleaseFillInAtLeastOneFiled' => 'Please fill in at least one field in the form before saving the data.',
//    'pleaseUpdateOpnameActual' => 'Please update the stock figure information for all available storage.',
//    'pleaseUpdateAdjustmentDetail' => 'Please fill in and save the adjustment goods detail.',
//    'stockArchiveDeletedReason' => 'This Archive has been canceled by <strong>:user</strong>,  because : <br /><strong>:reason</strong>',
//    'completeDocumentConfirmation' => 'Complete documentation?',
//    'missingRequiredDocument' => 'Missing Required Document',
//    'movementCompleteValidation' => 'Please fill in the VOLUME or WEIGHT information for all broken goods',
//    'sign_another_device' => 'Your credential has been used in another device. Please re-login again.',
    'noSerialNumberFound' => 'Nomor Serial untuk kode :code tidak ditemukan.',
//    'unablePublishSalesOrder' => 'Unable to publish this sales order, please provide service information.',
//    'cancelSalesOrderConfirmation' => 'Are you sure to cancel this sales order?',
//    'soCanceledReason' => 'This SO has been canceled by <strong>:user</strong>,  because : <br /><strong>:reason</strong>',
//    'generateSoNumberConfirmation' => 'Generate sales order number for this relation?',
//    'unablePublishJobOrderWlog' => 'Unable to publish this job order, please insert Job Manager, Aju Reference and Goods information.',
//    'invalidSerialNumberInbound' => 'Can not complete storage, please update all the serial number for all goods.',
//    'invalidSerialNumberOutbound' => 'Can not complete picking action, please update all the serial number for all goods.',
//    'soHoldReason' => 'This SO has been hold since <strong>:date</strong>,  because : <br /><strong>:reason</strong>',
//    'joHoldReason' => 'This Job Order has been hold since <strong>:date</strong>,  because : <br /><strong>:reason</strong>',
//    'holdConfirmation' => 'Are you sure to hold this data?',
//    'unHoldConfirmation' => 'Are you sure to un-hold this data?',
//    'movementGoodsValidation' => 'Please fill in GOODS information for this Job.',
//
//    'inklaringReleaseGoodsEmpty' => 'Can not complete release goods, please release for all goods.',
//    'inklaringReleaseGoodsNotMatch' => 'Can not complete release goods, total quantity goods does not match with total quantity loaded. register goods :inklaring Loaded :loaded.',
//    'inklaringContainerCompleteReleaseWarning' => 'Please release all containers before completing this action.',
//    'inklaringGoodsCompleteReleaseWarning' => 'Please release all goods before completing this action.',
//    'inklaringShipmentContainerLoadEmpty' => 'Can not complete shipment, please load for all container.',
//    'inklaringContainerLoadedNotMatch' => 'Can not complete release, please load for all container. register container :container Loaded :loaded.',
//    'unablePublishJobOrderContainer' => 'Unable to publish this job order, please add container information.',
//    'unablePublishJobOrderDocument' => 'Unable to publish this job order, please add document information.',
//    'requireFields' => 'Require Fields',
//    'inklaringGatePassWarning' => 'Please update all Gate Pass information before completing this action.',
//    'finishSoConfirmation' => 'Are you sure to finish this Sales Order?',
//    'serialNumberHistoryStatistic' => 'Please fill in serial number or range date to search the data.',
//    'packingNumberHistoryStatistic' => 'Please fill in packing number or range date to search the data.',
//    'invalidGoodsImport' => 'Goods with sku <strong>:sku</strong> with uom <strong>:uom</strong> was not registered yet.',
//    'duplicateGoodsImport' => 'Duplicate Goods with sku <strong>:sku</strong> inside the excel file.',
//    'goodsImportAlreadyRegistered' => 'Goods with sku <strong>:sku</strong> already exist in this job order.',
//    'invalidExcelData' => 'Invalid Excel Data.',
//    'invalidExcelHeaderData' => 'Invalid Excel Header Data.',
//    'unableUploadJogExcel' => 'Unable to upload the file, please try again.',
//    'invalidSnInbound' => 'Invalid SN :sn.',
//    'invalidPrefixSnInbound' => 'Invalid prefix :prefix for SN :sn.',
//    'duplicateSnInbound' => 'SN :sn has already been scaned.',
    'deletedData' => 'Data ini sudah di hapus oleh <strong>:user</strong>, pada tanggal <strong>:time</strong>,<br />karena : <strong>:reason</strong>',
//    'rejectRequest' => 'This request has been rejected by <strong>:user</strong>,on <strong>:time</strong>,<br />because : <strong>:reason</strong>',
//    'createBundleConfirmation' => 'Are you sure to start creating new bundle?',
//    'validationOutstandingBundle' => 'Please complete your outstanding bundle before creating new bundle.',
//    'bundlingQuantityNotMatch' => 'Can not complete bundling, total quantity planning does not match with total quantity complete bundling. Planning :planning,  Complete Bundling :bundling.',
//    'invalidBundlingStock' => 'SKU = <strong>:sku</strong>, Qty Required = <strong>:required :uom</strong>,  but Available Stock only <strong>:stock :uom</strong>.',
//    'invalidAvailableStock' => 'Invalid Available Stock',
//    'UnBundlingQuantityNotMatch' => 'Can not complete un-bundling, total quantity planning does not match with total quantity complete un-bundling. Planning :planning,  Complete un-Bundling :bundling.',
//    'invoiceRequestConfirmation' => 'Are you sure to request approval for these invoice?',
//    'invoiceRequestWarningAmount' => 'No Data found for invoice detail.',
//    'invoiceRequestWarningCa' => 'Invoice and Cash Advance must be in the same amount.',
//    'invoiceApproveConfirmation' => 'Are you sure to approve this invoice?',
//    'invoiceRejectConfirmation' => 'Are you sure to reject this invoice?',
//    'invoiceRejected' => 'This invoice has been rejected by <strong>:user</strong>,on <strong>:time</strong>,<br />because : <strong>:reason</strong>',
//    'invoicePaymentConfirmation' => 'Are you sure to confirm payment for this invoice?',
//    'requestApprovalConfirmation' => 'Are you sure to request approval for this data?',
//    'approvalRequestConfirmation' => 'Are you sure to approve this request?',
//    'rejectRequestConfirmation' => 'Are you sure to reject this request?',
//    'paymentDepositConfirmation' => 'Are you sure to paid this job deposit?',
//    'claimSettlementConfirmation' => 'Are you sure to confirm settlement for this deposit data?',
//    'depositRefundConfirmation' => 'Are you sure to confirm receive for this deposit refund?',
//    'invalidRecipientImport' => 'Recipient Name or phone number empty. Invoice number <strong>:invoice</strong>, Line Number <strong>:line_number</strong>',
//    'invalidCourierImport' => 'Courier Name empty. Invoice number <strong>:invoice</strong>, Line Number <strong>:line_number</strong>',
//    'invalidJobGoodsImport' => 'Product with SKU <strong>:sku</strong> Not Registered. Invoice number <strong>:invoice</strong>, Line Number <strong>:line_number</strong>',
//    'emptySkuProduct' => 'Product Sku Empty. Invoice number <strong>:invoice</strong>, Line Number <strong>:line_number</strong>',
//    'invalidJobOutboundImport' => 'Invoice number or payment date empty. Invoice number <strong>:invoice</strong>, Line Number <strong>:line_number</strong>',
//    'greetingThank' => 'Thank you for shopping<br> :relation.',
//    'closeIssueConfirmation' => 'Are you sure to close this issue?',
//    'apiKeyRefreshConfirmation' => 'Are you sure to refresh the api key?',
//    'priceApprovalConfirmation' => 'Are you sure to Approve this price?',
//    'message' => 'Message',
//    'unableApprovePrice' => 'Unable to approve this price, please add price detail information.',
//    'reopenIssueConfirmation' => 'Are you sure to re-open this issue?',
//    'invalidRequestIdPdfTrucking' => 'Invalid id for data trucking.',
//    'uniquePriceTrucking' => 'The truck type for origin and destination route has already been taken.',
//    'uniquePriceInklaring' => 'The customs clearance type for selected port has already been taken.',
//    'uniquePriceWarehouse' => 'The price for this selected warehouse has already exist.',
//    'noRelationSerialNumberFound' => 'Not found relation reference for Serial Number :code.',
//    'quotationRejected' => 'This quotation has been rejected by <strong>:user</strong>,on <strong>:time</strong>,<br />because : <strong>:reason</strong>',
//    'unableToSubmitQuotation' => 'Unable to submit quotation, please add price details before submitting quotation.',
//    'submitQuotationConfirmation' => 'Are you sure to submit this quotation?',
//    'unableToApproveQuotation' => 'Unable to approve quotation, please set all cost code for selected price.',
//    'approveQuotationConfirmation' => 'Are you sure to approve this quotation?',
//    'rejectQuotationConfirmation' => 'Are you sure to reject this quotation?',
//    'rejectedQuotation' => 'This quotation has been rejected by <strong>:user</strong>,on <strong>:time</strong>,<br />because : <strong>:reason</strong>',
//    'quotationConfirmationAndApproval' => 'We do hope the above rate could meet your requirement, Please feel free to contact us if you need further details. We are waiting to your confirmation. Thank you.',
//    'requiredPublishSo' => 'Please publish sales order first before publishing this job.',
//    'requiredGoodsForServiceInput' => 'Please provide goods information before adding service information.',
//    'missingRequiredFields' => 'Missing required fields',
//    'missingRequiredSoDocument' => 'Please provide required document form sales order detail.',
//    'inklaringContainerCompleteShipmentWarning' => 'Please shipment all containers before completing this action.',
//    'inklaringGoodsCompleteShipmentWarning' => 'Please shipment all goods before completing this action.',
//    'missingDeliveryOrderDetails' => 'Please add information for delivery details before publishing this job.',
//    'createJobInklaringConfirmation' => 'Are you sure to create inklaring job?',
//    'missingSkuJobGoodsWarehouse' => 'Please Update SKU of all job goods warehouse.',
//    'missingSocInSog' => 'Party ID for Goods :sogNumber.',
//    'missingTruckTypeInSoc' => 'Truck Type for Party :socNumber.',
//    'emptySogData' => 'Please fill in GOODS information for this SO.',
//    'emptySoqData' => 'Please insert quotation for this SO.',
//    'pleaseSelectOneOption' => 'Please select at least one option.',
//    'movementSogData' => 'Please fill in GOODS information for this SO.',
//    'mailModelNotFound' => 'Mail Model Not Found.',
//    'missingCashAdvanceDetailData' => 'Please select cash advance detail data.',
//    'invalidCaSettlementAmount' => 'Invalid cash settlement amount, Actual Amount = :actual, but total registered = :registered.',
//    'topUpExceedCeiling' => 'Your request exceeds the maximum ceiling, Please close all on going cash payment before making new request.',
//    'emptyJobFinanceData' => 'Please insert sales and purchase information for this Job Order.',
//    'notEnoughBalanceToPayCa' => 'This account does not have sufficient balance to make this payment. please make a request to top up your balance.',
//    'unableToFinishJobBeforeInvoice' => 'Please make sure to create or register all invoice for all financial items in this job.',
//    'blockedAccount' => 'This account has been blocked by <strong>:user</strong>,on <strong>:time</strong>,<br />because : <strong>:reason</strong>',
//    'blockAccountConfirmation' => 'Are you sure to block this account?',
//    'bankTransactionApproveConfirmation' => 'Are you sure to approve this transaction?',
//    'bankTransactionRejectConfirmation' => 'Are you sure to reject this transaction?',
//    'bankTransactionPaymentConfirmation' => 'Are you sure to confirm payment for this transaction?',
//    'caReceiveRejected' => 'This cash payment receive confirmation has been rejected by <strong>:user</strong>,on <strong>:time</strong>,<br />because : <strong>:reason</strong>',
//    'caReturnRejected' => 'This cash payment return confirmation has been rejected by <strong>:user</strong>,on <strong>:time</strong>,<br />because : <strong>:reason</strong>',
//    'cashPaymentNoDetailFound' => 'No data found for cash payment detail.',
];
