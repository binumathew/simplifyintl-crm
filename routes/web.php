<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/  
Auth::routes();

Route::get('/', function () {
    return redirect('/login');
});
Route::get('/dashboard', 'DashboardController@index');
Route::post('/dashboard', 'DashboardController@index');
Route::get('/authenticate/{id}', 'DashboardController@custom_authenticate');

/* Conference */
Route::get('/conference', 'ConferenceController@index');
Route::get('/conference-list', 'ConferenceController@conference_list');
Route::post('/conference-list', 'ConferenceController@conference_list');
Route::post('/conference-details', 'ConferenceController@conference_details');
Route::post('/delete-conference', 'ConferenceController@delete_conference');
Route::get('/user-list', 'ConferenceController@user_list');
Route::get('/conference-user-list', 'ConferenceController@conference_user_list');
Route::post('/conf-user-status', 'ConferenceController@conf_user_status');


/* Payment */
Route::get('/payment-history', 'PaymentHistoryController@list_payment_history');
Route::get('/payment-pagination', 'PaymentHistoryController@payment_history_pagination');
Route::post('/payment-pagination', 'PaymentHistoryController@payment_history_pagination');
Route::post('/refund-process', 'PaymentHistoryController@payment_refund_process');

/* Call History */
Route::get('/call-history', 'CallHistoryController@list_call_history');
Route::get('/call-history-list', 'CallHistoryController@call_history_list');
Route::post('/call-history-list', 'CallHistoryController@call_history_list');
// Route::get('/import-cdr', 'CallHistoryController@import_cdr');
// Route::post('/import-cdr', 'CallHistoryController@import_cdr_data');

/* Order Process */
Route::get('/new-order', 'OrderController@create_order');
Route::get('/select-plan', 'OrderController@select_plan');
Route::post('/select-plan', 'OrderController@selected_plan');
Route::get('/bolt-ons', 'OrderController@select_bolt_ons');
Route::post('/selected-plan', 'OrderController@selected_plan_popup');
Route::post('/manage-bolt-ons', 'OrderController@manage_bolt_ons');
Route::get('/provision', 'OrderController@provision_request');
Route::post('/provision', 'OrderController@provision_process');
Route::get('/billing', 'OrderController@billing');
Route::post('/billing', 'OrderController@billing_process');
Route::get('/summary', 'OrderController@order_summary');
Route::get('/payment', 'OrderController@payment');
Route::post('/payment', 'OrderController@process_payment');
Route::post('/select-gateway', 'OrderController@select_gateway');
Route::get('/success', 'OrderController@payment_success');
Route::get('/abandoned-orders', 'OrderController@abandoned_orders');
Route::get('/abandoned-list', 'OrderController@abandoned_orders_list');

Route::get('/orders', 'OrderController@orders');
Route::get('/orders-list', 'OrderController@orders_list');
Route::post('/orders-list', 'OrderController@orders_list');
Route::post('/update-promocode', 'OrderController@update_order_promocode');
Route::get('/order-details', 'OrderController@order_details');
Route::post('/order-details', 'OrderController@order_details');
Route::post('/item-detail', 'OrderController@sim_list_details');
Route::get('/orders-webreq', 'OrderController@orders_webreq');
Route::get('/orders-webreqlist', 'OrderController@orders_webreqlist');
Route::post('/orders-webreqlist', 'OrderController@orders_webreqlist');

/* Activation Controller */
Route::post('/sim-list', 'ActivationController@show_sim_list');
Route::post('/order-provision', 'ActivationController@order_provision');
Route::post('/terms-and-condition', 'ActivationController@terms_and_condition');
Route::post('/pro-rata-billing', 'ActivationController@prorata_billing');
Route::post('/pro-rata-payment', 'ActivationController@prorata_billing_process');
Route::post('/activate', 'ActivationController@activate');
Route::post('/subscribe', 'ActivationController@subscribe');
Route::post('/create-sippy-account', 'ActivationController@create_sippy_account');
Route::post('/process-email', 'ActivationController@process_order_email');
Route::post('/port-request', 'ActivationController@port_request');
Route::post('/provision-request', 'ActivationController@provision_request');
Route::post('/verify-sim-number', 'ActivationController@verfiy_sim_number');
Route::post('/verify-pac-code', 'ActivationController@verfiy_pac_code');
Route::post('/provision-process', 'ActivationController@provision_process');
Route::post('/provision-check', 'ActivationController@provision_check');

/* User Contoller */
Route::get('/users', 'UserController@users');
Route::get('/list-users', 'UserController@list_users');
Route::get('/user-details', 'UserController@user_detail');
Route::post('/user-details', 'UserController@user_detail');
Route::post('/user-data', 'UserController@user_data');
Route::post('/update-user', 'UserController@update_user');
Route::get('/temp-user', 'UserController@temp_users');
Route::get('/temp-user-list', 'UserController@temp_users_list');
Route::get('/fraudsters', 'UserController@list_fraudsters');
Route::post('/search-user', 'UserController@search_user');
Route::post('/save-note', 'UserController@save_note');
Route::post('/delete-user', 'UserController@delete_user');
Route::post('/delete-fraudster', 'UserController@delete_fraudsters');
Route::post('/delete-temp-user', 'UserController@delete_temp_user');
Route::post('/manage-status', 'UserController@change_user_status');
Route::post('/reset-otp-try', 'UserController@reset_otp_try');
Route::post('/manage-subscription', 'UserController@manage_subscription');
Route::post('/update-call-settings', 'UserController@update_call_settings');
Route::post('/card-list', 'UserController@user_card_list');
Route::post('/remove-card', 'UserController@remove_user_card');
Route::post('/change-subscription-card', 'UserController@change_subscription_card');
Route::post('/renewal-list', 'UserController@subscription_renewal_list');
Route::post('/subscription-renewal', 'UserController@sim_subscription_renewal');
Route::post('/credit-debit-manage', 'UserController@credit_debit_manage');
Route::post('/cal-credit-debit', 'UserController@cal_credit_debit');
Route::post('/credit-debit-gateway', 'UserController@credit_debit_gateway');
Route::get('/direct-debit/{id?}','UserController@direct_debit');
Route::post('/direct-debit-manage','UserController@direct_debit_manage');
Route::get('/opted-services','UserController@opted_services');
Route::get('/user-services','UserController@user_services');
Route::post('/user-services','UserController@user_services');
Route::post('/services-change','UserController@services_change');

/* Staff & Dealer */
Route::get('/staff-list', 'StaffController@staff_list');
Route::get('/dealers', 'StaffController@dealers');
Route::post('/send-password', 'StaffController@send_password');
Route::get('/create-dealer', 'StaffController@create_dealer');
Route::get('/create-staff', 'StaffController@create_staff');
Route::get('/edit-staff/{id}', 'StaffController@edit_staff_details');
Route::get('/edit-dealer/{id}', 'StaffController@edit_dealer_details');
Route::post('/get-parent', 'StaffController@get_parents');
Route::post('/check-promocode', 'StaffController@check_promocode');
Route::post('/save-staff', 'StaffController@save_staff');

/* Delivery */
Route::get('/delivery', 'DeliveryController@order_delivery');
Route::get('/delivery-list', 'DeliveryController@delivery_list');
Route::post('/delivery-list', 'DeliveryController@delivery_list');
Route::post('/order-status', 'DeliveryController@order_status');
Route::post('/order-update', 'DeliveryController@update_order');
Route::post('/re-order', 'DeliveryController@process_reorder');
Route::post('/sim-details', 'DeliveryController@sim_details');
Route::post('/order-shipment', 'DeliveryController@order_shipment');
Route::post('/find-address', 'DeliveryController@find_address');
Route::post('/enquiry', 'DeliveryController@enquiry_history');
Route::post('/save-enquiry', 'DeliveryController@save_enquiry');
Route::post('/update-address', 'DeliveryController@update_shipping_address');
Route::post('/welcome-letter', 'DeliveryController@print_welcome_letter');
Route::post('/cancel-order', 'DeliveryController@order_cancellation');
Route::post('/order-cancel-refund', 'DeliveryController@order_cancel_refund');



/* Settings Module */
Route::get('/settings', 'SettingsController@index');
Route::get('/roles', 'SettingsController@list_role');
Route::get('/manage-role/{id?}', 'SettingsController@manage_role');
Route::post('/save-role', 'SettingsController@save_role');
Route::get('/api-logger', 'SettingsController@api_logger');
Route::get('/list-api-log', 'SettingsController@list_api_log');
Route::get('/scheduled-tasks', 'SettingsController@scheduled_task');
Route::get('/manage-task/{id?}', 'SettingsController@manage_scheduled_task');
Route::post('/save-task', 'SettingsController@save_scheduled_task');
Route::post('/delete-task', 'SettingsController@delete_scheduled_task');
Route::post('/execute-task', 'SettingsController@execute_scheduled_task');
Route::get('/firewall', 'SettingsController@firewall');
Route::post('/save-firewall', 'SettingsController@save_firewall');
Route::post('/delete-firewall', 'SettingsController@delete_firewall');
Route::get('/throttles', 'SettingsController@throttles');
Route::get('/throttle-list', 'SettingsController@throttle_list');
Route::post('/delete-throttle', 'SettingsController@delete_throttle');
Route::get('/countries', 'SettingsController@list_countries');
Route::get('/country/{id?}', 'SettingsController@add_edit_country');
Route::post('/save-country', 'SettingsController@save_country');
Route::get('/coupons', 'SettingsController@list_coupons');
Route::post('/coupon', 'SettingsController@manage_coupon');
Route::post('/save-coupon', 'SettingsController@save_coupon');
Route::post('/delete-coupon', 'SettingsController@delete_coupon');
Route::get('/template/{id?}', 'SettingsController@template');
Route::get('/payment-gateway', 'SettingsController@payment_gateway');
Route::get('/credits', 'SettingsController@list_credits');
// Route::get('/edit-credits/{id}', 'DashboardController@edit_credits');
// Route::post('/update-credits/{id}', 'DashboardController@update_credits');

Route::get('/switch', 'SettingsController@switch_template');
Route::get('/did-pool', 'SettingsController@list_did_numbers');
Route::post('update-custom-settings','SettingsController@update_custom_settings');
Route::post('custom-changesettings','SettingsController@custom_changesettings');
Route::post('update-company-settings','SettingsController@update_company_settings');
Route::post('update-sms-settings','SettingsController@update_sms_settings');
Route::post('update-switch-settings','SettingsController@update_switch_settings');
Route::post('add-settings-form','SettingsController@add_settings_form');
Route::post('add-settings-data','SettingsController@add_settings_data');
Route::post('upload-company-images','SettingsController@upload_company_images');
Route::post('email-template-actions','SettingsController@email_template_actions');
Route::post('email-template-delete','SettingsController@email_template_delete');
Route::get('activity-log','SettingsController@activity_log');
Route::get('list-activitylog','SettingsController@list_activitylog');
Route::post('list-activitylog','SettingsController@list_activitylog');
Route::post('/notify-process', 'SettingsController@notify_process');

/* Porting Module */
Route::get('/port-list', 'PortController@port_list');
Route::get('/porting-list', 'PortController@porting_list');
Route::post('/create-port', 'PortController@create_port_request');
Route::get('/edi-port/{id}', 'PortController@edit_port_request');
Route::post('/port-details', 'PortController@port_description');
Route::post('/update-port', 'PortController@update_port_request');
Route::post('/port-finish', 'PortController@finish_port_process');

/* Stock */
Route::get('/stock-list', 'StockController@list_sim_stock');
Route::get('/stock-list-item', 'StockController@list_stock_item');
Route::post('/stock-list-item', 'StockController@list_stock_item');
Route::post('/import-stock', 'StockController@import_sim_stock');
Route::post('/edit-stock', 'StockController@edit_sim_stock');
Route::post('/update-stock-list', 'StockController@update_stock_list'); 
Route::post('/reset-stock-list', 'StockController@reset_stock_list');
Route::post('/get-box', 'StockController@get_box');
Route::post('/get-stock', 'StockController@get_stock');
Route::post('/assign-stock', 'StockController@assign_stock');
Route::post('/manage-stock', 'StockController@manage_stock');

/* Report */
Route::get('/report', 'ReportController@index');
Route::get('/report-autoplan','ReportController@report_autoplan');
Route::get('/list-autoplan','ReportController@list_autoplan');
Route::post('/list-autoplan','ReportController@list_autoplan');
Route::get('/report-cardexpiry','ReportController@report_cardexpiry');
Route::get('/list-cardexpiry','ReportController@list_cardexpiry');
Route::post('/list-cardexpiry','ReportController@list_cardexpiry');
Route::get('/report-user','ReportController@report_user');
Route::get('/list-user','ReportController@list_user');
Route::post('/list-user','ReportController@list_user');
Route::get('/report-usage','ReportController@report_usage');
Route::get('/list-usage','ReportController@list_usage');
Route::post('/list-usage','ReportController@list_usage');
Route::get('/report-order', 'ReportController@report_order')->name('reports');
Route::get('/list-order','ReportController@list_order');
Route::post('/list-order','ReportController@list_order');
Route::get('/report-dashboard','ReportController@report_dashboard');
Route::post('/report-dashboard','ReportController@report_dashboard');
Route::get('/report-activation','ReportController@report_activation');
Route::get('/list-activation','ReportController@list_activation');
Route::post('/list-activation','ReportController@list_activation');
Route::post('/get-cdr-records','ReportController@get_cdr_records');
Route::get('/report-dispute','ReportController@report_dispute');
Route::get('/list-dispute','ReportController@list_dispute');
Route::post('/list-dispute','ReportController@list_dispute');
Route::get('/report-invoice','ReportController@report_invoice');
Route::get('/list-invoice','ReportController@list_invoice');
Route::post('/list-invoice','ReportController@list_invoice');
Route::get('/report-invoice-txn','ReportController@report_invoice_txn');
Route::get('/list-invoice-txn','ReportController@list_invoice_txn');
Route::post('/list-invoice-txn','ReportController@list_invoice_txn');

/* Billing */
// Route::get('/api-login','BillingController@api_login');
// Route::get('/create_new_site','BillingController@create_new_site');
Route::get('/get_site_details','BillingController@get_site_details');
// Route::get('/add_cli','BillingController@add_cli');
// Route::get('/get_site_byref','BillingController@get_site_byref');
// Route::get('/update_site_byId','BillingController@update_site_byId');
// Route::get('/get_site_address','BillingController@get_site_address');
// Route::get('/update_site_address','BillingController@update_site_address');
// Route::get('/update_site_address_type','BillingController@update_site_address_type');
// Route::get('/update_site_contact','BillingController@update_site_contact');
// Route::get('/get_site_contact','BillingController@get_site_contact');
// Route::get('/create_site_email','BillingController@create_site_email');
// Route::get('/get_site_email','BillingController@get_site_email');
// Route::get('/update_site_email','BillingController@update_site_email');
// Route::get('/delete_site_email','BillingController@delete_site_email');
// Route::get('/update_cli','BillingController@update_cli');
// Route::get('/update_cli_details','BillingController@update_cli_details');




/* Mobility AT&T  */
Route::get('/verify-imei','AttController@verify_imei');
Route::get('/sim-activation','AttController@sim_activation');
Route::get('/sim-activation-status','AttController@check_sim_activation_status');
Route::get('/port-eligible','AttController@check_port_eligible');
Route::get('/sim-port','AttController@sim_port');
Route::get('/sim-port-status','AttController@check_sim_porting_status');
Route::get('/sim-port-modify','AttController@sim_port_modify');
Route::get('/sim-port-delete','AttController@sim_port_delete');
Route::get('/sim-account-status','AttController@sim_account_status');
Route::get('/sim-service-info','AttController@sim_service_info');
Route::get('/sim-service-modify-actions','AttController@sim_service_modify_actions');
Route::get('/sim-service-modify-equipment','AttController@sim_service_modify_equipment');
Route::get('/sim-service-modify-features','AttController@sim_service_modify_features');
Route::get('/sim-service-modify-plan','AttController@sim_service_modify_plan');
Route::get('/sim-usage','AttController@sim_usage');
Route::get('/sim-usage-all','AttController@sim_usage_all');
Route::get('/sim-usage-mdn','AttController@sim_usage_mdn');
Route::get('/sim-usage-syncdate','AttController@sim_usage_syncdate');
Route::get('/sim-subscribers-list','AttController@sim_subscribers_list');
Route::get('/att-validate-address','AttController@att_validate_address');


/* Plans  */
Route::get('/sim-plans','PlanController@sim_plans');
Route::get('/sim-plans-list','PlanController@sim_plans_list');
Route::post('/sim-plans-list','PlanController@sim_plans_list');
Route::get('/sim-plans-manage/{id?}','PlanController@sim_plans_manage');
Route::post('/sim-plans-actions','PlanController@sim_plans_actions');

Route::get('/switch-plans','PlanController@switch_plans');
Route::get('/switch-plans-list','PlanController@switch_plans_list');
Route::post('/switch-plans-list','PlanController@switch_plans_list');
Route::get('/switch-plans-manage/{id?}','PlanController@switch_plans_manage');
Route::post('/switch-plans-actions','PlanController@switch_plans_actions');

Route::get('/conf-plans','PlanController@conf_plans');
Route::get('/conf-plans-list','PlanController@conf_plans_list');
Route::post('/conf-plans-list','PlanController@conf_plans_list');
Route::get('/conf-plans-manage/{id?}','PlanController@conf_plans_manage');
Route::post('/conf-plans-actions','PlanController@conf_plans_actions');

Route::post('/plan-delete','PlanController@plan_delete');
Route::post('/plan-view','PlanController@plan_view');





/* Commission  */
Route::get('/comm-plan', 'CommissionController@comm_plan');
Route::get('/comm-plan-list', 'CommissionController@comm_plan_list');
Route::get('/commplan-manage/{id?}', 'CommissionController@commplan_manage');
Route::post('/comm-getplan','CommissionController@comm_getplan');
Route::post('/save-plancommission','CommissionController@save_plancommission');
Route::post('/delete-comm','CommissionController@delete_comm');
Route::get('/comm-plan-dealer','CommissionController@comm_plan_dealer');
Route::get('/commdealer-manage/{id?}','CommissionController@commdealer_manage');
Route::post('/save-dealercommission','CommissionController@save_dealercommission');
Route::get('/comm-dealer-list', 'CommissionController@comm_dealer_list');
Route::post('/comm-view', 'CommissionController@comm_view');
Route::post('/get-revenue','CommissionController@get_revenue');
Route::post('/add-revenue','CommissionController@add_revenue');
Route::get('/clawback-plan', 'CommissionController@clawback_plan');
Route::get('/clawback-list', 'CommissionController@clawback_list');
Route::get('/clawback-manage/{id?}', 'CommissionController@clawback_manage');
Route::post('/save-clawback','CommissionController@save_clawback');
Route::get('/clawback-dealer', 'CommissionController@clawback_dealer');
Route::post('/delete-clawback','CommissionController@delete_clawback');
Route::get('/comm-payment','CommissionController@comm_payment');
Route::get('/comm-payment-list','CommissionController@comm_payment_list');
Route::post('/comm-user', 'CommissionController@user_commission');
Route::get('/comm-user-list', 'CommissionController@user_commission_list');
Route::post('/comm-breakdown', 'CommissionController@comm_breakdown');
Route::post('/pay-commission', 'CommissionController@pay_commission');


/* Invoice  */
Route::get('/invoice-list', 'InvoiceController@invoice_list');
Route::get('/generate-invoices/{id}', 'InvoiceController@generate_invoices');


//Route::post('/update-port-status', 'ActivationController@change_port_status');
// Route::get('/porting', 'ActivationController@porting_request');
// Route::get('/edit-port-request/{id}', 'ActivationController@edit_porting_request');
// Route::post('/update-porting', 'ActivationController@update_porting_request');
// Route::post('/port-finish', 'ActivationController@finish_porting_process');

Route::get('/logout', function () {
	Auth::logout();
	Session::flush();
    return redirect('/login');
});

Route::get('/dwp', 'DwpController@dwp_sim_check');



//Route::get('/new-order', 'DashboardController@order_check');



// Route::get('/profile', 'DashboardController@get_users');
// Route::get('/permission', 'DashboardController@get_users');



// Route::get('/report-order', 'DashboardController@report_order')->name('reports');
// Route::get('/report-eelog', 'DashboardController@report_eelog')->name('reports');
// Route::get('/report-port', 'DashboardController@report_port')->name('reports');
// Route::get('/report-autoplan', 'DashboardController@report_autoplan')->name('reports');
// Route::get('/report-autorecharge', 'DashboardController@report_autorecharge')->name('reports');
// Route::get('/report-subscription', 'DashboardController@report_subscription')->name('reports');
// Route::get('/list-report-order', 'DashboardController@list_report_order')->name('reports');
// Route::post('/list-report-order', 'DashboardController@list_report_order')->name('reports');
// Route::get('/list-eelog', 'DashboardController@list_eelog')->name('reports');
// Route::get('/list-port', 'DashboardController@list_port')->name('reports');
// Route::post('/list-port', 'DashboardController@list_port')->name('reports');
// Route::get('/list-report-autoplan', 'DashboardController@list_report_autoplan')->name('reports');
// Route::post('/list-report-autoplan', 'DashboardController@list_report_autoplan')->name('report');
// Route::get('/list-report-autorecharge', 'DashboardController@list_report_autorecharge')->name('reports');
// Route::post('/list-report-autorecharge', 'DashboardController@list_report_autorecharge')->name('reports');
// Route::get('/list-report-subscription', 'DashboardController@list_report_subscription')->name('reports');
// Route::post('/list-report-subscription', 'DashboardController@list_report_subscription')->name('reports');
// Route::get('/report-staff-comm', 'DashboardController@report_staff_comm')->name('reports');
// Route::get('/list-report-staff-comm', 'DashboardController@list_report_staff_comm')->name('reports');
// Route::post('/list-report-staff-comm', 'DashboardController@list_report_staff_comm')->name('reports');
// Route::get('/report-dealer-comm', 'DashboardController@report_dealer_comm')->name('reports');
// Route::get('/list-report-dealer-comm', 'DashboardController@list_report_dealer_comm')->name('reports');
// Route::post('/list-report-dealer-comm', 'DashboardController@list_report_dealer_comm')->name('reports');
// Route::post('/report-dealer-details', 'DashboardController@report_dealer_details')->name('reports');
// Route::get('/report-dealer-full', 'DashboardController@report_dealer_full')->name('reports');
// Route::post('/report-dealer-full', 'DashboardController@report_dealer_full')->name('reports');
// // Route::get('/stock-management', 'DashboardController@get_users');



// Route::get('/addsettings','DashboardController@addsettings');
// Route::get('/addsettings/{id}','DashboardController@addsettings');
// Route::post('/save-settings','DashboardController@save_settings');
// Route::post('/delete-settings','DashboardController@delete_settings');

// Route::get('/plan-management', 'PlanController@list_plans')->name('plan');
// Route::get('/create-plan', 'PlanController@create_plan');
// Route::get('/edit-plan/{id}', 'PlanController@edit_plan');
// Route::post('/save-plan', 'PlanController@save_plan');

// Route::get('/package-management', 'PlanController@list_packages')->name('plan');
// Route::get('/create-package', 'PlanController@create_package');
// Route::get('/edit-package/{id}', 'PlanController@edit_package');
// Route::post('/save-package', 'PlanController@save_package');

// Route::get('/switch-management', 'PlanController@list_switch_template');
// Route::get('/plan-purchase/{id?}', 'PlanController@plan_purchase');


// Route::post('/update-delivery-status', 'DeliveryController@update_status');






// Route::get('/edit-user/{id}', 'UserController@edit');
// Route::post('/update-user/{id}', 'UserController@update');
// Route::post('/delete-user', 'UserController@delete_user');

// Route::post('/user-port', 'UserController@user_porting');
// Route::post('/split-user', 'UserController@split_child_user');

// Route::post('/update-autoplan-status', 'UserController@update_autoplan_status');
// Route::post('/update-autorcharge-status', 'UserController@update_autorecharge_status');
// Route::post('/update-sim-history', 'UserController@update_sim_history');
// Route::post('/get-sim-info', 'UserController@get_sim_info');
// Route::post('/change-user-card', 'UserController@change_user_card');
// Route::post('/add-autorecharge', 'UserController@add_autorecharge');


// Route::get('/inter-plan/{id}','UserController@inter_plan');
// Route::post('/save-interplan','UserController@save_interplan');
// Route::get('/add-member/{id?}', 'UserController@add_child_user');
// Route::post('/save-member', 'UserController@save_child_user');

// Route::get('/download-usage/{id?}', 'UserController@download_usage');
// Route::post('/reset-password', 'UserController@resend_password');
// Route::post('/custom-debit', 'UserController@user_custom_debit');
// Route::post('/send-payment-link', 'UserController@send_payment_link');
// Route::post('/renew-switch-plan', 'UserController@renew_switch_plan');






// Route::post('/activation-process', 'ActivationController@process_activation')->name('sim');
// Route::post('/mark-welcome-call', 'ActivationController@mark_welcome_call')->name('sim');

// Route::post('/enable-auto-recharge', 'ActivationController@enable_auto_recharge');
// Route::post('/change-port-status', 'ActivationController@change_port_status');

// Route::get('/get_crd', 'StockController@get_crd');
// Route::get('/import-sim', 'StockController@import_sim')->name('import_sim');
// Route::post('/import-sim', 'StockController@import_sim_data');
// Route::get('/dealer-assign', 'StockController@dealer_assign');
// Route::post('/search-imsi-number', 'StockController@search_imsi_number');
// Route::post('/sim-allocate', 'StockController@sim_allocate');
// Route::post('/update-stock-list', 'StockController@update_stock_list');
// Route::post('/search-number-range', 'StockController@search_number_range');

// Route::get('/billing/{id}', 'SimController@billing')->name('billing');
// Route::post('/billing/{id}', 'SimController@process_billing')->name('process_billing');

// Route::get('/cart', 'SimController@get_cart'); 
// Route::post('/add-cart', 'SimController@add_cart_item');
// Route::post('/update-cart', 'SimController@update_cart_item');
// Route::post('/get-cart-item-data', 'SimController@get_cart_item_data');
// Route::post('/remove-cart-item', 'SimController@remove_cart');
// Route::post('/reserve-sim', 'SimController@reserve_sim');
// Route::post('/get_postcode', 'SimController@get_postcode');
// Route::post('/add-cart', 'SimController@add_cart_item');
// Route::post('/self-payment-link', 'SimController@send_user_self_payment_link');
// Route::post('/reload-sim', 'SimController@reload_sim');
// Route::post('/search-number', 'SimController@search_number');
// Route::post('/check-discount-coupon', 'SimController@check_discount_coupon');
// Route::post('/action-discount', 'SimController@action_discount');
// Route::get('/user-payment/{id}', 'SimController@user_payment');
// Route::get('/success/{id}', 'SimController@payment_success');
// Route::get('/get-time', 'SimController@get_server_time');
    
// Route::get('/cdr-summary', 'DashboardController@get_cdr_summary');

// Route::get('/commission', 'CommissionController@index');
// Route::get('/comm-planlist', 'CommissionController@comm_planlist')->name('commission');
// Route::get('/comm-userlist', 'CommissionController@comm_userlist')->name('commission');
// Route::get('/create-commplan', 'CommissionController@create_commplan')->name('commission');
// Route::get('/create-commplanuser', 'CommissionController@create_commplanuser')->name('commission');
// Route::post('/comm-getplan', 'CommissionController@comm_getplan')->name('commission');
// Route::post('/save-plancommission', 'CommissionController@save_plancommission');
// Route::post('/save-userplancommission', 'CommissionController@save_userplancommission');
// Route::get('/edit-commplan/{id}', 'CommissionController@edit_commplan');
// Route::get('/edit-commplanuser/{id}', 'CommissionController@edit_commplanuser');
// Route::get('/comm-payment-report', 'CommissionController@commission_payment_report');
// Route::get('/comm-payment-list', 'CommissionController@list_commission_payment_report');
// Route::post('/pay-commission', 'CommissionController@pay_commission');
// Route::post('/comm-user', 'CommissionController@user_commission');
// Route::post('/comm-staff', 'CommissionController@staff_commission');
// Route::get('/comm-user-details', 'CommissionController@user_commission_details')->name('commission');
// Route::get('/comm-staff-details', 'CommissionController@staff_commission_details')->name('commission');
// Route::post('/comm-staff-details', 'CommissionController@staff_commission_details')->name('commission');
// Route::post('/comm-breakdown', 'CommissionController@comm_breakdown');
// Route::get('/comm-staff-list', 'CommissionController@comm_staff_list')->name('commission');
// Route::post('/comm-staff-generate', 'CommissionController@comm_staff_generate');
// Route::get('/staff-commission', 'StaffController@staff_commission');
// Route::get('/staff-commission-list', 'StaffController@staff_commission_list');
// Route::get('/add-staff-commission', 'StaffController@add_staff_commission');
// Route::get('/edit-staff-commission/{id}', 'StaffController@edit_staff_commission');
// Route::post('/save-staff-commission', 'StaffController@save_staff_commission');
// Route::get('/comm-staffpayment-report', 'CommissionController@comm_staffpayment_report')->name('commission');
// Route::get('/comm-staff-portlist', 'CommissionController@comm_staff_portlist')->name('commission');
// Route::get('/staff-port-commission-list', 'CommissionController@staff_port_commission_list');
// Route::get('/add-staff-port-commission', 'CommissionController@add_staff_port_commission');
// Route::post('/save-staff-port-commission', 'CommissionController@save_staff_port_commission');
// Route::get('/add-staff-port-commission/{id}', 'CommissionController@add_staff_port_commission');
// Route::get('/comm-staff-paycreditlist', 'CommissionController@comm_staff_paycreditlist')->name('commission');
// Route::get('/staff-paycredit-commission-list', 'CommissionController@staff_paycredit_commission_list');
// Route::get('/add-staff-paycredit-commission', 'CommissionController@add_staff_paycredit_commission');
// Route::post('/save-staff-paycredit-commission', 'CommissionController@save_staff_paycredit_commission');
// Route::get('/add-staff-paycredit-commission/{id}', 'CommissionController@add_staff_paycredit_commission');
// Route::post('/delete-plan-comm', 'CommissionController@delete_plan_comm');
// Route::post('/delete-userplan-comm', 'CommissionController@delete_userplan_comm');
// Route::post('/add-revenue','CommissionController@add_revenue');
// Route::post('/get-revenue','CommissionController@get_revenue');




// Route::get('store', 'TestController@index');
// Route::get('/my-account','SettingsController@myaccount');
// Route::post('/change-password','SettingsController@change_password');
// Route::get('/notification-log','SettingsController@notification_log');
// Route::get('/list-notification-log','SettingsController@list_notification_log');
// Route::post('/notification-manage','SettingsController@notification_manage');
// Route::post('/account-edit','SettingsController@account_edit');
// // Route::get('backUp', function () {
// //     \App\Jobs\BackUp::dispatch();
// // });

// Route::get('/ee-dealers', 'DealerController@list_dealers');
// Route::get('/ee-dealer/{id?}', 'DealerController@add_edit_dealer');
// Route::post('/ee-dealer', 'DealerController@save_update_dealer');
// // Route::get('/dealers', 'DealerController@create_dealer');
// // Route::post('/delete-dealer', 'DealerController@delete_dealer');

// Route::get('/testing/{id?}', 'TestController@testing');




Route::get('/cron', function () {
    Artisan::call('schedule:run');
});

// Route::get('/record',function(){ 
//     return Response::download('https://149.36.7.16:81/24-1-2020/0-91-1922-2157-2157-447766742689-o-2340-240120-124207.wav','sheet.wav');
// });
//Route::get('/affinity-user','AffinityController@index');
Route::get('/ts','AffinityController@ts');

Route::get('/get-sim-info','TelnaController@get_sim_info');

