-- Index yang hilang, ditemukan di audit database & query (RentOn-Audit-Database-Query.md §Sedang).
-- Semua ADD KEY di sini aman dijalankan tanpa downtime untuk InnoDB (online DDL),
-- tapi tetap disarankan pada jam trafik rendah kalau tabelnya sudah besar.

-- 1) notification.account_id -- dipakai untuk ambil notifikasi per akun (full scan tanpa ini).
ALTER TABLE `notification` ADD KEY `notification_account_id` (`account_id`);

-- 2) chat_message.account_id -- dipakai untuk filter pesan per pengirim (full scan tanpa ini).
ALTER TABLE `chat_message` ADD KEY `chat_message_account_id` (`account_id`);

-- 3) history_partner_reward(account_id, reward_id) -- dipakai is_reward_added() di dalam loop
--    _process_partner_rewards(), dieksekusi setiap kali booking diselesaikan (booking_done_put).
ALTER TABLE `history_partner_reward` ADD KEY `hpr_account_reward` (`account_id`,`reward_id`);

-- 4) partner_rewards(status, feature_id, reward_scope) -- dipakai list_reward() di code path yang
--    sama (booking_done_put). `status` selalu difilter (unconditional), feature_id & reward_scope
--    kondisional -- urutan kolom komposit ditaruh status dulu sesuai best practice index MySQL.
ALTER TABLE `partner_rewards` ADD KEY `pr_status_feature_scope` (`status`,`feature_id`,`reward_scope`);
