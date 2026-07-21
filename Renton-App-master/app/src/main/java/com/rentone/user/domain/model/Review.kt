package com.rentone.user.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class Review(
    val id: Int,
    val name: String? = null,
    val imgProfile: String? = null,
    val comment: String? = null,
    val rating: Int = 0,
    val dateModified: String? = null
)
