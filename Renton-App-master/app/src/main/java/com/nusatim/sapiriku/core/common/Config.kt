package com.nusatim.sapiriku.core.common

import com.nusatim.sapiriku.BuildConfig

object Config {
    val HOST_URL = BuildConfig.BASE_URL
    val API_URL = "${HOST_URL}api/"
    val BASE_VEHICLE_IMAGE = "${HOST_URL}data/vehicles/"
    val BASE_CUSTOMER_IMAGE = "${HOST_URL}data/customers/profile/"
    val BASE_CUSTOMER_IDENTITY_IMAGE = "${HOST_URL}data/customers/files/identity/"
    val BASE_PARTNER_IMAGE = "${HOST_URL}data/partners/profile/"
    val BASE_NEWS_IMAGE = "${HOST_URL}data/news/"
    val BASE_PARTNER_REWARD_IMAGE = "${HOST_URL}data/rewards/"
    const val CHANNEL_ID = "sapiriku_channel"
    const val NOTIFY_ID = 1

    object RequestCode {
        const val PICK_DATE_RANGE = 2598
        const val UPDATE_APP = 2599
        const val LOGIN = 2600
        const val PICK_LOCATION_1 = 2601
        const val PICK_LOCATION_2 = 2602
        const val PICK_IMAGE_1 = 2603
        const val PICK_IMAGE_2 = 2604
        const val PICK_IMAGE_3 = 2605
        const val PICK_IMAGE_4 = 2606
        const val PICK_IMAGE_5 = 2607
        const val ACTIVITY_REQUEST = 2001
        const val ACTIVITY_REQUEST_2 = 2002
        const val TRANSACTION_RENT_VEHICLE = 2003
        const val FILTER_REQUEST = 2004
        const val SORT_REQUEST = 2005
        const val CHAT = 2006
        const val RESUBMIT_PARTNER_REGISTER = 2008
    }
}
