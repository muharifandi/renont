package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class PartnerDetail(
    val id: Int,
    val ownershipId: Int = 0,
    val accountId: Int = 0,
    val ownershipName: String? = null,
    val companyName: String? = null,
    val taxNumber: String? = null,
    val imgProfile: String? = null,
    val regenciesId: Int = 0,
    val regenciesName: String? = null,
    val address: String? = null,
    val latitude: Double = 0.0,
    val longitude: Double = 0.0,
    val description: String? = null
)
