package com.rentone.user.core.common

object Config {
    const val HOST_URL = "https://cp.renton.co.id/"
    const val API_URL = "${HOST_URL}api/"
    const val BASE_VEHICLE_IMAGE = "${HOST_URL}data/vehicles/"
    const val BASE_CUSTOMER_IMAGE = "${HOST_URL}data/customers/profile/"
    const val BASE_CUSTOMER_IDENTITY_IMAGE = "${HOST_URL}data/customers/files/identity/"
    const val BASE_PARTNER_IMAGE = "${HOST_URL}data/partners/profile/"
    const val BASE_NEWS_IMAGE = "${HOST_URL}data/news/"
    const val BASE_PARTNER_REWARD_IMAGE = "${HOST_URL}data/rewards/"
    const val CHANNEL_ID = "rentone"
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
