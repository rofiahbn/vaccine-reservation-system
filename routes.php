<?php

require_once("{$_SERVER['DOCUMENT_ROOT']}/router.php");

/*front end */
get('/', 'views/index.php'); 
get('/pages', 'views/pages.php'); 


get('/admin', 'views/admin/login.php'); 
get('/logout', 'views/admin/logout.php'); 
get('/lupa_password', 'views/admin/lupa_password.php'); 

/*staff akun */
get('/stafflist', 'views/admin/stafflist.php'); 
get('/edit-staff', 'views/admin/editstaff.php'); 
post('/edit-staff', 'views/admin/editstaff.php'); 
post('/insert_pegawai', 'views/admin/insert_pegawai.php'); 
post('/proses_login', 'views/admin/proses_login.php'); 
post('/proses_update_staff', 'views/admin/proses_update_staff.php'); 
get('/proses_hapus_staff', 'views/admin/proses_hapus_staff.php'); 
get('/admin-addpegawai', 'views/admin/addpegawai.php');  
 

/*web interface*/
get('/web-interface', 'views/admin/web-interface.php'); 
get('/delete_menu', 'views/admin/proses_hapus_menu.php');  
get('/tambah-menu', 'views/admin/add_menu.php');
post('/proses-tambah-menu', 'views/admin/proses_tambah_menu.php');
/* Web Interface - Edit Menu */
get('/edit-menu', 'views/admin/edit_menu.php');
post('/proses-update-menu', 'views/admin/proses_update_menu.php');
get('/edit-page', 'views/admin/edit_content.php');
post('/proses-update-page', 'views/admin/proses_update_page.php');
post('/upload-gambar-page', 'views/admin/upload_handler.php'); // Route khusus upload gambar

/*roles*/
get('/role-list', 'views/admin/role_list.php');
get('/edit-role-access', 'views/admin/edit_role_access.php');
post('/proses-update-privileges', 'views/admin/proses_update_privileges.php');

/*order*/
get('/order', 'system/order.php');
get('/order.php', 'system/order.php');
get('/search_patient.php', 'system/search_patient.php');
get('/get_patient.php', 'system/get_patient.php');
get('/check_slots.php', 'system/check_slots.php');
/*system rofi */
//get('/add_participant.php', 'system/add_participant.php'); 
//post('/add_participant.php', 'system/add_participant.php'); 
get('/booking_confirmation.php', 'system/booking_confirmation.php'); 
get('/booking_success.php', 'system/booking_success.php');
get('/calendar_helper.php', 'system/calendar_helper.php'); 
get('/calender.php', 'system/calender.php'); 
get('/cancel_edit.php', 'system/cancel_edit.php'); 
get('/clear_session.php', 'system/clear_session.php');  
get('/delete_participant.php', 'system/delete_participant.php'); 
get('/edit_participant.php', 'system/edit_participant.php');  
get('/final_submit', 'system/final_submit.php'); 
post('/final_submit.php', 'system/final_submit.php'); 
get('/get_first_participant.php', 'system/get_first_participant.php'); 
get('/reset_session.php', 'system/reset_session.php');   
post('/save_booking.php', 'system/save_booking.php');

/*system rofi dashboard */
get('/dashboard', 'system/admin/dashboard.php'); 
post('/dashboard', 'system/admin/dashboard.php'); 

post('/proses_simpan.php', 'system/proses_simpan.php'); 
get('/dashboard.php', 'system/admin/dashboard.php'); 
get('/system-dashboard', 'system/admin/dashboard.php');
get('/booking_detail.php', 'system/admin/booking_detail.php');
get('/proses_tindakan.php', 'system/admin/proses_tindakan.php');
post('/proses_simpan_tindakan.php', 'system/admin/proses_simpan_tindakan.php');
post('/update_status.php', 'system/admin/update_status.php');
post('/update_diskon.php', 'system/admin/update_diskon.php');
post('/update_deskripsi.php', 'system/admin/update_deskripsi.php');
post('/update_booking.php', 'system/admin/update_booking.php');
get('/staff.php', 'system/admin/staff.php');
post('/save_staff.php', 'system/admin/save_staff.php');
post('/save_service.php', 'system/admin/save_service.php');
post('/save_product.php', 'system/admin/save_product.php');
post('/save_patient.php', 'system/admin/save_patient.php');
post('/save_jadwal_libur.php', 'system/admin/save_jadwal_libur.php');
post('/save_jadwal_klinik.php', 'system/admin/save_jadwal_klinik.php');
post('/save_jadwal_khusus.php', 'system/admin/save_jadwal_khusus.php');
post('/reschedule_booking.php', 'system/admin/reschedule_booking.php');
post('/remove_staff.php', 'system/admin/remove_staff.php');
post('/proses_simpan_pelayanan.php', 'system/admin/proses_simpan_pelayanan.php');
post('/proses_bayar_multiple.php', 'system/admin/proses_bayar_multiple.php');
get('/products_pelayanan.php', 'system/admin/products_pelayanan.php');
get('/products_jasa.php', 'system/admin/products_jasa.php');
get('/products.php', 'system/admin/products.php');
get('/pembayaran.php', 'system/admin/pembayaran.php');
get('/pembayaran.php', 'system/admin/pembayaran.php');
get('/patients.php', 'system/admin/patients.php');
get('/patient_detail.php', 'system/admin/patient_detail.php');
get('/kirim_invoice.php', 'system/admin/kirim_invoice.php');
get('/get_surat_list.php', 'system/admin/get_surat_list.php');
get('/get_staff.php', 'system/admin/get_staff.php');
get('/get_services.php', 'system/admin/get_services.php');
get('/get_service_detail.php', 'system/admin/get_service_detail.php');
get('/get_products.php', 'system/admin/get_products.php');
get('/get_patients.php', 'system/admin/get_patients.php');
get('/get_now_serving.php', 'system/admin/get_now_serving.php');
get('/edit_product.php', 'system/admin/edit_product.php');
post('/edit_product.php', 'system/admin/edit_product.php');
get('/edit_pelayanan.php', 'system/admin/edit_pelayanan.php');
post('/edit_pelayanan.php', 'system/admin/edit_pelayanan.php');
get('/edit_patient.php', 'system/admin/edit_patient.php');
post('/edit_patient.php', 'system/admin/edit_patient.php');
get('/edit_jasa.php', 'system/admin/edit_jasa.php');
post('/edit_jasa.php', 'system/admin/edit_jasa.php');
post('/delete_service.php', 'system/admin/delete_service.php');
get('/delete_product.php', 'system/admin/delete_product.php');
get('/delete_pelayanan.php', 'system/admin/delete_pelayanan.php');
get('/check_date_status.php', 'system/admin/check_date_status.php');
post('/cetak_surat.php', 'system/admin/cetak_surat.php');
get('/cetak_pembayaran.php', 'system/admin/cetak_pembayaran.php');
get('/cetak_label.php', 'system/admin/cetak_label.php');
get('/calendar_setting.php', 'system/admin/calendar_setting.php');
post('/assign_doctor.php', 'system/admin/assign_doctor.php');
post('/add_participant.php', 'system/admin/add_participant.php');
get('/add_jasa.php', 'system/admin/add_jasa.php');
post('/add_booking_staff.php', 'system/admin/add_booking_staff.php');
get('/templates/surat_sehat.php', 'system/admin/templates/surat_sehat.php'); 
get('/templates/surat_sakit.php', 'system/admin/templates/surat_sakit.php'); 
get('/templates/sertifikat_vaksin.php', 'system/admin/templates/sertifikat_vaksin.php'); 
get('/edit_booking.php', 'system/admin/edit_booking.php');
get('/add_product.php', 'system/admin/add_product.php');
get('/add_pelayanan.php', 'system/admin/add_pelayanan.php');
get('/laporan.php', 'system/admin/laporan.php');



  
  
 
/*newRoutes system order*/
get('/add_participant', 'system/add_participant.php'); 
post('/add_participant', 'system/add_participant.php');
get('/booking_confirmation', 'system/booking_confirmation.php');  
get('/booking_success', 'system/booking_success.php');
get('/calendar_helper', 'system/calendar_helper.php'); 
get('/calender', 'system/calender.php'); 
get('/cancel_edit', 'system/cancel_edit.php'); 
get('/clear_session', 'system/clear_session.php');  
get('/delete_participant', 'system/delete_participant.php'); 
get('/edit_participant', 'system/edit_participant.php');  
get('/final_submit', 'system/final_submit.php'); 
post('/final_submit', 'system/final_submit.php'); 
get('/get_first_participant', 'system/get_first_participant.php'); 
get('/reset_session', 'system/reset_session.php');   
post('/save_booking', 'system/save_booking.php');

any('/404','views/404.php');



?>

