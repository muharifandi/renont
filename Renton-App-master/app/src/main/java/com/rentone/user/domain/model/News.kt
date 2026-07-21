package com.rentone.user.domain.model



data class News(
    val id: Int,
    val userType: Int = 0,
    val title: String? = null,
    val img: String? = null,
    val content: String? = null,
    val isVoucher: Int = 0,
    val voucherId: Int = 0,
    val dateAdded: String? = null
)
