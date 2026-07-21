-- Migration: optimisasi index untuk fitur pencarian kendaraan (RentVehicle_m::list_vehicle / list_promote_vehicle)
-- Alasan: kolom-kolom ini dipakai sebagai kondisi WHERE/JOIN pada setiap request pencarian
-- (status selalu difilter, price & max_passenger dipakai saat multi-filter, regencies_id dipakai
-- saat filter kota, start_date/end_date dipakai saat filter tanggal sewa) tapi sebelumnya
-- tidak punya index sama sekali sehingga MySQL melakukan full table scan di setiap pencarian.
-- Jalankan di jam trafik rendah / maintenance window karena ALTER TABLE menambah index bisa
-- mengunci/membaca seluruh tabel tergantung ukuran data & engine (InnoDB online DDL umumnya aman
-- untuk ADD KEY, tapi tetap sediakan waktu untuk tabel besar).

ALTER TABLE `rent_vehicles_item`
  ADD KEY `rent_vehicles_item_status` (`status`),
  ADD KEY `rent_vehicles_item_price` (`price`),
  ADD KEY `rent_vehicles_item_max_passenger` (`max_passenger`),
  ADD KEY `rent_vehicles_item_status_price` (`status`,`price`),
  ADD KEY `rent_vehicles_item_status_max_passenger` (`status`,`max_passenger`);

ALTER TABLE `partners`
  ADD KEY `partners_regencies_id` (`regencies_id`);

ALTER TABLE `transaction_rent_vehicle`
  ADD KEY `transaction_rent_vehicle_status_dates` (`status`,`start_date`,`end_date`);
